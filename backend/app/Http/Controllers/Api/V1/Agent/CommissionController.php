<?php

namespace App\Http\Controllers\Api\V1\Agent;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $agent = $request->user();
        
        $query = Commission::with(['lead', 'enteredBy'])
            ->forPayee($agent->id)
            ->where('payee_role', 'agent');

        if ($request->filter === 'pending_my_payment') {
            $query->unpaid();
        } elseif ($request->filter === 'fully_paid') {
            $query->paid();
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

    public function summary(Request $request): JsonResponse
    {
        $agent = $request->user();

        $query = Commission::forPayee($agent->id)->where('payee_role', 'agent');

        return response()->json([
            'success' => true,
            'data' => [
                'total_earned' => (float) (clone $query)->sum('amount'),
                'total_unpaid' => (float) (clone $query)->unpaid()->sum('amount'),
                'total_paid'   => (float) (clone $query)->paid()->sum('amount'),
                'this_month'   => (float) (clone $query)->whereMonth('created_at', now()->month)->sum('amount'),
                'last_month'   => (float) (clone $query)->whereMonth('created_at', now()->subMonth()->month)->sum('amount'),
            ]
        ]);
    }
}
