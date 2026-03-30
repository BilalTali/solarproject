<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        $media = Media::orderBy('sort_order')->orderBy('date', 'desc')->get()
            ->map(fn ($m) => $this->format($m));

        return response()->json(['success' => true, 'data' => $media]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'is_published' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'image' => 'nullable|file|image|max:10240', // 10MB limit for winners images
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('media', 'public');
        }
        unset($data['image']);

        $media = Media::create($data);

        return response()->json(['success' => true, 'data' => $this->format($media)], 201);
    }

    public function update(Request $request, Media $media)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'is_published' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'image' => 'nullable|file|image|max:10240',
        ]);

        if ($request->hasFile('image')) {
            if ($media->image_path) {
                Storage::disk('public')->delete($media->image_path);
            }
            $data['image_path'] = $request->file('image')->store('media', 'public');
        }
        unset($data['image']);

        $media->update($data);

        return response()->json(['success' => true, 'data' => $this->format($media)]);
    }

    public function destroy(Media $media)
    {
        if ($media->image_path) {
            Storage::disk('public')->delete($media->image_path);
        }
        $media->delete();

        return response()->json(['success' => true]);
    }

    private function format(Media $m): array
    {
        return [
            'id' => $m->id,
            'title' => $m->title,
            'description' => $m->description,
            'image_url' => $m->image_path ? asset('storage/'.$m->image_path) : null,
            'date' => $m->date?->format('Y-m-d'),
            'is_published' => $m->is_published,
            'sort_order' => $m->sort_order,
            'created_at' => $m->created_at,
        ];
    }
}
