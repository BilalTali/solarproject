<?php

namespace App\Http\Controllers\Api\V1\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        // Optimize counts/sums with a single pass if possible, or just fix the fields
        $totalLeads = $user->submittedLeads()->count();
        $leadsInstalled = $user->submittedLeads()->whereIn('status', ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED'])->count();

        // Commissions use 'payment_status' and are grouped in our virtual attribute or direct query
        $commStats = $user->commissions()
            ->selectRaw('payment_status, SUM(amount) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $pendingCommission = (float) ($commStats['unpaid'] ?? 0);
        $totalEarned = (float) ($commStats['paid'] ?? 0);

        $thisMonthEarned = $user->commissions()
            ->where('payment_status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $lastMonth = now()->subMonth();
        $lastMonthEarned = $user->commissions()
            ->where('payment_status', 'paid')
            ->whereMonth('paid_at', $lastMonth->month)
            ->whereYear('paid_at', $lastMonth->year)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_leads' => $totalLeads,
                'leads_installed' => $leadsInstalled,
                'pending_commission' => $pendingCommission,
                'total_earned' => $totalEarned,
                'this_month_earned' => (float) $thisMonthEarned,
                'last_month_earned' => (float) $lastMonthEarned,
                'recent_leads' => $user->submittedLeads()
                    ->with(['documents', 'commissions'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
            ],
        ]);
    }

    public function getQrScans(Request $request)
    {
        $user = $request->user();
        $scans = $user->qrScanLogs()
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $scans,
        ]);
    }
}
