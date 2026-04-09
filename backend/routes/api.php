<?php

use App\Http\Controllers\Solar\AchievementController;
// Auth
use App\Http\Controllers\Solar\DocumentController;
// Public
use App\Http\Controllers\Solar\FeedbackController;
use App\Http\Controllers\Admin\MediaController;
// Agent
use App\Http\Controllers\Solar\PublicController;
use App\Http\Controllers\Admin\FAQController as AdminFAQController;
use App\Http\Controllers\Admin\AdminAgentController as AdminAgentController;
use App\Http\Controllers\Api\V1\Admin\CommissionSlabController as AdminCommissionSlabController;
use App\Http\Controllers\Admin\AdminDashboardController as AdminDashboardController;
// Super Agent
use App\Http\Controllers\Admin\AdminLeadController as AdminLeadController;
use App\Http\Controllers\Admin\AdminOfferController as AdminOfferController;
use App\Http\Controllers\Admin\OperatorController as AdminOperatorController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\AdminSuperAgentController as AdminSuperAgentController;
// Super Admin
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\MonitoringController;
// Admin
use App\Http\Controllers\Admin\AgentDashboardController as AgentDashboardController;
use App\Http\Controllers\Admin\AgentLeadController as AgentLeadController;
use App\Http\Controllers\Admin\AgentNotificationController as AgentNotificationController;
use App\Http\Controllers\Admin\AgentOfferController as AgentOfferController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ICardController;
use App\Http\Controllers\Admin\JoiningLetterController;
use App\Http\Controllers\Solar\LeadDocumentController;
use App\Http\Controllers\Solar\EligibilityController;
use App\Http\Controllers\Solar\PortalLeadController as PublicLeadController;
// CMS
use App\Http\Controllers\Admin\SuperAgentAgentController as SAAgentController;
use App\Http\Controllers\Admin\SuperAgentDashboardController as SADashboardController;
use App\Http\Controllers\Admin\SuperAgentLeadController as SALeadController;
use App\Http\Controllers\Admin\SuperAgentNotificationController as SANotificationController;
use App\Http\Controllers\Admin\SuperAgentOfferController as SAOfferController;
use App\Http\Controllers\SuperAdmin\ChatbotController as SAChatbotController;
use App\Http\Controllers\SuperAdmin\FAQController as SAFAQController;
use Illuminate\Support\Facades\Route;

// Health check — no auth required — used by uptime monitoring
Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::select('SELECT 1');

        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'env' => app()->environment(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'message' => 'Service unavailable',
        ], 503);
    }
});

