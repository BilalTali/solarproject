<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnterAgentCommissionRequest;
use App\Http\Requests\EnterSuperAgentCommissionRequest;
use App\Http\Requests\MarkCommissionPaidRequest;
use App\Http\Requests\UpdateCommissionRequest;
use App\Models\Commission;
use App\Models\Lead;
use App\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCommissionController extends Controller
{
    private CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Commission::query()->with(['lead', 'payee', 'enteredBy', 'paidBy']);

        if (!$user->isSuperAdmin()) {
            $managedIds = $user->getManagedUserIds();
            $query->whereIn('payee_id', $managedIds);
        }
        if ($request->filled('filter')) {
            $filter = $request->filter;
            if ($filter === 'super_agent_pending') {
                $query->where(fn($q) => $q->where('payee_role', 'super_agent'))->where(fn($q) => $q->where('payment_status', 'unpaid'));
            } elseif ($filter === 'super_agent_paid') {
                $query->where(fn($q) => $q->where('payee_role', 'super_agent'))->where(fn($q) => $q->where('payment_status', 'paid'));
            } elseif ($filter === 'super_agent_all') {
                $query->where(fn($q) => $q->where('payee_role', 'super_agent'));
            } elseif ($filter === 'agent_direct_pending') {
                $query->where(fn($q) => $q->where('payee_role', 'agent'))->whereHas('enteredBy', fn($q) => $q->where(fn($q2) => $q2->where('role', 'admin')))->where(fn($q) => $q->where('payment_status', 'unpaid'));
            } elseif ($filter === 'agent_direct_paid') {
                $query->where(fn($q) => $q->where('payee_role', 'agent'))->whereHas('enteredBy', fn($q) => $q->where(fn($q2) => $q2->where('role', 'admin')))->where(fn($q) => $q->where('payment_status', 'paid'));
            } elseif ($filter === 'agent_direct_all') {
                $query->where(fn($q) => $q->where('payee_role', 'agent'))->whereHas('enteredBy', fn($q) => $q->where(fn($q2) => $q2->where('role', 'admin')));
            } elseif ($filter === 'all_pending') {
                $query->where(fn($q) => $q->where('payment_status', 'unpaid'));
            } elseif ($filter === 'all_paid') {
                $query->where(fn($q) => $q->where('payment_status', 'paid'));
            }
        } else {
            if ($request->filled('payee_role')) {
                $query->where(fn($q) => $q->where('payee_role', $request->payee_role));
            }
            if ($request->filled('payment_status')) {
                $query->where(fn($q) => $q->where('payment_status', $request->payment_status));
            }
        }

        $commissions = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $commissions->items(),
            'meta' => [
                'current_page' => $commissions->currentPage(),
                'last_page' => $commissions->lastPage(),
                'total' => $commissions->total(),
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $managedIds = $isSuperAdmin ? [] : $user->getManagedUserIds();

        $query = Commission::query();
        if (!$isSuperAdmin) {
            $query->whereIn('payee_id', $managedIds);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'super_agent_unpaid_count' => (clone $query)->forSuperAgents()->unpaid()->count(),
                'super_agent_unpaid_amount' => (float) (clone $query)->forSuperAgents()->unpaid()->sum('amount'),
                'super_agent_paid_amount' => (float) (clone $query)->forSuperAgents()->paid()->sum('amount'),
                'direct_agent_unpaid_count' => (clone $query)->forAgents()->whereHas('enteredBy', fn($q) => $q->where('role', 'admin'))->unpaid()->count(),
                'direct_agent_unpaid_amount' => (float) (clone $query)->forAgents()->whereHas('enteredBy', fn($q) => $q->where('role', 'admin'))->unpaid()->sum('amount'),
                'all_time_disbursed' => (float) (clone $query)->paid()->sum('amount'),
            ],
        ]);
    }

    public function enterSuperAgentCommission(EnterSuperAgentCommissionRequest $request, string $ulid): JsonResponse
    {
        $lead = Lead::query()->where(fn($q) => $q->where('ulid', $ulid))->firstOrFail();
        $payee = $lead->assignedSuperAgent;

        if (!$payee) {
            return response()->json(['success' => false, 'message' => 'No super agent assigned to this lead.'], 422);
        }

        $commission = $this->commissionService->enterCommission($lead, $payee, (float) $request->amount, $request->user());

        $lead->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Super agent commission saved.',
            'data' => [
                'commission' => $commission,
                'lead_commission_status' => $lead->commission_entry_status,
            ],
        ]);
    }

    /**
     * UNIFIED commission entry endpoint.
     * Accepts payee_id + amount. Validates payee is a direct subordinate of payer for this lead.
     */
    public function enterCommission(Request $request, string $ulid): JsonResponse
    {
        $request->validate([
            'payee_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $lead = Lead::query()->where(fn($q) => $q->where('ulid', $ulid))->firstOrFail();
        $payee = \App\Models\User::findOrFail($request->payee_id);

        $commission = $this->commissionService->enterCommission($lead, $payee, (float) $request->amount, $request->user());

        $lead->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Commission saved for ' . $payee->name . '.',
            'data' => [
                'commission' => $commission,
                'lead_commission_status' => $lead->commission_entry_status,
            ],
        ]);
    }

    public function enterDirectAgentCommission(EnterAgentCommissionRequest $request, string $ulid): JsonResponse
    {
        $lead = Lead::query()->where(fn($q) => $q->where('ulid', $ulid))->firstOrFail();
        $payee = $lead->assignedAgent;

        if (!$payee) {
            return response()->json(['success' => false, 'message' => 'No agent assigned to this lead.'], 422);
        }

        $commission = $this->commissionService->enterCommission($lead, $payee, (float) $request->amount, $request->user());

        $lead->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Direct agent commission saved.',
            'data' => [
                'commission' => $commission,
                'lead_commission_status' => $lead->commission_entry_status,
            ],
        ]);
    }

    public function enterEnumeratorCommission(Request $request, string $ulid): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0']);
        $lead = Lead::query()->where(fn($q) => $q->where('ulid', $ulid))->firstOrFail();
        $payee = $lead->submittedByEnumerator;

        if (!$payee) {
            return response()->json(['success' => false, 'message' => 'No enumerator associated with this lead submission.'], 422);
        }

        $commission = $this->commissionService->enterCommission($lead, $payee, (float) $request->amount, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Enumerator commission saved.',
            'data' => [
                'commission' => $commission,
            ],
        ]);
    }

    public function update(UpdateCommissionRequest $request, int $id): JsonResponse
    {
        $commission = Commission::query()->findOrFail($id);
        $commission = $this->commissionService->editCommission($commission, (float) $request->amount, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commission updated successfully.',
            'data' => $commission,
        ]);
    }

    public function markPaid(MarkCommissionPaidRequest $request, int $id): JsonResponse
    {
        $commission = Commission::query()->findOrFail($id);
        $commission = $this->commissionService->markAsPaid($commission, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commission marked as paid.',
            'data' => $commission,
        ]);
    }

    public function getLeadCommissions(string $ulid): JsonResponse
    {
        $lead = Lead::query()->where(fn($q) => $q->where('ulid', $ulid))->firstOrFail();
        $data = $this->commissionService->getLeadCommissions($lead);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
