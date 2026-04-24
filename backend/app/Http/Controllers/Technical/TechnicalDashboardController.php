<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadTechnicalVisit;
use App\Models\User;
use App\Services\LeadService;
use App\Services\StatusTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TechnicalDashboardController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
        private readonly StatusTransitionService $transitionService,
    ) {}

    /**
     * Get leads assigned to the authenticated field technician.
     */
    public function getAssignedLeads(Request $request)
    {
        $user = $request->user();

        if (! $user->isFieldTechnician()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $leads = Lead::query()
            ->visibleToTechnician($user->id)
            ->with(['assignedSurveyor', 'assignedInstaller', 'technicalVisits'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['leads' => $leads]);
    }

    /**
     * Handle Site Survey or Installation Completion submission.
     *
     * B4 — Fixes:
     *  1. $oldStatus is captured BEFORE $lead->status is mutated so
     *     the status log records the correct from_status.
     *  2. Geotag evidence (photo path + GPS) is written to lead_status_logs
     *     alongside the visit record so the admin timeline can display it.
     *  3. Admin is notified via LeadService after a successful commit.
     */
    public function submitVisit(Request $request, string $ulid)
    {
        $user = $request->user();

        if (! $user->isFieldTechnician()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'visit_type'      => 'required|in:site_survey,installation_complete',
            'latitude'        => 'required|numeric',
            'longitude'       => 'required|numeric',
            'selfie_image'    => 'required|image|max:10240', // up to 10 MB
            'agreed_to_terms' => 'required|accepted',
        ]);

        $lead = Lead::where('ulid', $ulid)->firstOrFail();

        // ── Assignment guard ───────────────────────────────────────────
        if ($request->visit_type === 'site_survey' && $lead->assigned_surveyor_id !== $user->id) {
            return response()->json(['error' => 'You are not assigned as the surveyor for this lead.'], 403);
        }

        if ($request->visit_type === 'installation_complete' && $lead->assigned_installer_id !== $user->id) {
            return response()->json(['error' => 'You are not assigned as the installer for this lead.'], 403);
        }

        // ── Pipeline state guard ───────────────────────────────────────
        if ($request->visit_type === 'site_survey' && ! in_array($lead->status, ['NEW', 'ON_HOLD', 'REGISTERED'], true)) {
            return response()->json(['error' => 'Lead must be NEW, ON_HOLD, or REGISTERED to perform a site survey.'], 400);
        }

        if ($request->visit_type === 'installation_complete' && $lead->status !== 'AT_BANK') {
            return response()->json(['error' => 'Lead must be AT_BANK to perform installation completion.'], 400);
        }

        // ── FIX B4: Capture old status BEFORE any mutation ────────────
        $oldStatus = $lead->status;
        $newStatus = $request->visit_type === 'site_survey' ? 'SURVEY_DONE' : 'SOLAR_INSTALLED';
        $visitLabel = str_replace('_', ' ', $request->visit_type);

        $path = null;

        try {
            DB::beginTransaction();

            // Store geotag selfie
            $path = $request->file('selfie_image')->store('technical_visits', 'public');

            // Record visit row
            LeadTechnicalVisit::create([
                'lead_id'        => $lead->id,
                'technician_id'  => $user->id,
                'visit_type'     => $request->visit_type,
                'selfie_url'     => $path,
                'latitude'       => $request->latitude,
                'longitude'      => $request->longitude,
                'terms_agreed_at' => now(),
            ]);

            // Transition lead status
            $lead->status = $newStatus;
            $lead->save();

            // ── FIX B4: Write correct from_status + geotag to status log ──
            $lead->statusLogs()->create([
                'from_status'       => $oldStatus,   // ← correct pre-mutation value
                'to_status'         => $newStatus,
                'changed_by'        => $user->id,
                'changed_by_role'   => $user->role,
                'notes'             => "Status updated via Geo-tagged Field Visit ({$visitLabel})",
                'geotag_photo_path' => $path,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
            ]);

            DB::commit();

            // ── B5: Notify admin that tech team changed status ─────────
            $this->leadService->notifyAdminTechnicalStatusChanged($lead, $user, $oldStatus, $newStatus);

            return response()->json([
                'message' => 'Visit recorded and status updated successfully.',
                'lead'    => $lead->fresh(['assignedSurveyor', 'assignedInstaller', 'technicalVisits']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            return response()->json(['error' => 'Failed to process visit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get dashboard statistics for the technician.
     */
    public function getStats(Request $request)
    {
        $user = $request->user();

        if (! $user->isFieldTechnician()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $baseQuery = Lead::query()->visibleToTechnician($user->id);

        $stats = [
            'total_assigned'          => (clone $baseQuery)->count(),
            'pending_surveys'         => (clone $baseQuery)->whereIn('status', ['NEW', 'REGISTERED', 'ON_HOLD'])->count(),
            'completed_surveys'       => (clone $baseQuery)->whereIn('status', ['SURVEY_DONE', 'AT_BANK', 'SOLAR_INSTALLED', 'COMPLETED'])->count(),
            'pending_installations'   => (clone $baseQuery)->whereIn('status', ['AT_BANK', 'INSTALLATION_SCHEDULED'])->count(),
            'completed_installations' => (clone $baseQuery)->whereIn('status', ['SOLAR_INSTALLED', 'COMPLETED'])->count(),
        ];

        $recentActivity = LeadTechnicalVisit::where('technician_id', $user->id)
            ->with('lead:id,ulid,beneficiary_name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'stats'           => $stats,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }
}