/** @var \Illuminate\Routing\RouteRegistrar $api */
$api = Route::prefix('v1');
$api->as('api.v1.')->group(function () {

    // ==============================
    // PUBLIC ROUTES
    // ==============================
    Route::get('/whatsapp/webhook',  [\App\Http\Controllers\Solar\WhatsAppWebhookController::class, 'verify']);
    Route::post('/whatsapp/webhook', [\App\Http\Controllers\Solar\WhatsAppWebhookController::class, 'handle'])
         ->middleware('throttle:300,1');

    Route::post('/public/leads', [PublicLeadController::class, 'store'])->middleware('throttle:forms');
    Route::get('/public/leads/track', [PublicLeadController::class, 'track']);
    Route::post('/public/agent-register', [PublicLeadController::class, 'registerAgent'])->middleware('throttle:forms');
    Route::post('/public/enumerator-register', [PublicLeadController::class, 'registerEnumerator'])->middleware('throttle:forms');
    Route::get('/public/eligibility', [EligibilityController::class, 'index'])->middleware(\App\Http\Middleware\CacheResponse::class);
    // Route::get('/public/commission-slabs', [AdminCommissionSlabController::class, 'index']);
    Route::get('/public/incentive-offers', [AdminOfferController::class, 'index'])->middleware(\App\Http\Middleware\CacheResponse::class);

    // CMS Public (no auth required)
    Route::get('/public/settings', [PublicController::class, 'settings'])->middleware(\App\Http\Middleware\CacheResponse::class);
    Route::get('/public/achievements', [PublicController::class, 'achievements'])->middleware(\App\Http\Middleware\CacheResponse::class);
    Route::get('/public/feedbacks', [PublicController::class, 'feedbacks'])->middleware(\App\Http\Middleware\CacheResponse::class);
    Route::get('/public/media', [MediaController::class, 'index'])->middleware(\App\Http\Middleware\CacheResponse::class); // Public Reward Winners
    Route::get('/public/feedback', [FeedbackController::class, 'store'])->middleware('throttle:forms');
    Route::get('/public/documents', [DocumentController::class, 'publicIndex'])->middleware(\App\Http\Middleware\CacheResponse::class);
    Route::get('/public/verify-agent/{token}', [PublicController::class, 'verifyAgent']);
    Route::get('/public/help', [PublicController::class, 'help']);
    Route::get('/public/support-contacts', [\App\Http\Controllers\Admin\ChatbotController::class, 'publicContacts'])->middleware(\App\Http\Middleware\CacheResponse::class);

    // Signed View for Lead Documents (No auth header needed, secured by signature)
    Route::get('/signed/leads/{ulid}/documents/{id}/view', [LeadDocumentController::class, 'viewSigned'])
        ->name('leads.documents.signed-view')
        ->middleware('signed');

    Route::get('/signed/documents/{id}/view/{type}', [DocumentController::class, 'viewSigned'])
        ->name('documents.signed-view')
        ->middleware('signed');

    // ==============================
    // AUTHENTICATION
    // ==============================
    Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:6,1');
    Route::post('/auth/login-otp', [AuthController::class, 'loginOtp'])->middleware('throttle:6,1');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    // ── Signed Download Routes (No sanctum needed, signature is the key) ────
    Route::get('/icard/download/{userId?}', [ICardController::class, 'download'])
        ->middleware('signed')
        ->name('icard.download');

    Route::get('/joining-letter/download/{userId}', [JoiningLetterController::class, 'download'])
        ->middleware('signed')
        ->name('joining-letter.download');

    Route::middleware(['auth:sanctum', 'throttle:auth'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/set-password', [AuthController::class, 'setPassword']);
        Route::post('/auth/profile-photo', [AuthController::class, 'uploadProfilePhoto']);
        Route::put('/profile/change-password', [\App\Http\Controllers\Auth\SharedProfileController::class, 'changePassword']);
        Route::get('/icard/download-url', [ICardController::class, 'getDownloadUrl']);
        Route::get('/joining-letter/download-url', [JoiningLetterController::class, 'getDownloadUrl']);
        Route::get('/documents', [DocumentController::class, 'index']); // Auth-only resources
        Route::get('/documents/{id}/view-url', [DocumentController::class, 'getSignedUrl']);

        // Lead Documents
        Route::get('/leads/{ulid}/documents/{id}/download', [LeadDocumentController::class, 'download'])->name('leads.documents.download');
        Route::get('/leads/{ulid}/documents/{id}/view-url', [LeadDocumentController::class, 'getSignedUrl']);

        // Authenticated settings lookup works for all sub-roles to fetch their parent admin's branding
        Route::get('/admin/settings', [AdminSettingController::class, 'index']);

        // ==============================
        // ENUMERATOR ROUTES
        // ==============================
        Route::middleware('enumerator')->prefix('enumerator')->group(function () {
            Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\EnumeratorDashboardController::class, 'stats']);
            Route::get('/profile', [AuthController::class, 'me']);
            Route::put('/profile', [\App\Http\Controllers\Auth\SharedProfileController::class, 'update']);
            
            Route::get('/leads', [\App\Http\Controllers\Admin\EnumeratorLeadController::class, 'index']);
            Route::post('/leads', [\App\Http\Controllers\Admin\EnumeratorLeadController::class, 'store']);
            Route::get('/leads/{ulid}', [\App\Http\Controllers\Admin\EnumeratorLeadController::class, 'show']);
            Route::post('/leads/{ulid}/documents', [\App\Http\Controllers\Admin\EnumeratorLeadController::class, 'uploadDocument']);
            
            Route::get('/commissions', [\App\Http\Controllers\Admin\EnumeratorCommissionController::class, 'index']);
            
            Route::get('/notifications', [\App\Http\Controllers\Admin\EnumeratorNotificationController::class, 'index']);
            Route::put('/notifications/{id}/read', [\App\Http\Controllers\Admin\EnumeratorNotificationController::class, 'markAsRead']);
            
            Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'index']);
            Route::post('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'store']);
        });

        // ==============================
        // AGENT ROUTES
        // ==============================
        Route::middleware('agent')->prefix('agent')->group(function () {
            Route::get('/dashboard/stats', [AgentDashboardController::class, 'stats']);

            Route::get('/profile', [AuthController::class, 'me']);
            Route::get('/profile/qr-scans', [AgentDashboardController::class, 'getQrScans']);

            Route::get('/leads', [AgentLeadController::class, 'index']);
            Route::get('/leads/{ulid}', [AgentLeadController::class, 'show']);
            Route::post('/leads', [AgentLeadController::class, 'store']);
            Route::put('/leads/{ulid}/resubmit', [AgentLeadController::class, 'resubmit']);
            Route::put('/leads/{ulid}/verify', [AgentLeadController::class, 'verify']);
            Route::put('/leads/{ulid}/revert', [AgentLeadController::class, 'revert']);
            Route::get('/leads/{ulid}/verification-history', [AgentLeadController::class, 'verificationHistory']);
            Route::post('/leads/{ulid}/documents', [AgentLeadController::class, 'uploadDocument']);

            Route::get('/commissions', [\App\Http\Controllers\Admin\AgentCommissionController::class, 'index']);
            Route::get('/commissions/summary', [\App\Http\Controllers\Admin\AgentCommissionController::class, 'summary']);
            Route::post('/leads/{ulid}/commission/enumerator', [\App\Http\Controllers\Admin\AgentCommissionController::class, 'enterEnumeratorCommission']);
            Route::put('/commissions/{id}', [\App\Http\Controllers\Admin\AgentCommissionController::class, 'update']);
            Route::put('/commissions/{id}/mark-paid', [\App\Http\Controllers\Admin\AgentCommissionController::class, 'markPaid']);

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

            Route::put('/profile', [\App\Http\Controllers\Auth\SharedProfileController::class, 'update']);

            // Enumerators
            Route::apiResource('enumerators', \App\Http\Controllers\Admin\AgentEnumeratorController::class)->names('agent.enumerators');
            Route::put('/enumerators/{id}/status', [\App\Http\Controllers\Admin\AgentEnumeratorController::class, 'updateStatus']);

            Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'index']);
            Route::post('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'store']);
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
            Route::put('/leads/{ulid}/verify', [SALeadController::class, 'verify']);
            Route::put('/leads/{ulid}/revert', [SALeadController::class, 'revert']);
            Route::put('/leads/{ulid}/notes', [SALeadController::class, 'updateNotes']);
            Route::put('/leads/{ulid}/assign', [SALeadController::class, 'assign']);
            Route::put('/leads/{ulid}/assign-agent', [SALeadController::class, 'assignToAgent']);
            Route::get('/leads/{ulid}/verification-history', [SALeadController::class, 'verificationHistory']);
            Route::post('/leads/{ulid}/documents', [SALeadController::class, 'uploadDocument']);

            Route::get('/commissions', [\App\Http\Controllers\Admin\SuperAgentCommissionController::class, 'index']);
            Route::get('/commissions/summary', [\App\Http\Controllers\Admin\SuperAgentCommissionController::class, 'summary']);
            Route::post('/leads/{ulid}/commission/agent', [\App\Http\Controllers\Admin\SuperAgentCommissionController::class, 'enterAgentCommission']);
            Route::post('/leads/{ulid}/commission/enumerator', [\App\Http\Controllers\Admin\SuperAgentCommissionController::class, 'enterEnumeratorCommission']);
            Route::put('/commissions/{id}', [\App\Http\Controllers\Admin\SuperAgentCommissionController::class, 'update']);
            Route::put('/commissions/{id}/mark-paid', [\App\Http\Controllers\Admin\SuperAgentCommissionController::class, 'markPaid']);
            Route::get('/leads/{ulid}/commissions', [\App\Http\Controllers\Admin\SuperAgentCommissionController::class, 'getLeadCommissions']);

            Route::get('/notifications', [SANotificationController::class, 'index']);
            Route::put('/notifications/{id}/read', [SANotificationController::class, 'markRead']);
            Route::put('/notifications/mark-all-read', [SANotificationController::class, 'markAllRead']);

            Route::get('/offers', [SAOfferController::class, 'index']);
            Route::get('/offers/team-performance', [SAOfferController::class, 'teamPerformance']);
            Route::get('/offers/absorbed-points', [SAOfferController::class, 'absorbedPoints']);
            Route::post('/offers/absorbed-points/{absorbedPoint}/claim', [SAOfferController::class, 'claimAbsorbed']);
            Route::post('/offers/{id}/redeem', [SAOfferController::class, 'redeem']);
            Route::get('/offers/redemptions', [SAOfferController::class, 'redemptions']);

            Route::get('/profile', [AuthController::class, 'me']);
            Route::get('/profile/qr-scans', [SADashboardController::class, 'getQrScans']);
            Route::put('/profile', [\App\Http\Controllers\Auth\SharedProfileController::class, 'update']);
            Route::put('/change-password', [\App\Http\Controllers\Auth\SharedProfileController::class, 'changePassword']);

            // Route::get('/commission-slabs', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionSlabController::class, 'index']);
            // Route::post('/commission-slabs', [\App\Http\Controllers\Api\V1\SuperAgent\CommissionSlabController::class, 'store']);

            // Enumerators
            Route::apiResource('enumerators', \App\Http\Controllers\Admin\SuperAgentEnumeratorController::class)->names('super-agent.enumerators');
            Route::put('/enumerators/{id}/status', [\App\Http\Controllers\Admin\SuperAgentEnumeratorController::class, 'updateStatus']);
        });

        // ADMIN ROUTES
        // ==============================
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);

            // Agents
            Route::get('/agents', [AdminSuperAgentController::class, 'unassignedAgents']);
            Route::put('/agents/{id}/status', [AdminAgentController::class, 'updateStatus']);
            Route::apiResource('agents', AdminAgentController::class);
            Route::post('/agents/{id}/regenerate-qr', [AdminAgentController::class, 'regenerateQr']);
            Route::get('/agents/{id}/qr-scans', [AdminAgentController::class, 'getQrScans']);

            // Enumerators
            Route::apiResource('enumerators', \App\Http\Controllers\Admin\AdminEnumeratorController::class)->names('admin.enumerators');
            Route::put('/enumerators/{id}/status', [\App\Http\Controllers\Admin\AdminEnumeratorController::class, 'updateStatus']);

            // Operators (Admin-only management)
            Route::get('/operators', [AdminOperatorController::class, 'index']);
            Route::post('/operators', [AdminOperatorController::class, 'store']);
            Route::put('/operators/{id}/status', [AdminOperatorController::class, 'updateStatus']);
            Route::delete('/operators/{id}', [AdminOperatorController::class, 'destroy']);

            // Super Agents CRUD
            Route::get('/super-agents', [AdminSuperAgentController::class, 'index']);
            Route::post('/super-agents', [AdminSuperAgentController::class, 'store']);
            Route::get('/super-agents/{id}', [AdminSuperAgentController::class, 'show']);
            Route::put('/super-agents/{id}', [AdminSuperAgentController::class, 'update']);
            Route::put('/super-agents/{id}/status', [AdminSuperAgentController::class, 'updateStatus']);
            Route::delete('/super-agents/{id}', [AdminSuperAgentController::class, 'destroy']);
            Route::post('/super-agents/{id}/regenerate-qr', [AdminSuperAgentController::class, 'regenerateQr']);
            Route::put('/super-agents/{id}/toggle-public-contact', [AdminSuperAgentController::class, 'togglePublicContact']);
            Route::get('/super-agents/{id}/qr-scans', [AdminSuperAgentController::class, 'getQrScans']);

            // Super Agent Team Assignment
            Route::get('/super-agents/{id}/agents', [AdminSuperAgentController::class, 'teamAgents']);
            Route::post('/super-agents/{id}/agents/assign', [AdminSuperAgentController::class, 'assignAgent']);
            Route::post('/super-agents/{id}/agents/assign-bulk', [AdminSuperAgentController::class, 'assignAgentsBulk']);
            Route::delete('/super-agents/{id}/agents/{agent_id}', [AdminSuperAgentController::class, 'unassignAgent']);
            Route::get('/super-agents/{id}/team-log', [AdminSuperAgentController::class, 'teamLog']);

            // Leads — Admin-only actions (assign, edit, document upload, override)
            Route::put('/leads/{ulid}', [AdminLeadController::class, 'update']);
            Route::put('/leads/{ulid}/assign', [AdminLeadController::class, 'assign']);
            Route::put('/leads/{ulid}/assign-super-agent', [AdminLeadController::class, 'assignSuperAgent']);
            Route::put('/leads/{ulid}/assign-agent', [AdminLeadController::class, 'assignAgent']);
            Route::put('/leads/{ulid}/override-verification', [AdminLeadController::class, 'overrideVerification']);
            Route::post('/leads/{ulid}/documents', [AdminLeadController::class, 'uploadDocument']);

            // Commissions
            Route::get('/commissions', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'index']);
            Route::get('/commissions/summary', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'summary']);
            Route::post('/leads/{ulid}/commission/super-agent', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'enterSuperAgentCommission']);
            Route::post('/leads/{ulid}/commission/agent-direct', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'enterDirectAgentCommission']);
            Route::post('/leads/{ulid}/commission/enumerator', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'enterEnumeratorCommission']);
            Route::post('/leads/{ulid}/commission/enter', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'enterCommission']);
            Route::put('/commissions/{id}', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'update']);
            Route::put('/commissions/{id}/mark-paid', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'markPaid']);
            Route::get('/leads/{ulid}/commissions', [\App\Http\Controllers\Admin\AdminCommissionController::class, 'getLeadCommissions']);

            // Reports
            Route::get('/reports/pipeline', [AdminReportController::class, 'pipelineSummary']);
            Route::get('/reports/agent-performance', [AdminReportController::class, 'agentPerformance']);
            Route::get('/reports/geography', [AdminReportController::class, 'geographicDistribution']);
            Route::get('/reports/monthly-trend', [AdminReportController::class, 'monthlyTrend']);
            Route::get('/reports/super-agent-performance', [AdminReportController::class, 'superAgentPerformance']);

            // Route::get('/commission-slabs', [AdminCommissionSlabController::class, 'index']);

            // Withdrawals
            Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'adminIndex']);
            Route::put('/withdrawals/{id}/approve', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'approve']);
            Route::put('/withdrawals/{id}/reject', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'reject']);
            Route::put('/withdrawals/{id}/mark-paid', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'markPaid']);

            // Route::post('/commission-slabs', [AdminCommissionSlabController::class, 'store']);
            // Route::put('/commission-slabs/{id}', [AdminCommissionSlabController::class, 'update']);
            // Route::delete('/commission-slabs/{id}', [AdminCommissionSlabController::class, 'destroy']);

            // Incentive Offers v2
            Route::get('/offers/redemptions', [AdminOfferController::class, 'redemptions']);
            Route::post('/offers/redemptions/{id}/approve', [AdminOfferController::class, 'approveRedemption']);
            Route::post('/offers/redemptions/{id}/deliver', [AdminOfferController::class, 'deliveredRedemption']);
            Route::post('/offers/redemptions/{id}/cancel', [AdminOfferController::class, 'cancelRedemption']);
            Route::get('/offers/{offer}/participants', [AdminOfferController::class, 'participants']);
            Route::get('/offers/absorbed-points', [AdminOfferController::class, 'absorbedPoints']);
            Route::post('/offers/absorbed-points/{absorbedPoint}/approve', [AdminOfferController::class, 'approveAbsorption']);
            Route::post('/offers/{offer}/trigger-expiry', [AdminOfferController::class, 'triggerExpiry']);
            Route::apiResource('offers', AdminOfferController::class);

            // Settings
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

        // ==============================
        // SUPER ADMIN ROUTES
        // ==============================
        Route::middleware('super_admin')->prefix('super-admin')->group(function () {
            Route::get('/dashboard/stats', [MonitoringController::class, 'stats']);

            // Admin Management
            Route::get('/admins', [AdminManagementController::class, 'index']);
            Route::post('/admins', [AdminManagementController::class, 'store']);
            Route::put('/admins/{id}', [AdminManagementController::class, 'update']);
            Route::delete('/admins/{id}', [AdminManagementController::class, 'destroy']);
            Route::put('/admins/{id}/status', [AdminManagementController::class, 'toggleStatus']);

            // WA Handlers
            Route::get('chatbot/wa-handlers',              [SAChatbotController::class, 'waHandlers']);
            Route::post('chatbot/wa-handlers/{id}/toggle', [SAChatbotController::class, 'toggleWaHandler']);
            Route::post('chatbot/wa-handlers/reset-counters', [SAChatbotController::class, 'resetCounters']);

            // Chatbot Management (Moved to Super Admin)
            Route::apiResource('chatbot/categories', SAChatbotController::class)->except(['show']);
            Route::patch('chatbot/categories/{id}/toggle', [SAChatbotController::class, 'toggleActive']);
            Route::get('chatbot/registration-fields',      [SAChatbotController::class, 'getRegistrationFields']);
            Route::put('chatbot/registration-fields',       [SAChatbotController::class, 'setRegistrationFields']);
            Route::get('chatbot/sessions',                  [SAChatbotController::class, 'sessions']);
            Route::get('chatbot/contacts',                  [SAChatbotController::class, 'contacts']);
            Route::get('chatbot/all-contacts',              [SAChatbotController::class, 'allContacts']);
            Route::post('chatbot/contacts/{id}/toggle',     [SAChatbotController::class, 'toggleContact']);

            // FAQ Management (Moved to Super Admin)
            Route::apiResource('faqs', SAFAQController::class);
            Route::patch('/faqs/{faq}/toggle-status', [SAFAQController::class, 'toggleStatus']);

            // Commission Settlement (Super Admin pays Admins)
            Route::get('/commissions/summary', [MonitoringController::class, 'commissionsSummary']);
            Route::get('/commissions', [MonitoringController::class, 'commissionsList']);
            Route::put('/commissions/{id}/settle', [MonitoringController::class, 'settleCommission']);
        });



        // ==============================
        // ADMIN + OPERATOR SHARED ROUTES
        // Operators can: view all leads, update lead status.
        // They CANNOT access the admin group above (no dashboard, agents, settings etc.)
        // ==============================
        Route::middleware('admin_or_operator')->prefix('admin')->group(function () {
            Route::get('/leads', [AdminLeadController::class, 'index']);
            Route::get('/leads/{ulid}', [AdminLeadController::class, 'show']);
            Route::put('/leads/{ulid}/status', [AdminLeadController::class, 'updateStatus']);
        });
    });
});
