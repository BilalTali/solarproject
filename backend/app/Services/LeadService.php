<?php

namespace App\Services;

use App\Exceptions\InvalidLeadOperationException;
use App\Exceptions\LeadAccessDeniedException;
use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\LeadStatusLog;
use App\Models\LeadVerification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(
        private NotificationService $notificationService,
        private CommissionService $commissionService,
        private OfferService $offerService,
        private HierarchyService $hierarchyService
    ) {}

    // ────────────────────────────────────────────────────────────────
    // METHOD 1: createFromPublicForm()
    // Lead goes to admin pool, no verification needed
    // ────────────────────────────────────────────────────────────────
    public function createFromPublicForm(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $referralCode = isset($data['referral_agent_id']) ? strtoupper(trim($data['referral_agent_id'])) : null;
            $referringAgent = null;

            if ($referralCode) {
                $referringAgent = User::query()->where(fn($q) => $q->where('agent_id', $referralCode))
                    ->where(fn($q) => $q->where('status', 'active'))
                    ->where(fn($q) => $q->whereIn('role', ['agent', 'super_agent']))
                    ->first();
                /** @var User|null $referringAgent */
            }

            $leadData = collect($data)->except(['aadhaar_front', 'aadhaar_back', 'electricity_bill', 'photo', 'other', 'solar_roof_photo', 'bank_passbook', 'referral_agent_id'])->toArray();

            $createData = [
                ...$leadData,
                'source' => 'public_form',
                'referral_agent_id' => $referralCode,
                'status' => 'NEW',
            ];

            if ($referringAgent) {
                $createData['assigned_agent_id'] = $referringAgent->id;
                $createData['assigned_super_agent_id'] = $referringAgent->super_agent_id;
                $createData['owner_type'] = 'agent_pool';
                $createData['verification_status'] = 'not_required';
            } else {
                $createData['owner_type'] = 'admin_pool';
                $createData['verification_status'] = 'not_required';
            }

            /** @var Lead $lead */
            $lead = Lead::forceCreate($createData);

            $this->logStatusChange($lead, null, null, 'NEW', 'Lead received from public form'.($referralCode ? " (Ref: {$referralCode})" : ''));

            if ($referringAgent) {
                /** @var User $referringAgent */
                $this->notifyAgentNewReferralLead($lead, $referringAgent);
                if ($referringAgent->super_agent_id) {
                    $sa = User::find($referringAgent->super_agent_id);
                    if ($sa) {
                        $this->notifySuperAgentNewReferralLead($lead, $sa, $referringAgent);
                    }
                }
            } else {
                $this->notifyAdminNewPublicLead($lead);
            }

            return $lead;
        });
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function createFromAgent(array $data, User $agent): Lead
    {
        return DB::transaction(function () use ($data, $agent) {
            $routing = $this->hierarchyService->resolveInitialRouting($agent);
            $leadData = collect($data)->except(['aadhaar_front', 'aadhaar_back', 'electricity_bill', 'photo', 'other', 'solar_roof_photo', 'bank_passbook'])->toArray();

            /** @var Lead $lead */
            $lead = Lead::forceCreate([
                ...$leadData,
                'source' => 'agent_submission',
                'submitted_by_agent_id' => $agent->id,
                ...$routing,
                'status' => 'NEW',
            ]);

            $this->logStatusChange($lead, $agent->id, null, 'NEW', 'Lead submitted by agent');

            if ($lead->assigned_super_agent_id) {
                $this->notifySuperAgentLeadPendingVerification($lead, $agent);
            } else {
                $this->notifyAdminNewAgentLead($lead, $agent);
            }

            return $lead;
        });
    }

    /**
     * METHOD 2B: createFromEnumerator()
     * Uses HierarchyService to resolve routing for any Enumerator creation scenario (Case 1-3)
     */
    public function createFromEnumerator(array $data, User $enumerator): Lead
    {
        return DB::transaction(function () use ($data, $enumerator) {
            $routing = $this->hierarchyService->resolveInitialRouting($enumerator);
            $leadData = collect($data)->except(['aadhaar_front', 'aadhaar_back', 'electricity_bill', 'photo', 'other', 'solar_roof_photo', 'bank_passbook'])->toArray();

            $lead = Lead::forceCreate([
                ...$leadData,
                'source' => 'enumerator_submission',
                'submitted_by_enumerator_id' => $enumerator->id,
                ...$routing,
                'status' => 'NEW',
            ]);

            $this->logStatusChange($lead, $enumerator->id, null, 'NEW', "Lead submitted by enumerator {$enumerator->name}");
            
            // Notification logic
            if ($lead->verification_status === 'pending_agent_verification') {
                 $agent = User::find($lead->assigned_agent_id);
                 if ($agent) {
                    $this->notificationService->send($agent->id, 'new_enumerator_lead', 'New Enumerator Lead', "Enumerator {$enumerator->name} submitted a new lead: {$lead->ulid}", ['lead_ulid' => $lead->ulid]);
                 }
            } elseif ($lead->verification_status === 'pending_super_agent_verification') {
                 $sa = User::find($lead->assigned_super_agent_id);
                 if ($sa) {
                    $this->notificationService->send($sa->id, 'new_enumerator_lead', 'New Enumerator Lead (Pending Verification)', "Enumerator {$enumerator->name} submitted a lead: {$lead->ulid}", ['lead_ulid' => $lead->ulid]);
                 }
            } else {
                // Admin pool
                $this->notifyAdminNewPublicLead($lead); // reusable for admin pool
            }

            return $lead;
        });
    }
    public function createFromSuperAgent(array $data, User $sa): Lead
    {
        return DB::transaction(function () use ($data, $sa) {
            $routing = $this->hierarchyService->resolveInitialRouting($sa);
            $leadData = collect($data)->except(['aadhaar_front', 'aadhaar_back', 'electricity_bill', 'photo', 'other', 'solar_roof_photo', 'bank_passbook'])->toArray();

            $lead = Lead::forceCreate([
                ...$leadData,
                'source' => 'super_agent_submission',
                'created_by_super_agent_id' => $sa->id,
                ...$routing,
                'status' => 'NEW',
            ]);

            $this->logStatusChange($lead, $sa->id, null, 'NEW', "Lead created by Super Agent {$sa->name}");
            
            if ($lead->assigned_super_agent_id && (int)$lead->assigned_super_agent_id !== (int)$sa->id) {
                // If SA routed to ANOTHER SA (unlikely but possible in deeper hierarchy)
                $this->notifySuperAgentLeadAssigned($lead, $lead->assignedSuperAgent, $sa);
            } else {
                $this->notifyAdminNewAgentLead($lead, $sa);
            }

            return $lead;
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 4a: verifyLeadByAgent()
    // Agent approves the enumerator lead → sends to Super Agent pool
    // ────────────────────────────────────────────────────────────────
    public function verifyLeadByAgent(Lead $lead, User $agent, ?string $notes = null): Lead
    {
        if ((int) $lead->assigned_agent_id !== (int) $agent->id) {
            throw new LeadAccessDeniedException('You are not authorized to verify this lead.');
        }

        if ($lead->verification_status !== 'pending_agent_verification') {
            throw new InvalidLeadOperationException('This lead is not pending agent verification.');
        }

        return DB::transaction(function () use ($lead, $agent, $notes) {
            $hasSuperAgent = ! is_null($agent->super_agent_id);

            $lead->forceFill([
                'verification_status' => $hasSuperAgent ? 'pending_super_agent_verification' : 'not_required',
                'owner_type' => $hasSuperAgent ? 'super_agent_pool' : 'admin_pool',
            ])->save();

            LeadVerification::create([
                'lead_id' => $lead->id,
                'action' => 'verified',
                'performed_by' => $agent->id,
                'performer_role' => 'agent',
                'reason' => $notes,
                'revert_count_at_time' => $lead->revert_count,
            ]);

            $this->logStatusChange($lead, $agent->id, $lead->status, $lead->status,
                'Lead verified by Agent — sent forward for processing');

            if ($hasSuperAgent) {
                $this->notifySuperAgentLeadPendingVerification($lead, $agent);
            } else {
                $this->notifyAdminNewAgentLead($lead, $agent);
            }

            return $lead->fresh();
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 4b: revertLeadByAgent()
    // Agent sends lead back to enumerator
    // ────────────────────────────────────────────────────────────────
    public function revertLeadByAgent(Lead $lead, User $agent, string $reason): Lead
    {
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('A revert reason is required.');
        }

        if ($lead->verification_status !== 'pending_agent_verification') {
            throw new InvalidLeadOperationException('This lead cannot be reverted in its current state.');
        }

        if ((int) $lead->assigned_agent_id !== (int) $agent->id) {
            throw new LeadAccessDeniedException('You are not authorized to revert this lead.');
        }

        return DB::transaction(function () use ($lead, $agent, $reason) {
            $newRevertCount = $lead->revert_count + 1;

            if ($newRevertCount >= 3) {
                 $lead->forceFill([
                     'verification_status' => 'admin_override',
                     'revert_count' => $newRevertCount,
                     'revert_reason' => $reason.' [AUTO-ESCALATED: max reverts reached]',
                     'reverted_at' => now(),
                     'reverted_by' => $agent->id,
                     'owner_type' => 'admin_pool',
                 ])->save();
            } else {
                 $lead->forceFill([
                     'verification_status' => 'reverted_to_enumerator',
                     'revert_count' => $newRevertCount,
                     'revert_reason' => $reason,
                     'reverted_at' => now(),
                     'reverted_by' => $agent->id,
                 ])->save();
            }

            LeadVerification::create([
                'lead_id' => $lead->id,
                'action' => 'reverted',
                'performed_by' => $agent->id,
                'performer_role' => 'agent',
                'reason' => $reason,
                'revert_count_at_time' => $newRevertCount,
            ]);

            return $lead->fresh();
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 4: verifyLead()
    // SA approves the lead → sends to admin pool
    // ────────────────────────────────────────────────────────────────
    public function verifyLead(Lead $lead, User $superAgent, ?string $notes = null): Lead
    {
        if ((int) $lead->assigned_super_agent_id !== (int) $superAgent->id &&
            ! $this->leadBelongsToSuperAgentTeam($lead, $superAgent)) {
            throw new LeadAccessDeniedException('You are not authorized to verify this lead.');
        }

        if (! in_array($lead->verification_status, ['pending_super_agent_verification'])) {
            throw new InvalidLeadOperationException('This lead is not pending verification.');
        }

        return DB::transaction(function () use ($lead, $superAgent, $notes) {
            $lead->forceFill([
                'verification_status' => 'super_agent_verified',
                'verified_by_super_agent_id' => $superAgent->id,
                'verified_at' => now(),
                'owner_type' => 'admin_pool',
            ])->save();

            LeadVerification::create([
                'lead_id' => $lead->id,
                'action' => 'verified',
                'performed_by' => $superAgent->id,
                'performer_role' => 'super_agent',
                'reason' => $notes,
                'revert_count_at_time' => $lead->revert_count,
            ]);

            $this->logStatusChange($lead, $superAgent->id, $lead->status, $lead->status,
                'Lead verified by Super Agent — sent to Admin for processing');
            $this->notifyAdminLeadVerified($lead, $superAgent);

            return $lead->fresh();
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 5: revertLead()
    // SA sends lead back to agent with a reason
    // Auto-escalates to admin after 3 reverts
    // ────────────────────────────────────────────────────────────────
    public function revertLead(Lead $lead, User $superAgent, string $reason): Lead
    {
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('A revert reason is required.');
        }

        if (! in_array($lead->verification_status, ['pending_super_agent_verification'])) {
            throw new InvalidLeadOperationException('This lead cannot be reverted in its current state.');
        }

        if (! $this->leadBelongsToSuperAgentTeam($lead, $superAgent) &&
            (int) $lead->assigned_super_agent_id !== (int) $superAgent->id) {
            throw new LeadAccessDeniedException('You are not authorized to revert this lead.');
        }

        return DB::transaction(function () use ($lead, $superAgent, $reason) {
            $newRevertCount = $lead->revert_count + 1;

            if ($newRevertCount >= 3) {
                // Auto-escalate to admin
                $lead->forceFill([
                    'verification_status' => 'admin_override',
                    'revert_count' => $newRevertCount,
                    'revert_reason' => $reason.' [AUTO-ESCALATED: max reverts reached]',
                    'reverted_at' => now(),
                    'reverted_by' => $superAgent->id,
                    'owner_type' => 'admin_pool',
                ])->save();

                LeadVerification::create([
                    'lead_id' => $lead->id,
                    'action' => 'reverted',
                    'performed_by' => $superAgent->id,
                    'performer_role' => 'super_agent',
                    'reason' => $reason.' [AUTO-ESCALATED]',
                    'revert_count_at_time' => $newRevertCount,
                ]);

                $this->notifyAdminLeadAutoEscalated($lead, $superAgent, $reason);
                $this->notifyAgentLeadAutoEscalated($lead, $reason);
            } else {
                // Normal revert
                $lead->forceFill([
                    'verification_status' => 'reverted_to_agent',
                    'revert_count' => $newRevertCount,
                    'revert_reason' => $reason,
                    'reverted_at' => now(),
                    'reverted_by' => $superAgent->id,
                ])->save();

                LeadVerification::create([
                    'lead_id' => $lead->id,
                    'action' => 'reverted',
                    'performed_by' => $superAgent->id,
                    'performer_role' => 'super_agent',
                    'reason' => $reason,
                    'revert_count_at_time' => $newRevertCount,
                ]);

                $this->notifyAgentLeadReverted($lead, $reason, $newRevertCount);
            }

            return $lead->fresh();
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 6: resubmitLead()
    // Agent corrects and resubmits a reverted lead
    // ────────────────────────────────────────────────────────────────
    public function resubmitLead(Lead $lead, array $correctedData, User $agent): Lead
    {
        if ($lead->verification_status !== 'reverted_to_agent') {
            throw new InvalidLeadOperationException('Only reverted leads can be resubmitted.');
        }

        if ((int) $lead->submitted_by_agent_id !== (int) $agent->id) {
            throw new LeadAccessDeniedException('You can only resubmit your own leads.');
        }

        return DB::transaction(function () use ($lead, $correctedData, $agent) {
            $lead->update([
                ...$correctedData,
                'verification_status' => 'pending_super_agent_verification',
                'revert_reason' => null,
            ]);

            $this->logStatusChange($lead, $agent->id, $lead->status, $lead->status,
                'Lead corrected and resubmitted by agent (revert #'.$lead->revert_count.')');

            if ($lead->assigned_super_agent_id) {
                $this->notifySuperAgentLeadResubmitted($lead, $agent);
            }

            return $lead->fresh();
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 7: assignLeadToSuperAgent()
    // Admin assigns an unassigned (admin pool) lead to a super agent
    // ────────────────────────────────────────────────────────────────
    public function assignLeadToSuperAgent(Lead $lead, User $superAgent, User $admin): Lead
    {
        return DB::transaction(function () use ($lead, $superAgent, $admin) {
            $lead->update([
                'assigned_super_agent_id' => $superAgent->id,
                'owner_type' => 'super_agent_pool',
                'verification_status' => 'not_required',
            ]);

            $this->logStatusChange($lead, $admin->id, $lead->status, $lead->status,
                'Lead assigned to Super Agent '.($superAgent->super_agent_code ?? $superAgent->name).' by Admin');

            $this->notifySuperAgentLeadAssigned($lead, $superAgent, $admin);

            return $lead->fresh();
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 8: assignLeadToAgent()
    // Admin or SA assigns lead to a specific agent
    // ────────────────────────────────────────────────────────────────
    public function assignLeadToAgent(Lead $lead, User $agent, User $assigner): Lead
    {
        if ($assigner->isSuperAgent()) {
            if ((int) $agent->super_agent_id !== (int) $assigner->id) {
                throw new LeadAccessDeniedException('You can only assign leads to agents in your team.');
            }
            if ((int) $lead->assigned_super_agent_id !== (int) $assigner->id) {
                throw new LeadAccessDeniedException('You can only assign leads that belong to your pool.');
            }
        }

        return DB::transaction(function () use ($lead, $agent, $assigner) {
            $lead->update([
                'assigned_agent_id' => $agent->id,
                'assigned_super_agent_id' => $agent->super_agent_id ?? $lead->assigned_super_agent_id,
                'owner_type' => 'agent_pool',
            ]);

            $this->logStatusChange($lead, $assigner->id, $lead->status, $lead->status,
                'Lead assigned to Agent '.($agent->agent_id ?? $agent->name));

            $this->notifyAgentLeadAssigned($lead, $agent, $assigner);

            return $lead->fresh();
        });
    }

    // ────────────────────────────────────────────────────────────────
    // METHOD 9: updateStatus()
    // Core propagation: notifies SA + Agent automatically
    // ────────────────────────────────────────────────────────────────
    public function updateStatus(Lead $lead, string $newStatus, int $changedById, ?string $notes = null, ?UploadedFile $receipt = null): void
    {
        $allStatuses = [
            'NEW', 'ON_HOLD', 'INVALID', 'DUPLICATE', 'REJECTED',
            'REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED',
            'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED',
        ];

        if (! in_array($newStatus, $allStatuses)) {
            throw new \Exception("Invalid status: {$newStatus}");
        }

        if ($lead->status === $newStatus) {
            return;
        }

        DB::transaction(function () use ($lead, $newStatus, $changedById, $notes, $receipt) {
            $oldStatus = $lead->status;
            $changer = User::find($changedById);

            // REVOKE UNPAID COMMISSIONS if old status was completed
            if ($oldStatus === 'COMPLETED' && $newStatus !== 'COMPLETED') {
                $this->commissionService->revokeUnpaidCommissions($lead, $changer);
            }

            $this->logStatusChange($lead, $changedById, $oldStatus, $newStatus, $notes);

            $lead->status = $newStatus;
            $lead->save();

            // UPLOAD RECEIPT if provided
            if ($newStatus === 'COMPLETED' && $receipt) {
                $this->uploadDocument($lead, $receipt, 'receipt', $changedById);
            }

            // AUTO-PROPAGATION: Admin/SA changes → notify downstream parties
            if ($changer) {
                if ($changer->isAdmin()) {
                    if ($lead->assigned_super_agent_id) {
                        $this->notifySuperAgentStatusChanged($lead, $oldStatus, $newStatus, $changer);
                    }
                    if ($lead->assigned_agent_id) {
                        $this->notifyAgentStatusChanged($lead, $oldStatus, $newStatus, $changer);
                    }
                }
                if ($changer->isSuperAgent() && $lead->assigned_agent_id) {
                    $this->notifyAgentStatusChanged($lead, $oldStatus, $newStatus, $changer);
                }
            }

            // TRIGGER OFFERS: if status just became 'REGISTERED' (installation confirmed) or beyond.
            if (in_array($newStatus, ['REGISTERED', 'SITE_SURVEY', 'AT_BANK', 'COMPLETED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED'])) {
                $affectedAgent = null;

                // Priority 1: If submitted by an enumerator, points go to their creator/parent
                if ($lead->submitted_by_enumerator_id) {
                    $enumerator = User::find($lead->submitted_by_enumerator_id);
                    if ($enumerator && $enumerator->parent_id) {
                        $affectedAgent = User::find($enumerator->parent_id);
                    }
                }

                // Priority 2: Standard fallback chain
                if (!$affectedAgent) {
                    if ($lead->assigned_agent_id) {
                        $affectedAgent = User::find($lead->assigned_agent_id);
                    } elseif ($lead->submitted_by_agent_id) {
                        $affectedAgent = User::find($lead->submitted_by_agent_id);
                    } elseif ($lead->assigned_super_agent_id) {
                        // Fallback for direct SA leads
                        $affectedAgent = User::find($lead->assigned_super_agent_id);
                    } elseif ($lead->created_by_super_agent_id) {
                        // Fallback for direct SA leads
                        $affectedAgent = User::find($lead->created_by_super_agent_id);
                    }
                }

                if ($affectedAgent) {
                    $this->offerService->processPoints($lead, $affectedAgent);
                }
            }
        });
    }

    // ────────────────────────────────────────────────────────────────
    // Document upload (unchanged from original)
    // ────────────────────────────────────────────────────────────────
    public function uploadDocument(Lead $lead, UploadedFile $file, string $type, ?int $uploadedById = null): LeadDocument
    {
        $path = $file->store("leads/{$lead->ulid}", 'local');

        return LeadDocument::create([
            'lead_id' => $lead->id,
            'document_type' => $type,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'uploaded_by' => $uploadedById,
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────────────

    private function logStatusChange(Lead $lead, ?int $changedById, ?string $fromStatus, string $toStatus, ?string $notes): void
    {
        LeadStatusLog::create([
            'lead_id' => $lead->id,
            'changed_by' => $changedById,
            'from_status' => $fromStatus ?? $lead->status ?? 'NEW',
            'to_status' => $toStatus,
            'notes' => $notes,
        ]);
    }

    private function leadBelongsToSuperAgentTeam(Lead $lead, User $superAgent): bool
    {
        if ($lead->submitted_by_agent_id) {
            /** @var User|null $submittingAgent */
            $submittingAgent = User::query()->find($lead->submitted_by_agent_id);

            return $submittingAgent && (int) $submittingAgent->super_agent_id === (int) $superAgent->id;
        }

        return false;
    }

    // ── Notification helpers ─────────────────────────────────────────

    private function notifyAdminNewPublicLead(Lead $lead): void
    {
        /** @var User|null $admin */
        $admin = User::query()->where(fn ($q) => $q->where('role', 'admin'))->first();
        if ($admin) {
            $this->notificationService->send(
                $admin->id, 'new_public_lead',
                'New Public Lead Received',
                "New public lead from {$lead->beneficiary_name}, {$lead->beneficiary_district}",
                ['lead_ulid' => $lead->ulid]
            );
        }
    }

    private function notifyAdminNewAgentLead(Lead $lead, User $agent): void
    {
        /** @var User|null $admin */
        $admin = User::query()->where(fn ($q) => $q->where('role', 'admin'))->first();
        if ($admin) {
            $this->notificationService->send(
                $admin->id, 'new_agent_lead',
                'New Agent Lead Submitted',
                "Agent {$agent->agent_id} submitted a new lead: {$lead->ulid}",
                ['lead_ulid' => $lead->ulid, 'agent_id' => $agent->id]
            );
        }
    }

    private function notifyAdminLeadVerified(Lead $lead, User $sa): void
    {
        /** @var User|null $admin */
        $admin = User::query()->where(fn ($q) => $q->where('role', 'admin'))->first();
        if ($admin) {
            $this->notificationService->send(
                $admin->id, 'lead_verified',
                'Lead Verified — Ready for Processing',
                "Lead {$lead->ulid} verified by SA {$sa->super_agent_code} — ready for processing",
                ['lead_ulid' => $lead->ulid, 'sa_id' => $sa->id]
            );
        }
    }

    private function notifyAdminLeadAutoEscalated(Lead $lead, User $sa, string $reason): void
    {
        /** @var User|null $admin */
        $admin = User::query()->where(fn ($q) => $q->where('role', 'admin'))->first();
        if ($admin) {
            $this->notificationService->send(
                $admin->id, 'lead_auto_escalated',
                'Lead Auto-Escalated (Max Reverts)',
                "Lead {$lead->ulid} auto-escalated after 3 reverts. Reason: {$reason}",
                ['lead_ulid' => $lead->ulid, 'sa_id' => $sa->id]
            );
        }
    }

    private function notifySuperAgentLeadPendingVerification(Lead $lead, User $agent): void
    {
        if (! $lead->assigned_super_agent_id) {
            return;
        }
        $sa = User::find($lead->assigned_super_agent_id);
        if ($sa) {
            $this->notificationService->send(
                $sa->id, 'lead_pending_verification',
                'Lead Awaiting Verification',
                "Agent {$agent->agent_id} submitted a lead for verification: {$lead->ulid}",
                ['lead_ulid' => $lead->ulid, 'agent_id' => $agent->id]
            );
        }
    }

    private function notifySuperAgentLeadAssigned(Lead $lead, User $sa, User $admin): void
    {
        $this->notificationService->send(
            $sa->id, 'lead_assigned_to_sa',
            'New Lead Assigned to You',
            "Admin assigned lead {$lead->ulid} to you ({$lead->beneficiary_name})",
            ['lead_ulid' => $lead->ulid]
        );
    }

    private function notifySuperAgentLeadResubmitted(Lead $lead, User $agent): void
    {
        if (! $lead->assigned_super_agent_id) {
            return;
        }
        $sa = User::find($lead->assigned_super_agent_id);
        if ($sa) {
            $this->notificationService->send(
                $sa->id, 'lead_resubmitted',
                'Lead Resubmitted for Verification',
                "Agent {$agent->agent_id} resubmitted lead {$lead->ulid} for verification",
                ['lead_ulid' => $lead->ulid, 'agent_id' => $agent->id, 'revert_count' => $lead->revert_count]
            );
        }
    }

    private function notifySuperAgentStatusChanged(Lead $lead, string $from, string $to, User $changer): void
    {
        if (! $lead->assigned_super_agent_id) {
            return;
        }
        $sa = User::find($lead->assigned_super_agent_id);
        if ($sa) {
            $this->notificationService->send(
                $sa->id, 'lead_status_changed',
                'Lead Status Updated',
                "Lead {$lead->ulid} status → {$to}",
                ['lead_ulid' => $lead->ulid, 'from' => $from, 'to' => $to]
            );
        }
    }

    private function notifyAgentLeadAssigned(Lead $lead, User $agent, User $assigner): void
    {
        $byLabel = $assigner->isAdmin() ? 'Admin' : 'your Super Agent';
        $this->notificationService->send(
            $agent->id, 'lead_assigned_to_agent',
            'New Lead Assigned to You',
            "{$byLabel} assigned lead {$lead->ulid} to you",
            ['lead_ulid' => $lead->ulid]
        );
    }

    private function notifyAgentLeadReverted(Lead $lead, string $reason, int $count): void
    {
        if (! $lead->assigned_agent_id && ! $lead->submitted_by_agent_id) {
            return;
        }
        $agentId = $lead->assigned_agent_id ?? $lead->submitted_by_agent_id;
        $this->notificationService->send(
            $agentId, 'lead_reverted',
            '↩ Lead Returned for Correction',
            "Lead {$lead->ulid} returned by your Super Agent. Reason: {$reason}",
            ['lead_ulid' => $lead->ulid, 'reason' => $reason, 'revert_count' => $count]
        );
    }

    private function notifyAgentLeadAutoEscalated(Lead $lead, string $reason): void
    {
        $agentId = $lead->assigned_agent_id ?? $lead->submitted_by_agent_id;
        if (! $agentId) {
            return;
        }
        $this->notificationService->send(
            $agentId, 'lead_auto_escalated',
            'Lead Escalated to Admin',
            "Lead {$lead->ulid} has been escalated to Admin after 3 reverts.",
            ['lead_ulid' => $lead->ulid, 'reason' => $reason]
        );
    }

    private function notifyAgentNewReferralLead(Lead $lead, User $agent): void
    {
        $this->notificationService->send(
            $agent->id, 'new_referral_lead',
            'New Referral Lead',
            "A new lead ({$lead->beneficiary_name}) was submitted using your referral ID.",
            ['lead_ulid' => $lead->ulid]
        );
    }

    private function notifySuperAgentNewReferralLead(Lead $lead, User $sa, User $agent): void
    {
        $this->notificationService->send(
            $sa->id, 'new_team_referral_lead',
            'New Referral Lead in Team',
            "A new lead ({$lead->beneficiary_name}) was submitted via Agent {$agent->agent_id}'s referral ID.",
            ['lead_ulid' => $lead->ulid, 'agent_id' => $agent->id]
        );
    }

    private function notifyAgentStatusChanged(Lead $lead, string $from, string $to, User $changer): void
    {
        $agentId = $lead->assigned_agent_id ?? $lead->submitted_by_agent_id;
        if (! $agentId) {
            return;
        }
        $byLabel = $changer->isAdmin() ? 'Admin' : 'your Super Agent';
        $this->notificationService->send(
            $agentId, 'lead_status_changed',
            'Lead Status Updated',
            "Lead {$lead->ulid} status → {$to} (updated by {$byLabel})",
            ['lead_ulid' => $lead->ulid, 'from' => $from, 'to' => $to]
        );
    }
}
