<?php

namespace App\Http\Controllers\Solar;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::query()->orderBy('sort_order')->orderBy('created_at', 'desc')->get()
            ->map(function (Document $d) {
                return $this->format($d);
            });

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
                /** @var \Illuminate\Filesystem\FilesystemAdapter $local */
                $local = Storage::disk('local');
                $local->delete($document->file_path);
                /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
                $public = Storage::disk('public');
                $public->delete($document->file_path);
            }
            $data['file_path'] = $request->file('file')->store('documents', 'local');
        }
        if ($request->hasFile('thumbnail')) {
            if ($document->thumbnail_path) {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $local */
                $local = Storage::disk('local');
                $local->delete($document->thumbnail_path);
                /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
                $public = Storage::disk('public');
                $public->delete($document->thumbnail_path);
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
            /** @var \Illuminate\Filesystem\FilesystemAdapter $local */
            $local = Storage::disk('local');
            $local->delete($document->file_path);
            /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
            $public = Storage::disk('public');
            $public->delete($document->file_path);
        }
        if ($document->thumbnail_path) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $local */
            $local = Storage::disk('local');
            $local->delete($document->thumbnail_path);
            /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
            $public = Storage::disk('public');
            $public->delete($document->thumbnail_path);
        }
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function getSignedUrl(Request $request, $id, $type = 'file')
    {
        $document = Document::findOrFail($id);
        
        $url = URL::temporarySignedRoute(
            'documents.signed-view',
            now()->addMinutes(120),
            ['id' => $id, 'type' => $type]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['url' => $url]);
        }

        return redirect($url);
    }

    public function viewSigned(Request $request, $id, $type)
    {
        $document = Document::findOrFail($id);
        
        $path = $type === 'thumbnail' ? $document->thumbnail_path : $document->file_path;

        if (!$path) abort(404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $local */
        $local = Storage::disk('local');
        if ($local->exists($path)) {
            return $local->response($path);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
        $public = Storage::disk('public');
        if ($public->exists($path)) {
            return $public->response($path);
        }

        abort(404, 'File not found');
    }

    private function format(Document $d): array
    {
        $file_url = null;
        if ($d->file_path) {
            $file_url = URL::temporarySignedRoute(
                'documents.signed-view',
                now()->addMinutes(120),
                ['id' => $d->id, 'type' => 'file']
            );
        }

        $thumbnail_url = null;
        if ($d->thumbnail_path) {
            $thumbnail_url = URL::temporarySignedRoute(
                'documents.signed-view',
                now()->addMinutes(120),
                ['id' => $d->id, 'type' => 'thumbnail']
            );
        }

        return [
            'id' => $d->id,
            'title' => $d->title,
            'description' => $d->description,
            'category' => $d->category,
            'file_url' => $file_url,
            'thumbnail_url' => $thumbnail_url,
            'is_published' => $d->is_published,
            'sort_order' => $d->sort_order,
            'created_at' => $d->created_at,
        ];
    }
}
