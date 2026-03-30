<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Commission;
use App\Models\Lead;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function stats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // 1. Total Leads
        $totalLeads = Lead::query()->count();
        $newLeadsToday = Lead::query()->where(fn ($q) => $q->where('created_at', '>=', $today))->count();
        $leadsThisMonth = Lead::query()->where(fn ($q) => $q->where('created_at', '>=', $thisMonth))->count();

        $installedStatuses = ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED'];
        $totalInstallations = Lead::query()->where(fn ($q) => $q->whereIn('status', $installedStatuses))->count();
        $installationsThisMonth = Lead::query()->where(fn ($q) => $q->whereIn('status', $installedStatuses))
            ->where(fn ($q) => $q->where('updated_at', '>=', $thisMonth))->count();

        // 2. Agents
        $activeAgents = User::query()->where(fn ($q) => $q->where('role', 'agent'))->where(fn ($q) => $q->where('status', 'active'))->count();
        $pendingAgents = User::query()->where(fn ($q) => $q->where('role', 'agent'))->where(fn ($q) => $q->where('status', 'pending'))->count();

        // 3. Commissions (Dual Track)
        $totalAgentPaid = Commission::query()->where(fn ($q) => $q->where('payee_role', 'agent'))->where(fn ($q) => $q->where('payment_status', 'paid'))->sum('amount');
        $totalSuperAgentPaid = Commission::query()->where(fn ($q) => $q->where('payee_role', 'super_agent'))->where(fn ($q) => $q->where('payment_status', 'paid'))->sum('amount');
        $totalCommissionPaid = $totalAgentPaid + $totalSuperAgentPaid;

        $pendingAgent = Commission::query()->where(fn ($q) => $q->where('payee_role', 'agent'))->where(fn ($q) => $q->where('payment_status', 'unpaid'))->sum('amount');
        $pendingSuperAgent = Commission::query()->where(fn ($q) => $q->where('payee_role', 'super_agent'))->where(fn ($q) => $q->where('payment_status', 'unpaid'))->sum('amount');
        $pendingCommission = $pendingAgent + $pendingSuperAgent;

        // 4. Super Agents & Unassigned
        $activeSuperAgents = User::query()->where(fn ($q) => $q->where('role', 'super_agent'))->where(fn ($q) => $q->where('status', 'active'))->count();
        $unassignedAgentsCount = User::query()->where(fn ($q) => $q->where('role', 'agent'))
            ->where(fn ($q) => $q->where('status', 'active'))
            ->where(fn ($q) => $q->whereNull('super_agent_id'))->count();

        // 4. Pipeline Funnel Counts
        $statusCounts = Lead::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'kpis' => [
                    'total_leads' => $totalLeads,
                    'new_leads_today' => $newLeadsToday,
                    'leads_this_month' => $leadsThisMonth,
                    'total_installations' => $totalInstallations,
                    'installations_this_month' => $installationsThisMonth,
                    'active_agents' => $activeAgents,
                    'pending_agents' => $pendingAgents,
                    'total_commission_paid' => $totalCommissionPaid,
                    'pending_commission' => $pendingCommission,
                    'active_super_agents' => $activeSuperAgents,
                    'unassigned_agents_count' => $unassignedAgentsCount,
                ],
                'pipeline' => $statusCounts,
                'recent_leads' => Lead::query()->with(['assignedAgent'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get(),
                'pending_approvals' => User::query()->where(fn ($q) => $q->where('role', 'agent'))
                    ->where(fn ($q) => $q->where('status', 'pending'))
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
            ],
        ]);
    }
}
