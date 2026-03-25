<?php

namespace App\Http\Controllers\Api\V1\Agent;
 
use App\Http\Controllers\Controller;
use App\Http\Requests\MarkCommissionPaidRequest;
use App\Models\Commission;
use App\Models\Lead;
use App\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
 
class CommissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $agent = $request->user();

        if (in_array($request->filter, ['pending_to_pay', 'all_to_pay'])) {
            $query = Commission::query()->with(['lead', 'payee'])
                ->forPayer($agent->id)
                ->where(fn($q) => $q->where('payee_role', 'enumerator'));

            if ($request->filter === 'pending_to_pay') {
                $query->unpaid();
            }
        } else {
            $query = Commission::query()->with(['lead', 'enteredBy'])
                ->forPayee($agent->id)
                ->where(fn($q) => $q->where('payee_role', 'agent'));

            if ($request->filter === 'pending_my_payment') {
                $query->unpaid();
            } elseif ($request->filter === 'fully_paid') {
                $query->paid();
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
        $agent = $request->user();

        $myEarningsBase = Commission::forPayee($agent->id)->where(fn($q) => $q->where('payee_role', 'agent'));
        $payoutsBase = Commission::forPayer($agent->id)->where(fn($q) => $q->where('payee_role', 'enumerator'));

        return response()->json([
            'success' => true,
            'data' => [
                'my_earnings_total' => (float) (clone $myEarningsBase)->sum('amount'),
                'my_earnings_unpaid' => (float) (clone $myEarningsBase)->unpaid()->sum('amount'),
                'my_earnings_paid' => (float) (clone $myEarningsBase)->paid()->sum('amount'),
                'my_earnings_this_month' => (float) (clone $myEarningsBase)->whereMonth('created_at', now()->month)->sum('amount'),

                'enumerator_payouts_total' => (float) (clone $payoutsBase)->sum('amount'),
                'enumerator_payouts_unpaid' => (float) (clone $payoutsBase)->unpaid()->sum('amount'),
                'enumerator_payouts_paid' => (float) (clone $payoutsBase)->paid()->sum('amount'),
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
        
        $commissionService = app(CommissionService::class);
        $commission = $commissionService->enterCommission($lead, $payee, (float)$request->amount, $request->user());
 
        return response()->json([
            'success' => true,
            'message' => 'Enumerator commission saved.',
            'data' => [
                'commission' => $commission,
            ],
        ]);
    }
 
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0']);
        $commission = Commission::query()->findOrFail($id);
        $this->authorize('update', $commission);
        
        $commissionService = app(CommissionService::class);
        $commission = $commissionService->editCommission($commission, (float)$request->amount, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Commission updated.',
            'data' => $commission,
        ]);
    }

    public function markPaid(MarkCommissionPaidRequest $request, int $id): JsonResponse
    {
        $commission = Commission::query()->findOrFail($id);
        $this->authorize('update', $commission);
        $commissionService = app(CommissionService::class);
        $commission = $commissionService->markAsPaid($commission, $request->validated(), $request->user());
 
        return response()->json([
            'success' => true,
            'message' => 'Commission marked as paid.',
            'data' => $commission,
        ]);
    }
}
