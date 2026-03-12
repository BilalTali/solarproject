<?php
namespace App\Http\Controllers\Api\V1\Agent;

use App\Exceptions\InvalidLeadOperationException;
use App\Exceptions\LeadAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentLeadRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Lead::visibleToAgent($user->id)->with(['documents', 'commissions']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->has('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('beneficiary_name', 'like', "%{$search}%")
                  ->orWhere('beneficiary_mobile', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        // Count reverted leads for banner
        $revertedCount = Lead::visibleToAgent($user->id)
                             ->where('verification_status', 'reverted_to_agent')
                             ->count();

        return response()->json([
            'success' => true,
            'data'    => $leads,
            'meta'    => ['reverted_count' => $revertedCount],
        ]);
    }

    public function show(Request $request, $ulid)
    {
        $lead = Lead::visibleToAgent($request->user()->id)
                    ->with(['statusLogs.changedBy', 'documents', 'commissions', 'verifications.performedBy'])
                    ->where('ulid', $ulid)
                    ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $lead,
        ]);
    }

    /** Submit a new lead — routes to SA or directly to admin */
    public function store(StoreAgentLeadRequest $request)
    {
        $data         = $request->validated();
        $data['ulid'] = Str::ulid()->toBase32();

        $lead = DB::transaction(function () use ($data, $request) {
            $lead = $this->leadService->createFromAgent($data, $request->user());

            // Upload documents
            foreach (['aadhaar', 'electricity_bill', 'photo', 'solar_roof_photo', 'bank_passbook'] as $docKey) {
                if ($request->hasFile($docKey)) {
                    $this->leadService->uploadDocument($lead, $request->file($docKey), $docKey, $request->user()->id);
                }
            }

            return $lead;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lead submitted successfully!',
            'data'    => $lead,
        ], 201);
    }

    /** Agent corrects and resubmits a reverted lead */
    public function resubmit(StoreAgentLeadRequest $request, $ulid)
    {
        $lead = Lead::where('ulid', $ulid)->firstOrFail();

        try {
            $correctedData = $request->validated();
            $lead          = $this->leadService->resubmitLead($lead, $correctedData, $request->user());
        } catch (InvalidLeadOperationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (LeadAccessDeniedException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead resubmitted for verification.',
            'data'    => $lead,
        ]);
    }

    /** Get verification history for this lead */
    public function verificationHistory(Request $request, $ulid)
    {
        $lead    = Lead::visibleToAgent($request->user()->id)->where('ulid', $ulid)->firstOrFail();
        $history = $lead->verifications()->with('performedBy:id,name,role,agent_id,super_agent_code')->get();

        return response()->json(['success' => true, 'data' => $history]);
    }

    public function uploadDocument(Request $request, $ulid)
    {
        $request->validate([
            'document' => 'required|file|max:5120|mimes:jpg,png,pdf',
            'type'     => 'required|string',
        ]);

        $lead = Lead::visibleToAgent($request->user()->id)->where('ulid', $ulid)->firstOrFail();

        $document = $this->leadService->uploadDocument(
            $lead,
            $request->file('document'),
            $request->type,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data'    => $document,
        ], 201);
    }
}
