<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::orderBy('sort_order')->orderBy('created_at', 'desc')->get()
            ->map(fn($d) => $this->format($d));
        return response()->json(['success' => true, 'data' => $documents]);
    }

    /** Public endpoint — returns only published documents */
    public function publicIndex()
    {
        $documents = Document::where('is_published', true)
            ->orderBy('sort_order')->orderBy('created_at', 'desc')->get()
            ->map(fn($d) => $this->format($d));
        return response()->json(['success' => true, 'data' => $documents]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'nullable|string|max:100',
            'is_published' => 'sometimes|boolean',
            'sort_order'   => 'sometimes|integer',
            'file'         => 'required|file|max:51200', // 50MB limit
            'thumbnail'    => 'nullable|file|image|max:5120', // 5MB limit
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('documents', 'public');
        }
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $request->file('thumbnail')->store('document_thumbs', 'public');
        }
        unset($data['file'], $data['thumbnail']);

        $document = Document::create($data);
        return response()->json(['success' => true, 'data' => $this->format($document)], 201);
    }

    public function update(Request $request, Document $document)
    {
        $data = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'nullable|string|max:100',
            'is_published' => 'sometimes|boolean',
            'sort_order'   => 'sometimes|integer',
            'file'         => 'nullable|file|max:51200',
            'thumbnail'    => 'nullable|file|image|max:5120',
        ]);

        if ($request->hasFile('file')) {
            if ($document->file_path) Storage::disk('public')->delete($document->file_path);
            $data['file_path'] = $request->file('file')->store('documents', 'public');
        }
        if ($request->hasFile('thumbnail')) {
            if ($document->thumbnail_path) Storage::disk('public')->delete($document->thumbnail_path);
            $data['thumbnail_path'] = $request->file('thumbnail')->store('document_thumbs', 'public');
        }
        unset($data['file'], $data['thumbnail']);

        $document->update($data);
        return response()->json(['success' => true, 'data' => $this->format($document)]);
    }

    public function destroy(Document $document)
    {
        if ($document->file_path) Storage::disk('public')->delete($document->file_path);
        if ($document->thumbnail_path) Storage::disk('public')->delete($document->thumbnail_path);
        $document->delete();
        return response()->json(['success' => true]);
    }

    private function format(Document $d): array
    {
        return [
            'id'             => $d->id,
            'title'          => $d->title,
            'description'    => $d->description,
            'category'       => $d->category,
            'file_url'       => $d->file_path ? asset('storage/' . $d->file_path) : null,
            'thumbnail_url'  => $d->thumbnail_path ? asset('storage/' . $d->thumbnail_path) : null,
            'is_published'   => $d->is_published,
            'sort_order'     => $d->sort_order,
            'created_at'     => $d->created_at,
        ];
    }
}
