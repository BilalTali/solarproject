<?php

namespace App\Http\Controllers\Api\V1\SuperAgent;

use App\Http\Controllers\Controller;
use App\Models\CommissionSlab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionSlabController extends Controller
{
    /**
     * Get effective slabs for the logged-in Super Agent.
     * Includes Admin's defaults overlaid with Super Agent's custom settings.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        // 1. Get Admin defaults (where super_agent_id is NULL)
        $defaults = CommissionSlab::query()->whereNull('super_agent_id')->get()->keyBy('capacity');
        
        // 2. Get SuperAgent custom slabs
        $customs = CommissionSlab::query()->where(fn($q) => $q->where('super_agent_id', $user->id))->get()->keyBy('capacity');
        
        $capacities = $defaults->keys()->merge($customs->keys())->unique();
        
        $result = $capacities->map(function ($cap) use ($defaults, $customs) {
            $default = $defaults->get($cap);
            $custom = $customs->get($cap);
            
            return [
                'capacity' => $cap,
                'label' => $custom->label ?? $default->label ?? $cap,
                'agent_commission' => (float) ($custom->agent_commission ?? $default->agent_commission ?? 0),
                'super_agent_override' => (float) ($custom->super_agent_override ?? $default->super_agent_override ?? 0),
                'enumerator_commission' => (float) ($custom->enumerator_commission ?? $default->enumerator_commission ?? 0),
                'is_custom' => !is_null($custom),
                'id' => $custom->id ?? null,
                'default_id' => $default->id ?? null,
            ];
        });

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Create or update a custom slab for the Super Agent.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'capacity' => 'required|string|max:50',
            'agent_commission' => 'required|numeric|min:0',
            'super_agent_override' => 'required|numeric|min:0',
            'enumerator_commission' => 'sometimes|numeric|min:0',
            'label' => 'sometimes|string|max:255',
        ]);

        $slab = CommissionSlab::updateOrCreate(
            ['capacity' => $data['capacity'], 'super_agent_id' => $user->id],
            [
                'label' => $data['label'] ?? "Custom Slab ({$data['capacity']})",
                'agent_commission' => $data['agent_commission'],
                'super_agent_override' => $data['super_agent_override'],
                'enumerator_commission' => $data['enumerator_commission'] ?? 0,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true, 
            'data' => $slab, 
            'message' => 'Custom commission settings saved.'
        ]);
    }
}
