<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadTechnicalVisit;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TechnicalDashboardController extends Controller
{
    /**
     * Get leads assigned to the authenticated field technician
     */
    public function getAssignedLeads(Request $request)
    {
        $user = $request->user();

        if (!$user->isFieldTechnician()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $leads = Lead::query()
            ->visibleToTechnician($user->id)
            ->with(['assignedSurveyor', 'assignedInstaller', 'technicalVisits'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'leads' => $leads
        ]);
    }

    /**
     * Handle Site Survey or Installation Completion submission
     */
    public function submitVisit(Request $request, string $ulid)
    {
        $user = $request->user();

        if (!$user->isFieldTechnician()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'visit_type' => 'required|in:site_survey,installation_complete',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'selfie_image' => 'required|image|max:10240', // up to 10MB
            'agreed_to_terms' => 'required|accepted',
        ]);

        $lead = Lead::where('ulid', $ulid)->firstOrFail();

        // Validate assignment based on visit type
        if ($request->visit_type === 'site_survey' && $lead->assigned_surveyor_id !== $user->id) {
            return response()->json(['error' => 'You are not assigned as the surveyor for this lead.'], 403);
        }

        if ($request->visit_type === 'installation_complete' && $lead->assigned_installer_id !== $user->id) {
            return response()->json(['error' => 'You are not assigned as the installer for this lead.'], 403);
        }

        // Validate current status before transition
        if ($request->visit_type === 'site_survey' && !in_array($lead->status, ['NEW', 'ON_HOLD', 'REGISTERED'])) {
             return response()->json(['error' => 'Lead must be NEW or REGISTERED to perform a site survey.'], 400);
        }

        if ($request->visit_type === 'installation_complete' && $lead->status !== 'AT_BANK') { // Admin maps 'financing cleared' to AT_BANK
             return response()->json(['error' => 'Lead must be AT_BANK (Clearing) to perform installation completion.'], 400);
        }

        try {
            DB::beginTransaction();

            // Store selfie
            $path = $request->file('selfie_image')->store('technical_visits', 'public');

            // Record Visit
            $visit = LeadTechnicalVisit::create([
                'lead_id' => $lead->id,
                'technician_id' => $user->id,
                'visit_type' => $request->visit_type,
                'selfie_url' => $path,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'terms_agreed_at' => now()
            ]);

            // Transition Lead Status
            if ($request->visit_type === 'site_survey') {
                $lead->status = 'SITE_SURVEY';
            } else if ($request->visit_type === 'installation_complete') {
                $lead->status = 'COMPLETED';
            }

            $lead->save();

            // Log Status
            $lead->statusLogs()->create([
                'old_status' => $lead->getOriginal('status'),
                'new_status' => $lead->status,
                'changed_by' => $user->id,
                'notes' => 'Status updated via Geo-tagged Field Visit (' . str_replace('_', ' ', $request->visit_type) . ')',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Visit recorded and status updated successfully',
                'lead' => $lead->fresh(['assignedSurveyor', 'assignedInstaller', 'technicalVisits'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path)) Storage::disk('public')->delete($path);
            return response()->json(['error' => 'Failed to process visit: ' . $e->getMessage()], 500);
        }
    }
}
