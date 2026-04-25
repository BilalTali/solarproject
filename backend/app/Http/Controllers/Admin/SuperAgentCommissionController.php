<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnterAgentCommissionRequest;
use App\Http\Requests\MarkCommissionPaidRequest;
use App\Http\Requests\UpdateCommissionRequest;
use App\Models\Commission;
use App\Models\Lead;
use App\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAgentCommissionController extends Controller
{
    private CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request): JsonResponse
    {
        $superAgent = $request->user();

        // Return commissions where super agent is:
        // 1. the payee (earnings)
        // 2. the enterer (payouts)
        // 3. the natural parent of the payee
        // 4. the assigned super agent for the lead (dynamic parent)
        $query = Commission::query()->with(['lead', 'payee', 'enteredBy', 'paidBy'])
            ->where(function ($q) use ($superAgent) {
                $q->where('payee_id', $superAgent->id)
                    ->orWhere('entered_by', $superAgent->id)
                    ->orWhereHas('payee', fn ($pq) => $pq->where('parent_id', $superAgent->id))
                    ->orWhereHas('lead', fn ($lq) => $lq->where('assigned_super_agent_id', $superAgent->id));
            })
            ->where('payee_role', '!=', 'field_technical_team');

        // Apply status/view filter
        if ($request->filled('filter')) {
            $f = $request->filter;
            if ($f === 'pending_my_payment') {
                $query->where(fn ($q) => $q->where('payee_id', $superAgent->id))->where(fn ($q) => $q->where('payment_status', 'unpaid'));
            } elseif ($f === 'fully_paid') {
                $query->where(fn ($q) => $q->where('payee_id', $superAgent->id))->where(fn ($q) => $q->where('payment_status', 'paid'));
            } elseif ($f === 'pending_to_pay') {
                $query->where('payee_id', '!=', $superAgent->id)->where('payment_status', 'unpaid')
                    ->where(function ($q) use ($superAgent) {
                        $q->where('entered_by', $superAgent->id)
                            ->orWhereHas('payee', function ($pq) use ($superAgent) {
                                $pq->where('parent_id', $superAgent->id)
                                   ->orWhereHas('parent', fn ($gp) => $gp->where('parent_id', $superAgent->id));
                            })
                            ->orWhereHas('lead', fn ($lq) => $lq->where('assigned_super_agent_id', $superAgent->id));
                    });
            } elseif ($f === 'all_my_earnings') {
                $query->where('payee_id', $superAgent->id);
            } elseif ($f === 'all_to_pay') {
                $query->where('payee_id', '!=', $superAgent->id)
                    ->where(function ($q) use ($superAgent) {
                        $q->where('entered_by', $superAgent->id)
                            ->orWhereHas('payee', function ($pq) use ($superAgent) {
                                $pq->where('parent_id', $superAgent->id)
                                   ->orWhereHas('parent', fn ($gp) => $gp->where('parent_id', $superAgent->id));
                            })
                            ->orWhereHas('lead', fn ($lq) => $lq->where('assigned_super_agent_id', $superAgent->id));
                    });
            } elseif ($f === 'payouts_paid') {
                $query->where('payee_id', '!=', $superAgent->id)->where('payment_status', 'paid')
                    ->where(function ($q) use ($superAgent) {
                        $q->where('entered_by', $superAgent->id)
                            ->orWhereHas('payee', function ($pq) use ($superAgent) {
                                $pq->where('parent_id', $superAgent->id)
                                   ->orWhereHas('parent', fn ($gp) => $gp->where('parent_id', $superAgent->id));
                            })
                            ->orWhereHas('lead', fn ($lq) => $lq->where('assigned_super_agent_id', $superAgent->id));
                    });
            }
        } elseif ($request->filled('view')) {
            // Legacy/Fallback support for 'view'
            if ($request->view === 'my_earnings') {
                $query->where('payee_id', $superAgent->id);
            } elseif ($request->view === 'agent_payouts') {
                $query->where('payee_id', '!=', $superAgent->id)
                    ->where(function ($q) use ($superAgent) {
                        $q->where('entered_by', $superAgent->id)
                            ->orWhereHas('payee', fn ($pq) => $pq->where('parent_id', $superAgent->id))
                            ->orWhereHas('lead', fn ($lq) => $lq->where('assigned_super_agent_id', $superAgent->id));
                    });
            }
        }

        $commissions = $query->latest()->paginate($request->integer('per_page', 20));

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
        $superAgent = $request->user();

        // Summary logic: payouts are anything where SA is natural/dynamic parent or enterer
        $agentPayoutsQuery = Commission::query()->where('payee_id', '!=', $superAgent->id)
            ->where('payee_role', '!=', 'field_technical_team')
            ->where(function ($q) use ($superAgent) {
                $q->where('entered_by', $superAgent->id)
                    ->orWhereHas('payee', function ($pq) use ($superAgent) {
                        $pq->where('parent_id', $superAgent->id)
                           ->orWhereHas('parent', fn ($gp) => $gp->where('parent_id', $superAgent->id));
                    })
                    ->orWhereHas('lead', fn ($lq) => $lq->where('assigned_super_agent_id', $superAgent->id));
            });

        return response()->json([
            'success' => true,
            'data' => [
                'my_earnings_unpaid' => (float) Commission::forPayee($superAgent->id)->unpaid()->sum('amount'),
                'my_earnings_paid' => (float) Commission::forPayee($superAgent->id)->paid()->sum('amount'),
                'agent_payouts_unpaid_count' => (clone $agentPayoutsQuery)->unpaid()->count(),
                'agent_payouts_unpaid' => (float) (clone $agentPayoutsQuery)->unpaid()->sum('amount'),
                'agent_payouts_paid' => (float) (clone $agentPayoutsQuery)->paid()->sum('amount'),
            ],
        ]);
    }

    public function enterAgentCommission(EnterAgentCommissionRequest $request, string $ulid): JsonResponse
    {
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $payee = $lead->assignedAgent;

        if (!$payee) {
            return response()->json(['success' => false, 'message' => 'No agent assigned to this lead.'], 422);
        }

        $commission = $this->commissionService->enterCommission($lead, $payee, (float)$request->amount, $request->user());

        $lead->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Agent commission saved.',
            'data' => [
                'commission' => $commission,
                'lead_commission_status' => $lead->commission_entry_status,
            ],
        ]);
    }

    public function enterEnumeratorCommission(Request $request, string $ulid): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0']);
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $payee = $lead->submittedByEnumerator;

        if (!$payee) {
            return response()->json(['success' => false, 'message' => 'No enumerator associated with this lead submission.'], 422);
        }
        
        $commission = $this->commissionService->enterCommission($lead, $payee, (float)$request->amount, $request->user());

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
        $commission = Commission::findOrFail($id);
        
        $this->authorize('update', $commission);

        $commission = $this->commissionService->editCommission($commission, (float) $request->amount, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commission updated successfully.',
            'data' => $commission,
        ]);
    }

    public function markPaid(MarkCommissionPaidRequest $request, int $id): JsonResponse
    {
        $commission = Commission::findOrFail($id);
        $this->authorize('update', $commission);
        $commission = $this->commissionService->markAsPaid($commission, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commission marked as paid.',
            'data' => $commission,
        ]);
    }

    public function getLeadCommissions(string $ulid): JsonResponse
    {
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $data = $this->commissionService->getLeadCommissions($lead);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
