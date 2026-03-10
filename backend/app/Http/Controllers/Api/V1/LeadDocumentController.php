<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class LeadDocumentController extends Controller
{
    /**
     * Download a lead document with authorization check.
     */
    public function download(Request $request, string $ulid, int $documentId)
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $this->authorizeDocument($request->user(), $lead);

        $document = LeadDocument::where('id', $documentId)->where('lead_id', $lead->id)->firstOrFail();
        $path = $document->file_path;

        // Try 'local' disk first (new secure storage), fallback to 'public' (legacy storage)
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path, $document->original_filename);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path, $document->original_filename);
        }

        abort(404, 'File not found on any disk.');
    }

    /**
     * Generate a temporary signed URL for viewing a document.
     */
    public function getSignedUrl(Request $request, string $ulid, int $id)
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $this->authorizeDocument($request->user(), $lead);

        $url = URL::temporarySignedRoute(
            'api.v1.leads.documents.signed-view',
            now()->addMinutes(30),
            ['ulid' => $ulid, 'id' => $id]
        );

        return response()->json(['url' => $url]);
    }

    /**
     * View a document via a signed URL (no auth header needed).
     */
    public function viewSigned(Request $request, string $ulid, int $id)
    {
        // middleware('signed') handles the integrity check
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $document = LeadDocument::where('id', $id)->where('lead_id', $lead->id)->firstOrFail();
        $path = $document->file_path;

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path, $document->original_filename);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path, $document->original_filename);
        }

        abort(404, 'File not found');
    }

    private function authorizeDocument($user, $lead)
    {
        $isAuthorized = $user->isAdmin() || 
                        ($user->id === (int)$lead->assigned_agent_id) || 
                        ($user->id === (int)$lead->assigned_super_agent_id) ||
                        ($user->id === (int)$lead->submitted_by_agent_id);

        if (!$isAuthorized) {
            abort(403, 'Unauthorized access to this document.');
        }
    }
}
