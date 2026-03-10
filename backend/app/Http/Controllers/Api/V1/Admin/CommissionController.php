<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Lead;
use App\Services\CommissionService;
use App\Http\Requests\EnterSuperAgentCommissionRequest;
use App\Http\Requests\EnterAgentCommissionRequest;
use App\Http\Requests\UpdateCommissionRequest;
use App\Http\Requests\MarkCommissionPaidRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommissionController extends Controller
{
    private CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Commission::with(['lead', 'payee', 'enteredBy', 'paidBy']);

        if ($request->filled('filter')) {
            $filter = $request->filter;
            if ($filter === 'super_agent_pending') {
                $query->where('payee_role', 'super_agent')->where('payment_status', 'unpaid');
            } elseif ($filter === 'super_agent_paid') {
                $query->where('payee_role', 'super_agent')->where('payment_status', 'paid');
            } elseif ($filter === 'super_agent_all') {
                $query->where('payee_role', 'super_agent');
            } elseif ($filter === 'agent_direct_pending') {
                $query->where('payee_role', 'agent')->whereHas('enteredBy', fn($q) => $q->where('role', 'admin'))->where('payment_status', 'unpaid');
            } elseif ($filter === 'agent_direct_paid') {
                $query->where('payee_role', 'agent')->whereHas('enteredBy', fn($q) => $q->where('role', 'admin'))->where('payment_status', 'paid');
            } elseif ($filter === 'agent_direct_all') {
                $query->where('payee_role', 'agent')->whereHas('enteredBy', fn($q) => $q->where('role', 'admin'));
            } elseif ($filter === 'all_pending') {
                $query->where('payment_status', 'unpaid');
            } elseif ($filter === 'all_paid') {
                $query->where('payment_status', 'paid');
            }
        } else {
            if ($request->filled('payee_role')) {
                $query->where('payee_role', $request->payee_role);
            }
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
        }

        $commissions = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $commissions->items(),
            'meta'    => [
                'current_page' => $commissions->currentPage(),
                'last_page'    => $commissions->lastPage(),
                'total'        => $commissions->total(),
            ]
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'super_agent_unpaid_count'  => Commission::forSuperAgents()->unpaid()->count(),
                'super_agent_unpaid_amount' => (float) Commission::forSuperAgents()->unpaid()->sum('amount'),
                'super_agent_paid_amount'   => (float) Commission::forSuperAgents()->paid()->sum('amount'),
                'direct_agent_unpaid_count' => Commission::forAgents()->whereHas('enteredBy', fn($q) => $q->where('role', 'admin'))->unpaid()->count(),
                'direct_agent_unpaid_amount'=> (float) Commission::forAgents()->whereHas('enteredBy', fn($q) => $q->where('role', 'admin'))->unpaid()->sum('amount'),
                'all_time_disbursed'        => (float) Commission::paid()->sum('amount'),
            ]
        ]);
    }

    public function enterSuperAgentCommission(EnterSuperAgentCommissionRequest $request, string $ulid): JsonResponse
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $commission = $this->commissionService->enterSuperAgentCommission($lead, (float) $request->amount, $request->user());

        $lead->refresh();
        return response()->json([
            'success' => true,
            'message' => 'Super agent commission saved.',
            'data'    => [
                'commission' => $commission,
                'lead_commission_status' => $lead->commission_entry_status
            ]
        ]);
    }

    public function enterDirectAgentCommission(EnterAgentCommissionRequest $request, string $ulid): JsonResponse
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $commission = $this->commissionService->enterDirectAgentCommission($lead, (float) $request->amount, $request->user());

        $lead->refresh();
        return response()->json([
            'success' => true,
            'message' => 'Direct agent commission saved.',
            'data'    => [
                'commission' => $commission,
                'lead_commission_status' => $lead->commission_entry_status
            ]
        ]);
    }

    public function update(UpdateCommissionRequest $request, int $id): JsonResponse
    {
        $commission = Commission::findOrFail($id);
        $commission = $this->commissionService->editCommission($commission, (float) $request->amount, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commission updated successfully.',
            'data'    => $commission,
        ]);
    }

    public function markPaid(MarkCommissionPaidRequest $request, int $id): JsonResponse
    {
        $commission = Commission::findOrFail($id);
        $commission = $this->commissionService->markAsPaid($commission, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commission marked as paid.',
            'data'    => $commission,
        ]);
    }

    public function getLeadCommissions(string $ulid): JsonResponse
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $data = $this->commissionService->getLeadCommissions($lead);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
