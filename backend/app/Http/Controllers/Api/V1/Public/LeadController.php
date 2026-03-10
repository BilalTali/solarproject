<?php
namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRegistrationRequest;
use App\Http\Requests\StorePublicLeadRequest;
use App\Models\User;
use App\Services\AgentService;
use App\Services\LeadService;
use App\Services\NotificationService;
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
        $data         = $request->validated();
        $data['ulid'] = Str::ulid()->toBase32();

        $lead = $this->leadService->createFromPublicForm($data);

        // Handle file uploads
        $documentTypes = ['aadhaar', 'electricity_bill', 'photo', 'other', 'solar_roof_photo', 'bank_passbook'];
        foreach ($documentTypes as $type) {
            if ($request->hasFile($type)) {
                $this->leadService->uploadDocument($lead, $request->file($type), $type);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Query submitted successfully',
            'data'    => ['reference' => $lead->ulid],
        ], 201);
    }

    public function registerAgent(AgentRegistrationRequest $request)
    {
        $agent = $this->agentService->createAgent($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration received successfully. We will contact you soon.',
            'data'    => ['reference' => substr($agent->mobile, -4) . '_' . time()],
        ], 201);
    }
}
