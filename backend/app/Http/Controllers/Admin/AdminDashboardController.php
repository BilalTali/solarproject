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
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Multi-tenant Isolation: Get all managed IDs for recursive team visibility
        $managedIds = $isSuperAdmin ? [] : $user->getManagedUserIds();

        // 1. Leads
        $leadQuery = Lead::query();
        if (!$isSuperAdmin) {
            $leadQuery->where(function ($q) use ($managedIds) {
                $q->whereIn('created_by_super_agent_id', $managedIds)
                  ->orWhereIn('submitted_by_agent_id', $managedIds)
                  ->orWhereIn('submitted_by_enumerator_id', $managedIds)
                  ->orWhereIn('assigned_agent_id', $managedIds)
                  ->orWhereIn('assigned_super_agent_id', $managedIds);
            });
        }

        $totalLeads = (clone $leadQuery)->count();
        $newLeadsToday = (clone $leadQuery)->where('created_at', '>=', $today)->count();
        $leadsThisMonth = (clone $leadQuery)->where('created_at', '>=', $thisMonth)->count();

        $installedStatuses = ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED'];
        $totalInstallations = (clone $leadQuery)->whereIn('status', $installedStatuses)->count();
        $installationsThisMonth = (clone $leadQuery)->whereIn('status', $installedStatuses)
            ->where('updated_at', '>=', $thisMonth)->count();

        // 2. Agents
        $agentQuery = User::query()->where('role', 'agent');
        if (!$isSuperAdmin) {
            $agentQuery->whereIn('id', $managedIds);
        }

        $activeAgents = (clone $agentQuery)->where('status', 'active')->count();
        $pendingAgents = (clone $agentQuery)->where('status', 'pending')->count();

        // 3. Commissions
        $commQuery = Commission::query();
        if (!$isSuperAdmin) {
            $commQuery->whereIn('payee_id', $managedIds);
        }

        $totalAgentPaid = (clone $commQuery)->where('payee_role', 'agent')->where('payment_status', 'paid')->sum('amount');
        $totalSuperAgentPaid = (clone $commQuery)->where('payee_role', 'super_agent')->where('payment_status', 'paid')->sum('amount');
        $totalCommissionPaid = $totalAgentPaid + $totalSuperAgentPaid;

        $pendingAgent = (clone $commQuery)->where('payee_role', 'agent')->where('payment_status', 'unpaid')->sum('amount');
        $pendingSuperAgent = (clone $commQuery)->where('payee_role', 'super_agent')->where('payment_status', 'unpaid')->sum('amount');
        $pendingCommission = $pendingAgent + $pendingSuperAgent;

        // 4. Super Agents & Unassigned
        $saQuery = User::query()->where('role', 'super_agent');
        if (!$isSuperAdmin) {
            $saQuery->whereIn('id', $managedIds);
        }
        $activeSuperAgents = $saQuery->where('status', 'active')->count();

        // Unassigned count (only show public pool to Super Admin, or team-pool?)
        // User stated public enumerators report to admin. Unassigned agents usually for Super Admin.
        $unassignedAgentsCount = User::query()->where('role', 'agent')
            ->where('status', 'active')
            ->whereNull('super_agent_id')
            ->when(!$isSuperAdmin, fn($q) => $q->where('parent_id', $user->id)) // Only show their direct unassigned
            ->count();

        // 5. Pipeline Funnel Counts
        $statusCounts = (clone $leadQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // 6. District Distribution
        $districtDistribution = (clone $leadQuery)
            ->select('beneficiary_district', DB::raw('count(*) as total'))
            ->whereNotNull('beneficiary_district')
            ->groupBy('beneficiary_district')
            ->orderBy('total', 'desc')
            ->take(8)
            ->get();

        // 7. Daily Lead Trends (Last 14 Days)
        $trends = (clone $leadQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

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
                'trends' => $trends,
                'district_distribution' => $districtDistribution,
                'recent_leads' => (clone $leadQuery)->with(['assignedAgent'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get(),
                'pending_approvals' => (clone $agentQuery)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
            ],
        ]);
    }
}
