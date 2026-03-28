<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\Commission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /** Global stats for Super Admin */
    public function stats(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_admins' => User::roleAdmin()->count(),
                    'total_super_agents' => User::roleSuperAgent()->count(),
                    'total_agents' => User::roleAgent()->count(),
                    'total_enumerators' => User::roleEnumerator()->count(),
                    'total_leads' => Lead::count(),
                    'total_commissions' => Commission::sum('amount'),
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Super Admin Stats Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /** Monitor Super Agents (BDMs) */
    public function superAgents(Request $request): JsonResponse
    {
        $query = User::roleSuperAgent()
            ->withCount(['managedAgents', 'assignedSuperAgentLeads'])
            ->latest();

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(fn($q) => $q->where('name', 'like', $search)->orWhere('super_agent_code', 'like', $search));
        }

        return response()->json(['success' => true, 'data' => $query->paginate($request->per_page ?? 20)]);
    }

    /** Monitor Agents (BDEs) */
    public function agents(Request $request): JsonResponse
    {
        $query = User::roleAgent()
            ->with(['superAgent'])
            ->withCount(['assignedLeads', 'enumerators'])
            ->latest();

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(fn($q) => $q->where('name', 'like', $search)->orWhere('agent_id', 'like', $search));
        }

        return response()->json(['success' => true, 'data' => $query->paginate($request->per_page ?? 20)]);
    }

    /** Monitor Enumerators (ENMs) */
    public function enumerators(Request $request): JsonResponse
    {
        try {
            $query = User::roleEnumerator()
                ->with(['parentAgent'])
                ->withCount(['enumeratorLeads'])
                ->latest();

            if ($request->search) {
                $search = "%{$request->search}%";
                $query->where(fn($q) => $q->where('name', 'like', $search)->orWhere('enumerator_id', 'like', $search));
            }

            return response()->json(['success' => true, 'data' => $query->paginate($request->per_page ?? 20)]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Super Admin Enumerators Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /** Monitor Leads (Read-only) */
    public function leads(Request $request): JsonResponse
    {
        $query = Lead::with(['assignedAgent', 'assignedSuperAgent'])
            ->latest();

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(fn($q) => $q->where('beneficiary_name', 'like', $search)->orWhere('consumer_number', 'like', $search));
        }

        if ($request->status) {
            $status = $request->status;
            $query->where(fn($q) => $q->where('status', '=', $status));
        }

        return response()->json(['success' => true, 'data' => $query->paginate($request->per_page ?? 20)]);
    }

    /** Monitor Commissions (Read-only) */
    public function commissions(Request $request): JsonResponse
    {
        $query = Commission::with(['payee', 'lead'])
            ->latest();

        return response()->json(['success' => true, 'data' => $query->paginate($request->per_page ?? 20)]);
    }
}
