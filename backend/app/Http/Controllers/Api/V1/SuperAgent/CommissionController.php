<?php

namespace App\Http\Controllers\Api\V1\SuperAgent;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Lead;
use App\Services\CommissionService;
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
        $superAgent = $request->user();
        
        // Return commissions where super agent is either payee (earnings) or entered_by (payouts)
        $query = Commission::with(['lead', 'payee', 'enteredBy', 'paidBy'])
                    ->where(function($q) use ($superAgent) {
                        $q->where('payee_id', $superAgent->id)
                          ->orWhere('entered_by', $superAgent->id);
                    });

        // Apply status/view filter
        if ($request->filled('filter')) {
            $f = $request->filter;
            if ($f === 'pending_my_payment') {
                $query->where('payee_id', $superAgent->id)->where('payment_status', 'unpaid');
            } elseif ($f === 'fully_paid') {
                $query->where('payee_id', $superAgent->id)->where('payment_status', 'paid');
            } elseif ($f === 'pending_to_pay') {
                $query->where('entered_by', $superAgent->id)->where('payee_role', 'agent')->where('payment_status', 'unpaid');
            } elseif ($f === 'all_my_earnings') {
                $query->where('payee_id', $superAgent->id);
            } elseif ($f === 'all_to_pay') {
                $query->where('entered_by', $superAgent->id)->where('payee_role', 'agent');
            }
        } elseif ($request->filled('view')) {
            // Legacy/Fallback support for 'view'
            if ($request->view === 'my_earnings') {
                $query->where('payee_id', $superAgent->id);
            } elseif ($request->view === 'agent_payouts') {
                $query->where('entered_by', $superAgent->id)->where('payee_role', 'agent');
            }
        }

        $commissions = $query->latest()->paginate($request->integer('per_page', 20));

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

    public function summary(Request $request): JsonResponse
    {
        $superAgent = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'my_earnings_unpaid'         => (float) Commission::forPayee($superAgent->id)->unpaid()->sum('amount'),
                'my_earnings_paid'           => (float) Commission::forPayee($superAgent->id)->paid()->sum('amount'),
                'agent_payouts_unpaid_count' => Commission::where('entered_by', $superAgent->id)->unpaid()->count(),
                'agent_payouts_unpaid'       => (float) Commission::where('entered_by', $superAgent->id)->unpaid()->sum('amount'),
                'agent_payouts_paid'         => (float) Commission::where('entered_by', $superAgent->id)->paid()->sum('amount'),
            ]
        ]);
    }

    public function enterAgentCommission(EnterAgentCommissionRequest $request, string $ulid): JsonResponse
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $commission = $this->commissionService->enterAgentCommission($lead, (float) $request->amount, $request->user());

        $lead->refresh();
        return response()->json([
            'success' => true,
            'message' => 'Agent commission saved.',
            'data'    => [
                'commission' => $commission,
                'lead_commission_status' => $lead->commission_entry_status
            ]
        ]);
    }

    public function update(UpdateCommissionRequest $request, int $id): JsonResponse
    {
        $commission = Commission::findOrFail($id);
        
        // Ensure this is an agent commission entered by this super agent
        if ($commission->payee_role !== 'agent' || $commission->entered_by !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

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
