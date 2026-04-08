<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\Commission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    /** Global stats for Super Admin */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_admins' => User::roleAdmin()->count(),
            'total_super_agents' => User::roleSuperAgent()->count(),
            'total_agents' => User::roleAgent()->count(),
            'total_enumerators' => User::roleEnumerator()->count(),
            'total_leads' => Lead::count(),
            'total_commissions' => (float) Commission::sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
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
        $query = User::roleEnumerator()
            ->with(['parentAgent'])
            ->withCount(['enumeratorLeads'])
            ->latest();

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(fn($q) => $q->where('name', 'like', $search)->orWhere('enumerator_id', 'like', $search));
        }

        return response()->json(['success' => true, 'data' => $query->paginate($request->per_page ?? 20)]);
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

    // ══════════════════════════════════════════════════════════════
    // SUPER ADMIN — ADMIN COMMISSION SETTLEMENT
    // ══════════════════════════════════════════════════════════════

    /** Summary of Admin commission amounts (pending & paid) */
    public function commissionsSummary(): JsonResponse
    {
        $base = Commission::query()->where(fn($q) => $q->where('payee_role', 'admin'));

        return response()->json([
            'success' => true,
            'data' => [
                'admin_unpaid_count'  => (clone $base)->where(fn($q) => $q->where('payment_status', 'unpaid'))->count(),
                'admin_unpaid_amount' => (float)(clone $base)->where(fn($q) => $q->where('payment_status', 'unpaid'))->sum('amount'),
                'admin_paid_amount'   => (float)(clone $base)->where(fn($q) => $q->where('payment_status', 'paid'))->sum('amount'),
                'all_time_disbursed'  => (float) Commission::query()->where(fn($q) => $q->where('payment_status', 'paid'))->sum('amount'),
            ],
        ]);
    }

    /** Paginated list of Admin commissions with optional status filter */
    public function commissionsList(Request $request): JsonResponse
    {
        $query = Commission::with(['payee', 'lead', 'enteredBy', 'paidBy'])
            ->where(fn($q) => $q->where('payee_role', 'admin'))
            ->latest();

        if ($request->filled('status') && in_array($request->status, ['paid', 'unpaid'])) {
            $status = $request->status;
            $query->where(fn($q) => $q->where('payment_status', $status));
        }

        if ($request->filled('payee_id')) {
            $payeeId = $request->payee_id;
            $query->where(fn($q) => $q->where('payee_id', $payeeId));
        }

        $commissions = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $commissions->items(),
            'meta'    => [
                'current_page' => $commissions->currentPage(),
                'last_page'    => $commissions->lastPage(),
                'total'        => $commissions->total(),
            ],
        ]);
    }

    /** Settle (mark as paid) a commission for an Admin */
    public function settleCommission(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'payment_method'    => 'required|string|in:bank_transfer,upi,cash,cheque',
            'payment_reference' => 'required|string|max:255',
            'payment_notes'     => 'nullable|string|max:1000',
        ]);

        $commission = Commission::query()
            ->where(fn($q) => $q->where('payee_role', 'admin'))
            ->findOrFail($id);

        if ($commission->payment_status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Commission is already marked as paid.'], 422);
        }

        $commission->update([
            'payment_status'    => 'paid',
            'paid_at'           => now(),
            'paid_by'           => $request->user()->id,
            'payment_method'    => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_notes'     => $request->payment_notes ?? null,
        ]);

        app(\App\Services\NotificationService::class)->send(
            $commission->payee_id,
            'commission_paid',
            '✅ Commission Payment Received',
            "₹{$commission->amount} has been settled by Super Admin. Ref: {$request->payment_reference}",
            ['commission_id' => $commission->id, 'amount' => $commission->amount]
        );

        return response()->json([
            'success' => true,
            'message' => 'Admin commission settled successfully.',
            'data'    => $commission->fresh(['payee', 'paidBy']),
        ]);
    }
}

