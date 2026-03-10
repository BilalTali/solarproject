<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\User;
use App\Models\Commission;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // 1. Total Leads
        $totalLeads = Lead::count();
        $newLeadsToday = Lead::where('created_at', '>=', $today)->count();
        $leadsThisMonth = Lead::where('created_at', '>=', $thisMonth)->count();
        
        $installedStatuses = ['installed', 'completed'];
        $totalInstallations = Lead::whereIn('status', $installedStatuses)->count();
        $installationsThisMonth = Lead::whereIn('status', $installedStatuses)->where('updated_at', '>=', $thisMonth)->count();

        // 2. Agents
        $activeAgents = User::where('role', 'agent')->where('status', 'active')->count();
        $pendingAgents = User::where('role', 'agent')->where('status', 'pending')->count();

        // 3. Commissions (Dual Track)
        $totalAgentPaid = Commission::where('payee_role', 'agent')->where('payment_status', 'paid')->sum('amount');
        $totalSuperAgentPaid = Commission::where('payee_role', 'super_agent')->where('payment_status', 'paid')->sum('amount');
        $totalCommissionPaid = $totalAgentPaid + $totalSuperAgentPaid;

        $pendingAgent = Commission::where('payee_role', 'agent')->where('payment_status', 'unpaid')->sum('amount');
        $pendingSuperAgent = Commission::where('payee_role', 'super_agent')->where('payment_status', 'unpaid')->sum('amount');
        $pendingCommission = $pendingAgent + $pendingSuperAgent;

        // 4. Super Agents & Unassigned
        $activeSuperAgents = User::where('role', 'super_agent')->where('status', 'active')->count();
        $unassignedAgentsCount = User::where('role', 'agent')->where('status', 'active')->whereNull('super_agent_id')->count();

        // 4. Pipeline Funnel Counts
        $statusCounts = Lead::select('status', \DB::raw('count(*) as total'))
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
                'recent_leads' => Lead::with('assignedAgent')
                                      ->orderBy('created_at', 'desc')
                                      ->take(10)
                                      ->get(),
                'pending_approvals' => User::where('role', 'agent')
                                           ->where('status', 'pending')
                                           ->orderBy('created_at', 'desc')
                                           ->take(5)
                                           ->get()
            ]
        ]);
    }
}
