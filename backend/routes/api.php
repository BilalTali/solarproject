<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Api\V1\Auth\AuthController;

// Public
use App\Http\Controllers\Api\V1\Public\LeadController as PublicLeadController;
use App\Http\Controllers\Api\V1\Public\EligibilityController;

// Agent
use App\Http\Controllers\Api\V1\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Api\V1\Agent\LeadController as AgentLeadController;
use App\Http\Controllers\Api\V1\Agent\CommissionController as AgentCommissionController;
use App\Http\Controllers\Api\V1\Agent\NotificationController as AgentNotificationController;
use App\Http\Controllers\Api\V1\Agent\OfferController as AgentOfferController;

// Super Agent
use App\Http\Controllers\Api\V1\SuperAgent\DashboardController as SADashboardController;
use App\Http\Controllers\Api\V1\SuperAgent\AgentController as SAAgentController;
use App\Http\Controllers\Api\V1\SuperAgent\LeadController as SALeadController;
use App\Http\Controllers\Api\V1\SuperAgent\CommissionController as SACommissionController;
use App\Http\Controllers\Api\V1\SuperAgent\NotificationController as SANotificationController;
use App\Http\Controllers\Api\V1\SuperAgent\OfferController as SAOfferController;

// Admin
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AgentController as AdminAgentController;
use App\Http\Controllers\Api\V1\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Api\V1\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\V1\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\V1\Admin\SuperAgentController as AdminSuperAgentController;
use App\Http\Controllers\Api\V1\Admin\CommissionSlabController as AdminCommissionSlabController;
use App\Http\Controllers\Api\V1\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Api\V1\ICardController;
use App\Http\Controllers\Api\V1\JoiningLetterController;

// CMS
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\V1\LeadDocumentController;

// Health check — no auth required — used by uptime monitoring
Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::select('SELECT 1');
        return response()->json([
            'status'    => 'ok',
            'database'  => 'connected',
            'timestamp' => now()->toISOString(),
            'version'   => config('app.version', '1.0.0'),
            'env'       => app()->environment(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'   => 'error',
            'database' => 'disconnected',
            'message'  => 'Service unavailable',
        ], 503);
    }
});

