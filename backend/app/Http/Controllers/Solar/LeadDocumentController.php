<?php

namespace App\Http\Controllers\Solar;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Filesystem\FilesystemAdapter;

class LeadDocumentController extends Controller
{
    public function index(Request $request, string $ulid)
    {
        $lead = Lead::query()->where('ulid', $ulid)->with('documents')->firstOrFail();
        $this->authorizeDocument($request->user(), $lead);

        $documents = $lead->documents->map(function ($doc) use ($ulid) {
            return [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'original_filename' => $doc->original_filename,
                'created_at' => $doc->created_at,
                'download_url' => $doc->download_url,
                'is_virtual' => false
            ];
        });

        $virtualDocuments = collect([
            [
                'id' => 'virtual-quotation',
                'document_type' => 'Pro Forma Quotation',
                'original_filename' => 'Quotation-'.$lead->quotation_serial.'.pdf',
                'created_at' => $lead->bill_date ?? $lead->created_at,
                'download_url' => url('/api/v1/leads/'.$ulid.'/pdf/quotation'),
                'is_virtual' => true
            ],
            [
                'id' => 'virtual-receipt',
                'document_type' => 'Payment Receipt',
                'original_filename' => 'Receipt-'.$lead->receipt_serial.'.pdf',
                'created_at' => $lead->bill_date ?? $lead->created_at,
                'download_url' => url('/api/v1/leads/'.$ulid.'/pdf/receipt'),
                'is_virtual' => true
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => collect($virtualDocuments)->merge($documents)->values()
        ]);
    }

    /**
     * Download a lead document with authorization check.
     */
    public function download(Request $request, string $ulid, int $documentId)
    {
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $this->authorizeDocument($request->user(), $lead);

        $document = LeadDocument::query()
            ->where(fn ($q) => $q->where('id', $documentId))
            ->where(fn ($q) => $q->where('lead_id', $lead->id))
            ->firstOrFail();
        $path = $document->file_path;

        // Try 'local' disk first (new secure storage), fallback to 'public' (legacy storage)
        /** @var \Illuminate\Filesystem\FilesystemAdapter $local */
        $local = Storage::disk('local');
        if ($local->exists($path)) {
            return $local->download($path, $document->original_filename);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
        $public = Storage::disk('public');
        if ($public->exists($path)) {
            return $public->download($path, $document->original_filename);
        }

        abort(404, 'File not found on any disk.');
    }

    /**
     * Generate a temporary signed URL for viewing a document.
     */
    public function getSignedUrl(Request $request, string $ulid, int $id)
    {
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $this->authorizeDocument($request->user(), $lead);

        $url = URL::temporarySignedRoute(
            'leads.documents.signed-view',
            now()->addMinutes(120),
            ['ulid' => $ulid, 'id' => $id]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['url' => $url]);
        }

        return redirect($url);
    }

    /**
     * View a document via a signed URL (no auth header needed).
     */
    public function viewSigned(Request $request, string $ulid, int $id)
    {
        \Illuminate\Support\Facades\Log::info('SIGNED_VIEW_HIT', [
            'ulid' => $ulid,
            'id' => $id,
            'query' => $request->query(),
            'url' => $request->fullUrl()
        ]);

        // Manual signature verification (better for production error handling)
        if (! $request->hasValidSignature()) {
            \Illuminate\Support\Facades\Log::warning('INVALID_SIGNATURE', [
                'ulid' => $ulid,
                'id' => $id,
                'full_url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);
            abort(403, 'Invalid or expired signature.');
        }

        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $document = LeadDocument::query()
            ->where(fn ($q) => $q->where('id', $id))
            ->where(fn ($q) => $q->where('lead_id', $lead->id))
            ->firstOrFail();
        $path = $document->file_path;

        $isDownload = $request->query('disposition') === 'attachment';
        $filename = $document->original_filename ?: basename($path);

        \Illuminate\Support\Facades\Log::info('FILE_PATH_RESOLVED', [
            'path' => $path,
            'is_download' => $isDownload
        ]);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $local */
        $local = Storage::disk('local');
        if ($local->exists($path)) {
            return $isDownload 
                ? $local->download($path, $filename)
                : $local->response($path);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
        $public = Storage::disk('public');
        if ($public->exists($path)) {
            return $isDownload 
                ? $public->download($path, $filename)
                : $public->response($path);
        }

        abort(404, 'File not found');
    }

    private function authorizeDocument($user, $lead)
    {
        $isAuthorized = $user->isAdmin() ||
                        ($user->id === (int) $lead->assigned_agent_id) ||
                        ($user->id === (int) $lead->assigned_super_agent_id) ||
                        ($user->id === (int) $lead->submitted_by_agent_id) ||
                        ($user->id === (int) $lead->submitted_by_enumerator_id);

        if (! $isAuthorized) {
            abort(403, 'Unauthorized access to this document.');
        }
    }
}
