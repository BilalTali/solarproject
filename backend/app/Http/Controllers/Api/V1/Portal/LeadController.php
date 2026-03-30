<?php

namespace App\Http\Controllers\Api\V1\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRegistrationRequest;
use App\Http\Requests\StorePublicLeadRequest;
use App\Models\User;
use App\Services\AgentService;
use App\Services\LeadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private LeadService $leadService,
        private AgentService $agentService
    ) {}

    public function store(StorePublicLeadRequest $request)
    {
        $data = $request->validated();
        $data['ulid'] = Str::ulid()->toBase32();

        $lead = DB::transaction(function () use ($data, $request) {
            $lead = $this->leadService->createFromPublicForm($data);

            // Handle file uploads
            $documentTypes = ['aadhaar', 'electricity_bill', 'photo', 'other', 'solar_roof_photo', 'bank_passbook'];
            foreach ($documentTypes as $type) {
                if ($request->hasFile($type)) {
                    $this->leadService->uploadDocument($lead, $request->file($type), $type);
                }
            }

            return $lead;
        });

        return response()->json([
            'success' => true,
            'message' => 'Query submitted successfully',
            'data' => ['reference' => $lead->ulid],
        ], 201);
    }

    public function track(Request $request)
    {
        $request->validate([
            'id' => 'required|string|min:4',
        ]);

        $search = $request->id;

        $lead = \App\Models\Lead::query()->where(fn($q) => $q->where('ulid', $search)
            ->orWhere('beneficiary_mobile', $search))
            ->first();

        /** @var \App\Models\Lead $lead */
        if (! $lead) {
            return response()->json([
                'success' => false,
                'message' => 'No application found with these details.',
            ], 404);
        }

        // Map status to user-friendly messaging
        $status = 'New / Pending';
        $meaning = 'Query received, agent not yet assigned';
        $nextStep = 'Wait — you will be called within 24 hrs';
        $stepIndex = 1;

        if ($lead->assigned_agent_id) {
            $status = 'Assigned';
            $meaning = 'An agent has been assigned to your query';
            $nextStep = 'Expect a call from agent '.($lead->assignedAgent->agent_code ?? 'SM-XXXX');
            $stepIndex = 2;
        }

        // Logic for "In Progress"
        if ($lead->verification_status === 'pending_super_agent_verification') {
            $status = 'In Progress';
            $meaning = 'Agent is working on your registration';
            $nextStep = 'Cooperate with your agent';
            $stepIndex = 3;
        }

        if ($lead->verification_status === 'super_agent_verified') {
            $status = 'Verified';
            $meaning = 'Documents verified by manager';
            $nextStep = 'Final processing for portal registration';
            $stepIndex = 4;
        }

        if ($lead->status === 'REGISTERED') {
            $status = 'Registered';
            $meaning = 'Your application has been registered on the PM Surya Ghar portal';
            $nextStep = 'Site survey will be arranged soon';
            $stepIndex = 5;
        }

        if ($lead->status === 'SITE_SURVEY') {
            $status = 'Site Survey Done';
            $meaning = 'Technical team has inspected your site for solar installation';
            $nextStep = 'Installation work will begin shortly';
            $stepIndex = 6;
        }

        if ($lead->status === 'PROJECT_COMMISSIONING') {
            $status = 'Project Commissioned';
            $meaning = 'System successfully tested and commissioned';
            $nextStep = 'We are filing your MNRE subsidy application';
            $stepIndex = 7;
        }

        if ($lead->status === 'SUBSIDY_REQUEST') {
            $status = 'Subsidy Requested';
            $meaning = 'Your MNRE subsidy claim has been submitted';
            $nextStep = 'Waiting for MNRE approval';
            $stepIndex = 8;
        }

        if ($lead->status === 'SUBSIDY_DISBURSED') {
            $status = 'Subsidy Disbursed';
            $meaning = 'MNRE has approved and disbursed your subsidy';
            $nextStep = 'Subsidy will reflect in your bank account shortly';
            $stepIndex = 9;
        }

        if ($lead->status === 'COMPLETED') {
            $status = 'Completed';
            $meaning = 'Your entire solar journey is officially complete';
            $nextStep = 'Enjoy free solar electricity!';
            $stepIndex = 10;
        }

        if ($lead->verification_status === 'reverted_to_agent') {
            $status = 'Correction Required';
            $meaning = 'There is an issue with your documents';
            $nextStep = 'Your agent will contact you for corrections';
            $stepIndex = 3;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reference' => $lead->ulid,
                'beneficiary' => $lead->beneficiary_name,
                'status' => $status,
                'meaning' => $meaning,
                'next_step' => $nextStep,
                'step_index' => $stepIndex, // 1 to 5
                'updated_at' => $lead->updated_at->toISOString(),
                'district' => $lead->beneficiary_district,
            ],
        ]);
    }

    public function registerAgent(AgentRegistrationRequest $request)
    {
        $agent = $this->agentService->createAgent($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration received successfully. We will contact you soon.',
            'data' => ['reference' => substr($agent->mobile, -4).'_'.time()],
        ], 201);
    }
}
