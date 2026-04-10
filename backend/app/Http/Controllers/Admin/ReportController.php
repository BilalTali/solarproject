<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function pipelineSummary(Request $request)
    {
        $user = $request->user();
        $query = Lead::select('status', DB::raw('count(*) as total'));

        if (!$user->isSuperAdmin()) {
            $managedIds = $user->getManagedUserIds();
            $query->where(function ($q) use ($managedIds) {
                $q->whereIn('created_by_super_agent_id', $managedIds)
                  ->orWhereIn('submitted_by_agent_id', $managedIds)
                  ->orWhereIn('assigned_super_agent_id', $managedIds)
                  ->orWhereIn('assigned_agent_id', $managedIds);
            });
        }

        if ($request->has('status')) {
            $query->where(fn ($q) => $q->where('status', (string) $request->status));
        }
        $statusCounts = $query->groupBy('status')
            ->pluck('total', 'status');

        $total = Lead::query();
        if (!$user->isSuperAdmin()) {
            $managedIds = $user->getManagedUserIds();
            $total->where(function ($q) use ($managedIds) {
                $q->whereIn('created_by_super_agent_id', $managedIds)
                  ->orWhereIn('submitted_by_agent_id', $managedIds)
                  ->orWhereIn('assigned_super_agent_id', $managedIds)
                  ->orWhereIn('assigned_agent_id', $managedIds);
            });
        }
        $total = $total->count();

        // Define funnel steps to calculate drop-offs
        $funnel = [
            'new' => $statusCounts['NEW'] ?? 0,
            'registered' => $statusCounts['REGISTERED'] ?? 0,
            'site_survey' => $statusCounts['SITE_SURVEY'] ?? 0,
            'completed' => $statusCounts['COMPLETED'] ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'funnel' => $funnel,
                'status_distribution' => $statusCounts,
            ],
        ]);
    }

    public function agentPerformance(Request $request)
    {
        $user = $request->user();
        $agentsQuery = User::query()->where('role', 'agent');
        if (!$user->isSuperAdmin()) {
            $managedIds = $user->getManagedUserIds();
            $agentsQuery->whereIn('id', $managedIds);
        }

        $agents = $agentsQuery
            ->select('id', 'name', 'agent_id')
            ->withCount('submittedLeads as total_source_leads')
            ->withCount('assignedLeads as total_assigned_leads')
            ->withCount(['assignedLeads as installed_leads' => function ($q) use ($request) {
                $q->where(fn($q2) => $q2->whereIn('status', ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED']));
                if ($request->has('active')) {
                    // Note: 'is_active' is typically a user property, not a lead property.
                    // This filter will apply to the assigned leads, not the agent.
                    $q->where(fn ($subQ) => $subQ->where('is_active', (bool) filter_var($request->active, FILTER_VALIDATE_BOOLEAN)));
                }
            }])
            ->withSum(['commissions as total_earned' => function ($q) {
                $q->where(fn($q2) => $q2->where('payment_status', 'paid'));
            }], 'amount')
            ->get();

        foreach ($agents as $agent) {
            $agent->total_leads = $agent->total_source_leads + $agent->total_assigned_leads;
            $agent->conversion_rate = $agent->total_leads > 0
                ? round(($agent->installed_leads / $agent->total_leads) * 100, 2)
                : 0;
        }

        // Sort by total_leads and paginate
        $sortedAgents = $agents->sortByDesc('total_leads')->values();


        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedAgents->forPage($page, $perPage),
            $sortedAgents->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'data' => $paginated,
        ]);
    }

    public function geographicDistribution(Request $request)
    {
        $user = $request->user();
        $leadQuery = Lead::query();
        if (!$user->isSuperAdmin()) {
            $managedIds = $user->getManagedUserIds();
            $leadQuery->where(function ($q) use ($managedIds) {
                $q->whereIn('created_by_super_agent_id', $managedIds)
                  ->orWhereIn('submitted_by_agent_id', $managedIds)
                  ->orWhereIn('assigned_super_agent_id', $managedIds)
                  ->orWhereIn('assigned_agent_id', $managedIds);
            });
        }

        $byState = (clone $leadQuery)->select('beneficiary_state as state', DB::raw('count(*) as count'))
            ->groupBy('beneficiary_state')
            ->orderByDesc('count')
            ->get();

        $topDistricts = (clone $leadQuery)->select('beneficiary_district as district', 'beneficiary_state as state', DB::raw('count(*) as count'))
            ->groupBy('beneficiary_state', 'beneficiary_district')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'by_state' => $byState,
                'top_districts' => $topDistricts,
            ],
        ]);
    }

    public function monthlyTrend(Request $request)
    {
        $user = $request->user();
        $leadQuery = Lead::query();
        if (!$user->isSuperAdmin()) {
            $managedIds = $user->getManagedUserIds();
            $leadQuery->where(function ($q) use ($managedIds) {
                $q->whereIn('created_by_super_agent_id', $managedIds)
                  ->orWhereIn('submitted_by_agent_id', $managedIds)
                  ->orWhereIn('assigned_super_agent_id', $managedIds)
                  ->orWhereIn('assigned_agent_id', $managedIds);
            });
        }

        $months = 12;
        $trend = collect();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            $newLeads = (clone $leadQuery)->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $installations = (clone $leadQuery)->where(fn($q) => $q->whereIn('status', ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED']))
                ->whereMonth('updated_at', $date->month)
                ->whereYear('updated_at', $date->year)
                ->count();

            $trend->push((object) [
                'month' => $date->format('M Y'),
                'new_leads' => $newLeads,
                'installations' => $installations,
            ]);
        }


        return response()->json([
            'success' => true,
            'data' => $trend,
        ]);
    }

    public function superAgentPerformance(Request $request)
    {
        $user = $request->user();
        $saQuery = User::query()->where('role', 'super_agent');
        if (!$user->isSuperAdmin()) {
            $managedIds = $user->getManagedUserIds();
            $saQuery->whereIn('id', $managedIds);
        }

        $superAgents = $saQuery
            ->select('id', 'name', 'agent_id', 'super_agent_code')
            ->withCount('managedAgents as team_size')
            ->get();

        foreach ($superAgents as $sa) {
            $agentIds = $sa->managedAgents()->pluck('id');

            // Aggregated Team Metrics (Count leads where team is submitter OR assignee)
            $teamLeadsQuery = Lead::where(function ($q) use ($agentIds) {
                $q->whereIn('submitted_by_agent_id', $agentIds)
                    ->orWhereIn('assigned_agent_id', $agentIds);
            });

            $sa->total_leads = (clone $teamLeadsQuery)->count();
            $sa->total_installed = (clone $teamLeadsQuery)->where(fn($q) => $q->whereIn('status', ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED']))->count();

            $sa->conversion_rate = $sa->total_leads > 0
                ? round(($sa->total_installed / $sa->total_leads) * 100, 2)
                : 0;

            // Top Performing Agents in this SA's team
            $sa->top_agents = User::whereIn('id', $agentIds)
                ->select('id', 'name', 'agent_id')
                ->withCount(['submittedLeads as total_source_leads'])
                ->withCount(['assignedLeads as total_assigned_leads'])
                ->withCount(['assignedLeads as installed_leads' => function ($q) {
                    $q->whereIn('status', ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED']);
                }])
                ->get();

            foreach ($sa->top_agents as $agent) {
                $agent->total_leads = $agent->total_source_leads + $agent->total_assigned_leads;
                $agent->conversion_rate = $agent->total_leads > 0
                    ? round(($agent->installed_leads / $agent->total_leads) * 100, 2)
                    : 0;
            }

            // Sort top agents by total leads and take top 5
            $sa->top_agents = $sa->top_agents->sortByDesc('total_leads')->take(5)->values();
        }

        // Sort Super Agents by their team's total leads
        $sortedSAs = $superAgents->sortByDesc('total_leads')->values();

        return response()->json([
            'success' => true,
            'data' => $sortedSAs,
        ]);
    }
}
