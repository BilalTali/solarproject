<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\InvalidLeadOperationException;
use App\Exceptions\LeadAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(Request $request)
    {
        $query = Lead::with(['assignedSuperAgent', 'assignedAgent', 'submittedByAgent', 'createdBySuperAgent', 'documents', 'commissions']);

        if ($request->filled('status')) {
            $query->whereIn('status', explode(',', $request->status));
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('owner_type')) {
            $query->where('owner_type', $request->owner_type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('agent_id')) {
            $query->where('assigned_agent_id', $request->agent_id);
        }

        if ($request->filled('super_agent_id')) {
            $query->where('assigned_super_agent_id', $request->super_agent_id);
        }

        if ($request->filled('state')) {
            $query->where('beneficiary_state', $request->state);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function($q) use ($search) {
                $q->where('beneficiary_name', 'like', "%{$search}%")
                  ->orWhere('beneficiary_mobile', 'like', "%{$search}%")
                  ->orWhere('consumer_number', 'like', "%{$search}%")
                  ->orWhere('ulid', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $leads
        ]);
    }

    public function show($ulid)
    {
        $lead = Lead::with([
            'assignedSuperAgent', 'assignedAgent', 'submittedByAgent',
            'createdBySuperAgent', 'verifiedBySuperAgent',
            'statusLogs.changedBy', 'documents', 'commissions',
            'verifications.performedBy',
        ])
        ->where('ulid', $ulid)
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $lead
        ]);
    }

    public function update(Request $request, $ulid)
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();
        $data = $request->except(['status', 'ulid', 'verification_status', 'owner_type']);
        $lead->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => $lead
        ]);
    }

    public function updateStatus(UpdateLeadStatusRequest $request, $ulid)
    {
        $lead = Lead::with(['assignedAgent.superAgent', 'submittedByAgent.superAgent'])
                    ->where('ulid', $ulid)
                    ->firstOrFail();

        $this->leadService->updateStatus(
            $lead,
            $request->status,
            $request->user()->id,
            $request->notes
        );

        $prompt = ['should_prompt' => false];
        $agent  = $lead->assignedAgent ?? $lead->submittedByAgent;

        if (in_array($request->status, ['installed', 'completed']) && $agent) {
            $superAgent = $agent->superAgent;
            $prompt = $superAgent ? [
                'should_prompt'       => true,
                'payee_role'          => 'super_agent',
                'payee_id'            => $superAgent->id,
                'payee_name'          => $superAgent->name,
                'payee_code'          => $superAgent->super_agent_code ?? '',
                'payee_type_label'    => 'Super Agent',
                'existing_commission' => null,
            ] : [
                'should_prompt'       => true,
                'payee_role'          => 'agent',
                'payee_id'            => $agent->id,
                'payee_name'          => $agent->name,
                'payee_code'          => $agent->agent_id ?? '',
                'payee_type_label'    => 'Agent (Direct — No Super Agent)',
                'existing_commission' => null,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated successfully',
            'data' => [
                'lead' => clone $lead->fresh(['statusLogs']),
                'commission_prompt' => $prompt,
            ]
        ]);
    }

    /** Assign lead to a Super Agent */
    public function assignSuperAgent(Request $request, $ulid)
    {
        $request->validate(['super_agent_id' => 'required|exists:users,id']);

        $lead       = Lead::where('ulid', $ulid)->firstOrFail();
        $superAgent = User::where('id', $request->super_agent_id)->where('role', 'super_agent')->firstOrFail();

        $lead = $this->leadService->assignLeadToSuperAgent($lead, $superAgent, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned to Super Agent successfully',
            'data'    => $lead->fresh(['assignedSuperAgent']),
        ]);
    }

    /** Assign lead to a specific Agent (may skip SA) */
    public function assignAgent(Request $request, $ulid)
    {
        $request->validate(['agent_id' => 'required|exists:users,id']);

        $lead  = Lead::where('ulid', $ulid)->firstOrFail();
        $agent = User::where('id', $request->agent_id)->where('role', 'agent')->firstOrFail();

        $lead = $this->leadService->assignLeadToAgent($lead, $agent, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned to Agent successfully',
            'data'    => $lead->fresh(['assignedAgent', 'assignedSuperAgent']),
        ]);
    }

    /** Admin bypasses SA verification */
    public function overrideVerification(Request $request, $ulid)
    {
        $request->validate(['reason' => 'required|string|min:5']);

        $lead = Lead::where('ulid', $ulid)->firstOrFail();

        $lead->update([
            'verification_status' => 'admin_override',
            'owner_type'          => 'admin_pool',
            'revert_reason'       => null,
        ]);

        // Audit log
        \App\Models\LeadVerification::create([
            'lead_id'              => $lead->id,
            'action'               => 'verified',
            'performed_by'         => $request->user()->id,
            'performer_role'       => 'admin',
            'reason'               => $request->reason . ' [ADMIN OVERRIDE]',
            'revert_count_at_time' => $lead->revert_count,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification overridden by Admin',
            'data'    => $lead->fresh(),
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
            'type'     => 'required|in:aadhaar,electricity_bill,photo,other'
        ]);

        $lead     = Lead::where('ulid', $ulid)->firstOrFail();
        $document = $this->leadService->uploadDocument($lead, $request->file('document'), $request->type, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data'    => $document
        ], 201);
    }
}
