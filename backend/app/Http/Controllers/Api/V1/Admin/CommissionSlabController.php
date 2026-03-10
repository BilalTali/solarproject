<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionSlab;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommissionSlabController extends Controller
{
    public function index(): JsonResponse
    {
        $slabs = CommissionSlab::ordered();
        return response()->json(['success' => true, 'data' => $slabs]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'capacity'             => 'required|string|max:50|unique:commission_slabs,capacity',
            'label'                => 'required|string|max:255',
            'agent_commission'     => 'required|numeric|min:0',
            'super_agent_override' => 'sometimes|numeric|min:0',
            'description'          => 'nullable|string',
            'is_active'            => 'sometimes|boolean',
        ]);

        $slab = CommissionSlab::create($data);

        return response()->json(['success' => true, 'data' => $slab, 'message' => 'Commission slab created.'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $slab = CommissionSlab::findOrFail($id);

        $data = $request->validate([
            'label'                => 'sometimes|string|max:255',
            'agent_commission'     => 'sometimes|numeric|min:0',
            'super_agent_override' => 'sometimes|numeric|min:0',
            'description'          => 'nullable|string',
            'is_active'            => 'sometimes|boolean',
        ]);

        $slab->update($data);

        return response()->json(['success' => true, 'data' => $slab->fresh(), 'message' => 'Commission slab updated.']);
    }

    public function destroy(int $id): JsonResponse
    {
        CommissionSlab::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Commission slab deleted.']);
    }
}
