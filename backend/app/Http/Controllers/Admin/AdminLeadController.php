<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Models\LeadVerification;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class AdminLeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(Request $request)
    {
        $query = Lead::query()->with(['assignedSuperAgent', 'assignedAgent', 'submittedByAgent', 'createdBySuperAgent', 'documents', 'commissions']);

        $user = $request->user();

        // ── WA LEAD VISIBILITY GATE ───────────────────────────────────────
        if ($user->is_wa_lead_handler) {
            $query->where(function ($q) use ($user) {
                $q->where('source', '!=', 'whatsapp_chatbot')
                  ->orWhere('wa_handler_admin_id', $user->id);
            });
        } else {
            $query->where('source', '!=', 'whatsapp_chatbot');
        }
        // ── END GATE ─────────────────────────────────────────────────────

        if ($request->filled('status')) {
            $query->where(fn ($q) => $q->whereIn('status', explode(',', $request->status)));
        }

        if ($request->filled('verification_status')) {
            $query->where(fn ($q) => $q->where('verification_status', $request->verification_status));
        }

        if ($request->filled('owner_type')) {
            $query->where(fn ($q) => $q->where('owner_type', $request->owner_type));
        }

        if ($request->filled('source')) {
            $query->where(fn ($q) => $q->where('source', $request->source));
        }

        if ($request->filled('agent_id')) {
            $query->where(fn ($q) => $q->where('assigned_agent_id', $request->agent_id));
        }

        if ($request->filled('super_agent_id')) {
            $query->where(fn ($q) => $q->where('assigned_super_agent_id', $request->super_agent_id));
        }

        if ($request->filled('state')) {
            $query->where(fn ($q) => $q->where('beneficiary_state', $request->state));
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where(fn ($q2) => $q2->where('beneficiary_name', 'like', "%{$search}%"))
                    ->orWhere(fn ($q2) => $q2->where('beneficiary_mobile', 'like', "%{$search}%"))
                    ->orWhere(fn ($q2) => $q2->where('consumer_number', 'like', "%{$search}%"))
                    ->orWhere(fn ($q2) => $q2->where('ulid', 'like', "%{$search}%"));
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
    }

    public function show($ulid)
    {
        $lead = Lead::query()->with([
            'assignedSuperAgent', 'assignedAgent', 'submittedByAgent',
            'submittedByEnumerator', 'createdBySuperAgent', 'verifiedBySuperAgent',
            'statusLogs.changedBy', 'documents', 'commissions',
            'verifications.performedBy',
        ])
            ->where(fn ($q) => $q->where('ulid', $ulid))
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
    }

    public function update(Request $request, $ulid)
    {
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $data = $request->except(['status', 'ulid', 'verification_status', 'owner_type']);
        $lead->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => $lead,
        ]);
    }

    public function updateStatus(UpdateLeadStatusRequest $request, $ulid)
    {
        $lead = Lead::query()->with(['assignedAgent.superAgent', 'submittedByAgent.superAgent'])
            ->where(fn ($q) => $q->where('ulid', $ulid))
            ->firstOrFail();

        $this->leadService->updateStatus(
            $lead,
            $request->status,
            $request->user()->id,
            $request->notes
        );

        $commissionStatus = app(\App\Services\CommissionService::class)->getCommissionStatus($lead);

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated successfully',
            'data' => [
                'lead' => clone $lead->fresh(['statusLogs']),
                'commission_prompts' => $commissionStatus,
                'commission_prompt' => $commissionStatus[0] ?? ['should_prompt' => false], // Backward compatibility
            ],
        ]);
    }

    /** Assign lead to a Super Agent */
    public function assignSuperAgent(Request $request, $ulid)
    {
        $request->validate(['super_agent_id' => 'required|exists:users,id']);

        /** @var \App\Models\Lead $lead */
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        /** @var \App\Models\User $superAgent */
        $superAgent = User::query()->where(fn ($q) => $q->where('id', $request->super_agent_id))->where(fn ($q) => $q->where('role', 'super_agent'))->firstOrFail();

        $lead = $this->leadService->assignLeadToSuperAgent($lead, $superAgent, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned to Super Agent successfully',
            'data' => $lead->fresh(['assignedSuperAgent']),
        ]);
    }

    /** Assign lead to a specific Agent (may skip SA) */
    public function assignAgent(Request $request, $ulid)
    {
        $request->validate(['agent_id' => 'required|exists:users,id']);

        /** @var \App\Models\Lead $lead */
        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        /** @var \App\Models\User $agent */
        $agent = User::query()->where(fn ($q) => $q->where('id', $request->agent_id))->where(fn ($q) => $q->where('role', 'agent'))->firstOrFail();

        $lead = $this->leadService->assignLeadToAgent($lead, $agent, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned to Agent successfully',
            'data' => $lead->fresh(['assignedAgent', 'assignedSuperAgent']),
        ]);
    }

    /** Admin bypasses SA verification */
    public function overrideVerification(Request $request, $ulid)
    {
        $request->validate(['reason' => 'required|string|min:5']);

        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();

        $lead->update([
            'verification_status' => 'admin_override',
            'owner_type' => 'admin_pool',
            'revert_reason' => null,
        ]);

        // Audit log
        LeadVerification::create([
            'lead_id' => $lead->id,
            'action' => 'verified',
            'performed_by' => $request->user()->id,
            'performer_role' => 'admin',
            'reason' => $request->reason.' [ADMIN OVERRIDE]',
            'revert_count_at_time' => $lead->revert_count,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification overridden by Admin',
            'data' => $lead->fresh(),
        ]);
    }

    /** Legacy assign endpoint kept for backward compatibility — assigns to SA */
    public function assign(Request $request, $ulid)
    {
        return $this->assignSuperAgent($request, $ulid);
    }

    public function uploadDocument(Request $request, $ulid)
    {
        $request->validate([
            'document' => 'required|file|max:5120|mimes:jpg,png,pdf',
            'type' => 'required|in:aadhaar,electricity_bill,photo,other',
        ]);

        $lead = Lead::query()->where(fn ($q) => $q->where('ulid', $ulid))->firstOrFail();
        $document = $this->leadService->uploadDocument($lead, $request->file('document'), $request->type, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data' => $document,
        ], 201);
    }
}
