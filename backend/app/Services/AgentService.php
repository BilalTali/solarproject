<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\SuperAgentService;

class AgentService
{
    public function __construct(
        private NotificationService $notificationService,
        private JoiningLetterService $joiningLetterService,
        private SuperAgentService $superAgentService // Added this injection
    ) {}

    /** Generate SM-YYYY-XXXX code for regular agents */
    public function generateAgentId(): string
    {
        $year = date('Y');
        $lastAgent = User::agents()->whereNotNull('agent_id')
            ->where('agent_id', 'like', "SM-{$year}-%")
            ->orderBy('agent_id', 'desc')->first();
        if ($lastAgent && preg_match('/SM-\d{4}-(\d{4})/', $lastAgent->agent_id, $matches)) {
            $seq = (int)$matches[1] + 1;
        } else {
            $seq = 1001;
        }
        return "SM-{$year}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /** Generate SSM-YYYY-XXXX code for super agents */
    public function generateSuperAgentCode(): string
    {
        $year = date('Y');
        $last = User::superAgents()->whereNotNull('super_agent_code')
            ->where('super_agent_code', 'like', "SSM-{$year}-%")
            ->orderBy('super_agent_code', 'desc')->first();
        if ($last && preg_match('/SSM-\d{4}-(\d{4})/', $last->super_agent_code, $matches)) {
            $seq = (int)$matches[1] + 1;
        } else {
            $seq = 1001;
        }
        return "SSM-{$year}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Admin approves an agent.
     * Optionally assigns to a super agent at approval time.
     * If agent was created by a SA, keeps that assignment unless overridden.
     */
    public function approveAgent(User $agent, ?User $admin = null, ?int $superAgentId = null): User
    {
        return DB::transaction(function () use ($agent, $admin, $superAgentId) {
            $updateData = ['status' => 'active'];

            if (!$agent->agent_id) {
                $updateData['agent_id'] = $this->generateAgentId();
            }
            if (!$agent->letter_number) {
                $updateData['letter_number'] = $this->joiningLetterService->generateLetterNumber($agent);
                $updateData['joining_date'] = now()->toDateString();
                $updateData['approved_at'] = now();
                $updateData['approved_by'] = $admin ? $admin->id : null;
            }

            // QR Token Generation (Always generate if missing for active agents)
            if (!$agent->qr_token) {
                $updateData['qr_token'] = hash('sha256', \Illuminate\Support\Str::random(40) . $agent->id . now()->timestamp);
                $updateData['qr_generated_at'] = now();
            }

            // Admin explicitly assigns to a different SA
            if ($superAgentId) {
                $superAgent = User::where('id', $superAgentId)
                                  ->where('role', 'super_agent')
                                  ->firstOrFail();
                $updateData['super_agent_id'] = $superAgent->id;
            }
            // If no explicit SA override, keep existing super_agent_id (set by SA at creation)

            $agent->forceFill($updateData)->save();
            $agent->refresh();

            $this->notificationService->send(
                $agent->id,
                'agent_approved',
                'Account Approved!',
                "Your account is approved. Your Agent ID is {$agent->agent_id}."
            );

            // Notify SA if agent is assigned
            if ($agent->super_agent_id) {
                $this->notifySuperAgentNewAgentAssigned($agent);
            }

            return $agent;
        });
    }

    /**
     * Super Agent creates a new agent for their team.
     * Agent is set to 'pending' and requires Admin approval before activation.
     */
    public function createAgentBySuperAgent(array $data, User $superAgent): User
    {
        return $this->createAgent($data, $superAgent, 'pending');
    }

    /**
     * Generic agent creation logic
     */
    public function createAgent(array $data, ?User $creator = null, string $status = 'pending'): User
    {
        return DB::transaction(function () use ($data, $creator, $status) {
            $data['role']                      = 'agent';
            $data['status']                    = $status;
            
            if ($creator && $creator->isSuperAgent()) {
                $data['super_agent_id']            = $creator->id;
                $data['created_by_super_agent_id'] = $creator->id;
            }

            $data['password'] = Hash::make($data['password'] ?? 'Welcome@123');

            // Handle File Uploads
            $data = $this->handleFileUploads($data);

            $agent = User::forceCreate($data);

            // If active (e.g. created by admin), trigger the approval logic (ID generation etc)
            if ($status === 'active') {
                $this->approveAgent($agent, $creator && $creator->isAdmin() ? $creator : null);
            }

            // Notify admin if pending
            if ($status === 'pending') {
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    $this->notificationService->send(
                        $admin->id,
                        'new_agent_pending',
                        'New Agent Pending Approval',
                        $creator && $creator->isSuperAgent() 
                            ? "Super Agent {$creator->super_agent_code} added a new agent {$agent->name}. Awaiting your approval."
                            : "New Agent registration: {$agent->name}. Awaiting your approval.",
                        ['agent_id' => $agent->id, 'creator_id' => $creator?->id]
                    );
                }
            }

            return $agent;
        });
    }

    /**
     * Admin assigns (or reassigns) an existing agent to a super agent.
     * Requires force=true to override origin SA.
     */
    public function assignAgentToSuperAgent(User $agent, User $superAgent, User $admin, bool $force = false): User
    {
        if ($agent->created_by_super_agent_id
            && (int)$agent->created_by_super_agent_id !== (int)$superAgent->id
            && !$force) {
            throw new \InvalidArgumentException(
                'This agent was created by a different Super Agent. Use force=true to override.'
            );
        }

        $agent->forceFill(['super_agent_id' => $superAgent->id])->save();

        $this->notifySuperAgentNewAgentAssigned($agent);
        $this->notifyAgentSuperAgentAssigned($agent, $superAgent);

        return $agent->fresh();
    }

    private function notifySuperAgentNewAgentAssigned(User $agent): void
    {
        if (!$agent->super_agent_id) return;
        $sa = User::find($agent->super_agent_id);
        if ($sa) {
            $this->notificationService->send(
                $sa->id,
                'new_agent_in_team',
                'New Agent in Your Team',
                "Agent {$agent->name} ({$agent->agent_id}) has been approved and added to your team.",
                ['agent_id' => $agent->id]
            );
        }
    }

    private function notifyAgentSuperAgentAssigned(User $agent, User $superAgent): void
    {
        $this->notificationService->send(
            $agent->id,
            'sa_assigned',
            'Super Agent Assigned',
            "You have been assigned to Super Agent {$superAgent->name} ({$superAgent->super_agent_code}).",
            ['sa_id' => $superAgent->id]
        );
    }

    /**
     * Handle document uploads for agent registration
     */
    public function handleFileUploads(array $data): array
    {
        $fileFields = [
            'profile_photo'    => 'agents/photos',
            'aadhaar_document' => 'agents/documents',
            'pan_document'     => 'agents/documents',
            'education_cert'   => 'agents/documents',
            'resume'           => 'agents/documents',
            'mou_signed'       => 'agents/documents',
        ];

        foreach ($fileFields as $field => $folder) {
            if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                $data[$field] = $data[$field]->store($folder, 'public');
            }
        }

        return $data;
    }
}
