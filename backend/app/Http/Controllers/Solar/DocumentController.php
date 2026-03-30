<?php

namespace App\Http\Controllers\Solar;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::query()->orderBy('sort_order')->orderBy('created_at', 'desc')->get()
            ->map(fn ($d) => $this->format($d));

        return response()->json(['success' => true, 'data' => $documents]);
    }

    /** Public endpoint — returns only published documents */
    public function publicIndex()
    {
        $documents = Document::query()->where(fn ($q) => $q->where('is_published', true))
            ->orderBy('sort_order')->orderBy('created_at', 'desc')->get()
            ->map(fn ($d) => $this->format($d));

        return response()->json(['success' => true, 'data' => $documents]);
    }

    public function store(StoreDocumentRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('documents', 'local');
        }
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $request->file('thumbnail')->store('document_thumbs', 'local');
        }
        unset($data['file'], $data['thumbnail']);

        $document = Document::query()->create($data);

        return response()->json(['success' => true, 'data' => $this->format($document)], 201);
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($document->file_path) {
                Storage::disk('local')->delete($document->file_path);
                Storage::disk('public')->delete($document->file_path);
            }
            $data['file_path'] = $request->file('file')->store('documents', 'local');
        }
        if ($request->hasFile('thumbnail')) {
            if ($document->thumbnail_path) {
                Storage::disk('local')->delete($document->thumbnail_path);
                Storage::disk('public')->delete($document->thumbnail_path);
            }
            $data['thumbnail_path'] = $request->file('thumbnail')->store('document_thumbs', 'local');
        }
        unset($data['file'], $data['thumbnail']);

        $document->update($data);

        return response()->json(['success' => true, 'data' => $this->format($document)]);
    }

    public function destroy(Document $document)
    {
        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
            Storage::disk('public')->delete($document->file_path);
        }
        if ($document->thumbnail_path) {
            Storage::disk('local')->delete($document->thumbnail_path);
            Storage::disk('public')->delete($document->thumbnail_path);
        }
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function getSignedUrl(Request $request, $id, $type = 'file')
    {
        $document = Document::findOrFail($id);
        
        $url = URL::temporarySignedRoute(
            'api.v1.documents.signed-view',
            now()->addMinutes(30),
            ['id' => $id, 'type' => $type]
        );

        return response()->json(['url' => $url]);
    }

    public function viewSigned(Request $request, $id, $type)
    {
        $document = Document::findOrFail($id);
        
        $path = $type === 'thumbnail' ? $document->thumbnail_path : $document->file_path;

        if (!$path) abort(404);

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404, 'File not found');
    }

    private function format(Document $d): array
    {
        return [
            'id' => $d->id,
            'title' => $d->title,
            'description' => $d->description,
            'category' => $d->category,
            'file_url' => $d->file_path ? url('/api/v1/documents/'.$d->id.'/view-url?type=file') : null,
            'thumbnail_url' => $d->thumbnail_path ? url('/api/v1/documents/'.$d->id.'/view-url?type=thumbnail') : null,
            'is_published' => $d->is_published,
            'sort_order' => $d->sort_order,
            'created_at' => $d->created_at,
        ];
    }
}
