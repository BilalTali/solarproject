<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SuperAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAgentDashboardController extends Controller
{
    public function __construct(private SuperAgentService $superAgentService) {}

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->superAgentService->getTeamStats($request->user());

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getQrScans(Request $request): JsonResponse
    {
        $scans = $request->user()->qrScanLogs()
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $scans,
        ]);
    }
}
