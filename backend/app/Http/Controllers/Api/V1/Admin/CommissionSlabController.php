<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionSlab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionSlabController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $saId = $request->query('super_agent_id'); 
        $query = CommissionSlab::query();
        
        if ($saId) {
            $query->where(fn($q) => $q->where('super_agent_id', $saId));
        } else {
            $query->whereNull('super_agent_id');
        }
        
        $slabs = $query->orderBy('capacity', 'asc')->get();

        return response()->json(['success' => true, 'data' => $slabs]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'super_agent_id' => 'nullable|exists:users,id',
            'capacity' => 'required|string|max:50',
            'label' => 'required|string|max:255',
            'agent_commission' => 'required|numeric|min:0',
            'super_agent_override' => 'sometimes|numeric|min:0',
            'enumerator_commission' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Ensure unique capacity per SA
        $exists = CommissionSlab::query()
            ->where(fn($q) => $q->where('super_agent_id', $data['super_agent_id'] ?? null))
            ->where(fn($q) => $q->where('capacity', $data['capacity']))
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Slab for this capacity already exists.'], 422);
        }

        $slab = CommissionSlab::query()->create($data);

        return response()->json(['success' => true, 'data' => $slab, 'message' => 'Commission slab created.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $slab = CommissionSlab::query()->findOrFail($id);

        $data = $request->validate([
            'label' => 'sometimes|string|max:255',
            'agent_commission' => 'sometimes|numeric|min:0',
            'super_agent_override' => 'sometimes|numeric|min:0',
            'enumerator_commission' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $slab->update($data);

        return response()->json(['success' => true, 'data' => $slab->fresh(), 'message' => 'Commission slab updated.']);
    }

    public function destroy(int $id): JsonResponse
    {
        CommissionSlab::query()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Commission slab deleted.']);
    }
}