Route::prefix('v1')->as('api.v1.')->group(function () {

    // ==============================
    // PUBLIC ROUTES
    // ==============================
    Route::post('/public/leads', [PublicLeadController::class, 'store']);
    Route::get('/public/leads/track', [PublicLeadController::class, 'track']);
    Route::post('/public/agent-register', [PublicLeadController::class, 'registerAgent']);
    Route::get('/public/eligibility', [EligibilityController::class, 'index']);
    Route::get('/public/commission-slabs', [AdminCommissionSlabController::class, 'index']);
    Route::get('/public/incentive-offers', [AdminOfferController::class, 'index']);

    // CMS Public (no auth required)
    Route::get('/public/settings', [PublicController::class, 'settings']);
    Route::get('/public/achievements', [PublicController::class, 'achievements']);
    Route::get('/public/feedbacks', [PublicController::class, 'feedbacks']);
    Route::get('/public/media', [MediaController::class, 'index']); // Public Reward Winners
    Route::get('/public/feedback', [FeedbackController::class, 'store']);
    Route::get('/public/documents', [DocumentController::class, 'publicIndex']); 
    Route::get('/public/verify-agent/{token}', [PublicController::class, 'verifyAgent']);

    // Signed View for Lead Documents (No auth header needed, secured by signature)
    Route::get('/signed/leads/{ulid}/documents/{id}/view', [\App\Http\Controllers\Api\V1\LeadDocumentController::class, 'viewSigned'])
        ->name('leads.documents.signed-view')
        ->middleware('signed');


    // ==============================
    // AUTHENTICATION
    // ==============================
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/auth/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:6,1');
    Route::post('/auth/agent/login', [AuthController::class, 'agentLogin'])->middleware('throttle:6,1');
    Route::post('/super-agent/auth/login', [AuthController::class, 'superAgentLogin'])->middleware('throttle:6,1');

    // ── Signed Download Routes (No sanctum needed, signature is the key) ────
    Route::get('/icard/download/{userId?}', [ICardController::class, 'download'])
         ->middleware('signed')
         ->name('icard.download');

    Route::get('/joining-letter/download/{userId}', [JoiningLetterController::class, 'download'])
         ->middleware('signed')
         ->name('joining-letter.download');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/set-password', [AuthController::class, 'setPassword']);
        Route::post('/auth/profile-photo', [AuthController::class, 'uploadProfilePhoto']);
        Route::put('/profile/change-password', [\App\Http\Controllers\Api\V1\Shared\ProfileController::class, 'changePassword']);
        Route::get('/icard/download-url', [ICardController::class, 'getDownloadUrl']);
        Route::get('/joining-letter/download-url', [JoiningLetterController::class, 'getDownloadUrl']);
        Route::get('/documents', [DocumentController::class, 'index']); // Auth-only resources
        
        // Lead Documents
        Route::get('/leads/{ulid}/documents/{id}/download', [LeadDocumentController::class, 'download'])->name('leads.documents.download');
        Route::get('/leads/{ulid}/documents/{id}/view-url', [LeadDocumentController::class, 'getSignedUrl']);

        // ==============================
        // AGENT ROUTES
        // ==============================
        Route::middleware('agent')->prefix('agent')->group(function () {
            Route::get('/dashboard/stats', [AgentDashboardController::class, 'stats']);

            Route::get('/profile', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'me']);
            Route::get('/profile/qr-scans', [AgentDashboardController::class, 'getQrScans']);

            Route::get('/leads', [AgentLeadController::class, 'index']);
            Route::get('/leads/{ulid}', [AgentLeadController::class, 'show']);
            Route::post('/leads', [AgentLeadController::class, 'store']);
            Route::put('/leads/{ulid}/resubmit', [AgentLeadController::class, 'resubmit']);
            Route::get('/leads/{ulid}/verification-history', [AgentLeadController::class, 'verificationHistory']);
            Route::post('/leads/{ulid}/documents', [AgentLeadController::class, 'uploadDocument']);

            Route::get('/commissions', [\App\Http\Controllers\Api\V1\Agent\CommissionController::class, 'index']);
            Route::get('/commissions/summary', [\App\Http\Controllers\Api\V1\Agent\CommissionController::class, 'summary']);

            Route::get('/notifications', [AgentNotificationController::class, 'index']);
            Route::put('/notifications/{id}/read', [AgentNotificationController::class, 'markAsRead']);

            // My assigned Super Agent
            Route::get('/my-super-agent', function (\Illuminate\Http\Request $r) {
                $user = $r->user()->load('superAgent');
                return response()->json(['success' => true, 'data' => $user->superAgent]);
            });

            Route::get('/offers', [AgentOfferController::class, 'index']);
            Route::post('/offers/{id}/redeem', [AgentOfferController::class, 'redeem']);
            Route::get('/offers/redemptions', [AgentOfferController::class, 'redemptions']);

            Route::put('/profile', [\App\Http\Controllers\Api\V1\Shared\ProfileController::class, 'update']);
        });

        // ==============================
        // SUPER AGENT ROUTES
        // ==============================
        Route::middleware('super_agent')->prefix('super-agent')->group(function () {
            Route::get('/dashboard/stats', [SADashboardController::class, 'stats']);

            Route::get('/agents', [SAAgentController::class, 'index']);
            Route::post('/agents', [SAAgentController::class, 'store']);
            Route::get('/agents/{agent_id}', [SAAgentController::class, 'show']);

            Route::get('/leads', [SALeadController::class, 'index']);
            Route::post('/leads', [SALeadController::class, 'store']);
            Route::get('/leads/{ulid}', [SALeadController::class, 'show']);
            Route::put('/leads/{ulid}/status', [SALeadController::class, 'updateStatus']);
            Route::put('/leads/{ulid}/verify', [SALeadController::class, 'verify']);
            Route::put('/leads/{ulid}/revert', [SALeadController::class, 'revert']);
            Route::put('/leads/{ulid}/notes', [SALeadController::class, 'updateNotes']);
            Route::put('/leads/{ulid}/assign', [SALeadController::class, 'assign']);
            Route::put('/leads/{ulid}/assign-agent', [SALeadController::class, 'assignToAgent']);
            Route::get('/leads/{ulid}/verification-history', [SALeadController::class, 'verificationHistory']);
            Route::post('/leads/{ulid}/documents', [SALeadController::class, 'uploadDocument']);

            Route::get('/commissions', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionController::class, 'index']);
            Route::get('/commissions/summary', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionController::class, 'summary']);
            Route::post('/leads/{ulid}/commission/agent', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionController::class, 'enterAgentCommission']);
            Route::put('/commissions/{id}', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionController::class, 'update']);
            Route::put('/commissions/{id}/mark-paid', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionController::class, 'markPaid']);
            Route::get('/leads/{ulid}/commissions', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionController::class, 'getLeadCommissions']);

            Route::get('/notifications', [SANotificationController::class, 'index']);
            Route::put('/notifications/{id}/read', [SANotificationController::class, 'markRead']);
            Route::put('/notifications/mark-all-read', [SANotificationController::class, 'markAllRead']);

            Route::get('/offers', [SAOfferController::class, 'index']);
            Route::get('/offers/team-performance', [SAOfferController::class, 'teamPerformance']);
            Route::get('/offers/absorbed-points', [SAOfferController::class, 'absorbedPoints']);
            Route::post('/offers/absorbed-points/{absorbedPoint}/claim', [SAOfferController::class, 'claimAbsorbed']);
            Route::post('/offers/{id}/redeem', [SAOfferController::class, 'redeem']);
            Route::get('/offers/redemptions', [SAOfferController::class, 'redemptions']);

            Route::get('/profile', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'me']);
            Route::get('/profile/qr-scans', [SADashboardController::class, 'getQrScans']);
            Route::put('/profile', [\App\Http\Controllers\Api\V1\Shared\ProfileController::class, 'update']);
            Route::put('/change-password', [\App\Http\Controllers\Api\V1\Shared\ProfileController::class, 'changePassword']);
        });

        // ADMIN ROUTES
        // ==============================
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);

            // Agents
            Route::get('/agents/unassigned', [AdminSuperAgentController::class, 'unassignedAgents']);
            Route::put('/agents/{id}/status', [AdminAgentController::class, 'updateStatus']);
            Route::apiResource('agents', AdminAgentController::class);
            Route::post('/agents/{id}/regenerate-qr', [AdminAgentController::class, 'regenerateQr']);
            Route::get('/agents/{id}/qr-scans', [AdminAgentController::class, 'getQrScans']);

            // Super Agents CRUD
            Route::get('/super-agents', [AdminSuperAgentController::class, 'index']);
            Route::post('/super-agents', [AdminSuperAgentController::class, 'store']);
            Route::get('/super-agents/{id}', [AdminSuperAgentController::class, 'show']);
            Route::put('/super-agents/{id}', [AdminSuperAgentController::class, 'update']);
            Route::put('/super-agents/{id}/status', [AdminSuperAgentController::class, 'updateStatus']);
            Route::delete('/super-agents/{id}', [AdminSuperAgentController::class, 'destroy']);
            Route::post('/super-agents/{id}/regenerate-qr', [AdminSuperAgentController::class, 'regenerateQr']);
            Route::get('/super-agents/{id}/qr-scans', [AdminSuperAgentController::class, 'getQrScans']);

            // Super Agent Team Assignment
            Route::get('/super-agents/{id}/agents', [AdminSuperAgentController::class, 'teamAgents']);
            Route::post('/super-agents/{id}/agents/assign', [AdminSuperAgentController::class, 'assignAgent']);
            Route::post('/super-agents/{id}/agents/assign-bulk', [AdminSuperAgentController::class, 'assignAgentsBulk']);
            Route::delete('/super-agents/{id}/agents/{agent_id}', [AdminSuperAgentController::class, 'unassignAgent']);
            Route::get('/super-agents/{id}/team-log', [AdminSuperAgentController::class, 'teamLog']);



            // Leads
            Route::get('/leads', [AdminLeadController::class, 'index']);
            Route::get('/leads/{ulid}', [AdminLeadController::class, 'show']);
            Route::put('/leads/{ulid}', [AdminLeadController::class, 'update']);
            Route::put('/leads/{ulid}/status', [AdminLeadController::class, 'updateStatus']);
            Route::put('/leads/{ulid}/assign', [AdminLeadController::class, 'assign']);
            Route::put('/leads/{ulid}/assign-super-agent', [AdminLeadController::class, 'assignSuperAgent']);
            Route::put('/leads/{ulid}/assign-agent', [AdminLeadController::class, 'assignAgent']);
            Route::put('/leads/{ulid}/override-verification', [AdminLeadController::class, 'overrideVerification']);
            Route::post('/leads/{ulid}/documents', [AdminLeadController::class, 'uploadDocument']);

            // Commissions
            Route::get('/commissions', [\App\Http\Controllers\Api\V1\Admin\CommissionController::class, 'index']);
            Route::get('/commissions/summary', [\App\Http\Controllers\Api\V1\Admin\CommissionController::class, 'summary']);
            Route::post('/leads/{ulid}/commission/super-agent', [\App\Http\Controllers\Api\V1\Admin\CommissionController::class, 'enterSuperAgentCommission']);
            Route::post('/leads/{ulid}/commission/agent-direct', [\App\Http\Controllers\Api\V1\Admin\CommissionController::class, 'enterDirectAgentCommission']);
            Route::put('/commissions/{id}', [\App\Http\Controllers\Api\V1\Admin\CommissionController::class, 'update']);
            Route::put('/commissions/{id}/mark-paid', [\App\Http\Controllers\Api\V1\Admin\CommissionController::class, 'markPaid']);
            Route::get('/leads/{ulid}/commissions', [\App\Http\Controllers\Api\V1\Admin\CommissionController::class, 'getLeadCommissions']);

            // Reports
            Route::get('/reports/pipeline', [AdminReportController::class, 'pipelineSummary']);
            Route::get('/reports/agent-performance', [AdminReportController::class, 'agentPerformance']);
            Route::get('/reports/geography', [AdminReportController::class, 'geographicDistribution']);
            Route::get('/reports/monthly-trend', [AdminReportController::class, 'monthlyTrend']);
            Route::get('/reports/super-agent-performance', [AdminReportController::class, 'superAgentPerformance']);

            // Commission Slabs
            Route::get('/commission-slabs', [AdminCommissionSlabController::class, 'index']);
            Route::post('/commission-slabs', [AdminCommissionSlabController::class, 'store']);
            Route::put('/commission-slabs/{id}', [AdminCommissionSlabController::class, 'update']);
            Route::delete('/commission-slabs/{id}', [AdminCommissionSlabController::class, 'destroy']);

            // Incentive Offers v2
            Route::get('/offers/redemptions', [AdminOfferController::class, 'redemptions']);
            Route::post('/offers/redemptions/{id}/approve', [AdminOfferController::class, 'approveRedemption']);
            Route::post('/offers/redemptions/{id}/deliver', [AdminOfferController::class, 'deliveredRedemption']);
            Route::get('/offers/{offer}/participants', [AdminOfferController::class, 'participants']);
            Route::get('/offers/absorbed-points', [AdminOfferController::class, 'absorbedPoints']);
            Route::post('/offers/absorbed-points/{absorbedPoint}/approve', [AdminOfferController::class, 'approveAbsorption']);
            Route::post('/offers/{offer}/trigger-expiry', [AdminOfferController::class, 'triggerExpiry']);
            Route::apiResource('offers', AdminOfferController::class);

            // Settings
            Route::get('/settings', [AdminSettingController::class, 'index']);
            Route::put('/settings', [AdminSettingController::class, 'updateBulk']);
            Route::post('/settings/upload', [AdminSettingController::class, 'uploadFile']);
            Route::put('/profile', [AdminSettingController::class, 'updateProfile']);

            // Achievements (admin)
            Route::get('/achievements', [AchievementController::class, 'index']);
            Route::post('/achievements', [AchievementController::class, 'store']);
            Route::put('/achievements/{achievement}', [AchievementController::class, 'update']);
            Route::delete('/achievements/{achievement}', [AchievementController::class, 'destroy']);

            // Feedbacks (admin)
            Route::get('/feedbacks', [FeedbackController::class, 'index']);
            Route::post('/feedbacks/{feedback}/reply', [FeedbackController::class, 'reply']);
            Route::put('/feedbacks/{feedback}/toggle-publish', [FeedbackController::class, 'togglePublish']);
            Route::delete('/feedbacks/{feedback}', [FeedbackController::class, 'destroy']);

            // Media (admin)
            Route::get('/media', [MediaController::class, 'index']);
            Route::post('/media', [MediaController::class, 'store']);
            Route::patch('/media/{media}', [MediaController::class, 'update']); // PATCH for updates with files
            Route::delete('/media/{media}', [MediaController::class, 'destroy']);

            // Documents (admin)
            Route::get('/documents', [DocumentController::class, 'index']);
            Route::post('/documents', [DocumentController::class, 'store']);
            Route::patch('/documents/{document}', [DocumentController::class, 'update']);
            Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
        });
    });
});
