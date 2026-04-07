<?php

namespace App\Services;

use App\Exceptions\CommissionAccessDeniedException;
use App\Exceptions\CommissionAlreadyExistsException;
use App\Exceptions\CommissionLockedException;
use App\Exceptions\LeadNotCompletedException;
use App\Models\Commission;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * UNIFIED COMMISSION ENTRY
     * Handles all roles (Super Agent, Agent, Enumerator) based on the hierarchy chain.
     */
    public function enterCommission(
        Lead $lead,
        User $payee,
        float $amount,
        User $payer
    ): Commission {
        if ($lead->status !== 'COMPLETED') {
            throw new LeadNotCompletedException('Commission can only be entered for leads that have reached the COMPLETED milestone.');
        }

        // Authorization logic based on parentage (Allow ancestors to enter commission)
        $hierarchyService = app(HierarchyService::class);
        $logicalParentId = $hierarchyService->getLogicalParentId($payee, $lead);
        $ascendantSAId = $hierarchyService->findAscendantSuperAgentId($payee);

        $isAuthorized = ($payer->isAdmin() || 
                         (int) $logicalParentId === (int) $payer->id || 
                         ($payer->isSuperAgent() && (int) $ascendantSAId === (int) $payer->id));

        if (! $isAuthorized) {
            throw new CommissionAccessDeniedException('You can only enter commissions for your subordinates.');
        }

        $existing = Commission::query()->withTrashed()->where(fn($q) => $q->where('lead_id', $lead->id))
            ->where(fn($q) => $q->where('payee_id', $payee->id))
            ->first();

        if ($existing) {
            if ($existing->isPaid()) {
                throw new CommissionAlreadyExistsException('Commission already paid for this lead.');
            }
            $existing->forceDelete();
        }

        return DB::transaction(function () use ($lead, $payee, $amount, $payer) {
            $commission = Commission::create([
                'lead_id' => $lead->id,
                'payee_id' => $payee->id,
                'payee_role' => $payee->role,
                'amount' => $amount,
                'entered_by' => $payer->id,
                'locked_at' => now()->addHours(24),
            ]);

            $this->refreshLeadCommissionStatus($lead);
            
            // Notify Payee
            app(NotificationService::class)->send(
                $payee->id,
                'commission_entered',
                '💰 Commission Entered',
                "{$payer->name} has entered ₹{$amount} commission for lead {$lead->ulid}.",
                ['lead_ulid' => $lead->ulid, 'commission_id' => $commission->id, 'amount' => $amount]
            );

            return $commission;
        });
    }

    /**
     * EDIT COMMISSION (within 24 hours only)
     */
    public function editCommission(
        Commission $commission,
        float $newAmount,
        User $editor
    ): Commission {
        if ($commission->isLocked() && ! $editor->isAdmin()) {
            throw new CommissionLockedException('This commission cannot be edited. It was locked 24 hours after creation.');
        }

        if ($commission->isPaid()) {
            throw new CommissionLockedException('Paid commissions cannot be edited.');
        }

        $hierarchyService = app(HierarchyService::class);
        $logicalParentId = $hierarchyService->getLogicalParentId($commission->payee, $commission->lead);
        $isParent = (int) $logicalParentId === (int) $editor->id;
        $isEnterer = (int) $commission->entered_by === (int) $editor->id;

        if (!$isParent && !$isEnterer && !$editor->isAdmin()) {
            throw new CommissionAccessDeniedException('You are not authorized to edit this commission.');
        }

        $commission->update([
            'amount' => $newAmount,
            'entered_by' => $editor->id,
        ]);

        $this->refreshLeadCommissionStatus($commission->lead);

        return $commission;
    }

    // Removed role-specific enterEnumeratorCommission (unified into enterCommission)

    /**
     * MARK COMMISSION AS PAID
     * Hierarchy-aware: Parent can pay their direct children. Admin can pay anyone.
     */
    public function markAsPaid(
        Commission $commission,
        array $paymentData,
        User $payer
    ): Commission {
        $payee = $commission->payee;
        $hierarchyService = app(HierarchyService::class);
        $logicalParentId = $hierarchyService->getLogicalParentId($payee, $commission->lead);
        $ascendantSAId = $hierarchyService->findAscendantSuperAgentId($payee);

        $isAuthorized = ($payer->isAdmin() || 
                         (int) $logicalParentId === (int) $payer->id || 
                         ($payer->isSuperAgent() && (int) $ascendantSAId === (int) $payer->id));

        if (! $isAuthorized) {
            throw new CommissionAccessDeniedException('You can only mark payments for your subordinates.');
        }

        if ($commission->isPaid()) {
            throw new \InvalidArgumentException('Commission is already marked as paid.');
        }

        $commission->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'paid_by' => $payer->id,
            'payment_method' => $paymentData['payment_method'],
            'payment_reference' => $paymentData['payment_reference'],
            'payment_notes' => $paymentData['payment_notes'] ?? null,
        ]);

        $this->notifyPaymentMade($commission);

        return $commission;
    }

    /**
     * REVOKE UNPAID COMMISSIONS
     * Called when lead status moves away from terminal 'COMPLETED' or MNRE statuses.
     */
    public function revokeUnpaidCommissions(Lead $lead, ?User $revokedBy = null): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Commission> $commissions */
        $commissions = Commission::query()->where(fn ($q) => $q->where('lead_id', $lead->id))->unpaid()->get();

        foreach ($commissions as $commission) {
            $this->notifyCommissionRevoked($commission, $revokedBy);
            $commission->forceDelete();
        }

        if ($commissions->isNotEmpty()) {
            $this->refreshLeadCommissionStatus($lead);
        }
    }

    /**
     * DYNAMIC COMMISSION STATUS REFRESH
     * Analyzes the hierarchy chain to see if all required commissions are entered.
     */
    private function refreshLeadCommissionStatus(Lead $lead): void
    {
        $lead->refresh();
        $submitter = $lead->submittedByEnumerator ?? $lead->submittedByAgent ?? $lead->createdBySuperAgent;
        
        if (! $submitter) {
            $lead->update(['commission_entry_status' => 'none']);
            return;
        }

        $hierarchyService = app(HierarchyService::class);
        $chain = $hierarchyService->getCommissionChain($submitter, $lead);
        
        // Subject (Submitter) often gets commission too (if Enum)
        $requiredPayees = [];
        if ($submitter->role === 'enumerator') {
            $requiredPayees[] = $submitter->id;
        }
        foreach ($chain as $user) {
            $requiredPayees[] = $user->id;
        }

        $enteredCount = Commission::query()->where(fn($q) => $q->where('lead_id', $lead->id))->whereIn('payee_id', $requiredPayees)->count();
        
        $status = match (true) {
            $enteredCount === 0 => 'none',
            $enteredCount === count($requiredPayees) => 'all_entered',
            default => 'partially_entered',
        };

        $lead->update(['commission_entry_status' => $status]);
    }

    /**
     * DYNAMIC COMMISSION PROMPTS
     * Returns a list of required commissions for the given lead.
     */
    public function getCommissionStatus(Lead $lead): array
    {
        if ($lead->status !== 'COMPLETED') {
            return [];
        }

        $hierarchyService = app(HierarchyService::class);
        $prompts = [];
        $processedPayeeIds = [];

        // 1. Identify all potential payees (Submitter, Assignee, Enumerator)
        $potentialPayees = collect([
            $lead->submittedByEnumerator,
            $lead->submittedByAgent,
            $lead->assignedAgent,
            $lead->createdBySuperAgent
        ])->filter()->unique('id');

        foreach ($potentialPayees as $payee) {
            /** @var User $payee */
            if ($payee->isAdmin()) continue;
            
            $prompts[] = $this->buildPrompt($lead, $payee, $payee->role);
            $processedPayeeIds[] = $payee->id;
        }

        // 2. Upstream Hierarchy (BDM/Super Agent level and above)
        // We look at the submitter's chain if available, otherwise the assignee's chain.
        $startingUser = $lead->submittedByEnumerator ?? $lead->submittedByAgent ?? $lead->assignedAgent ?? $lead->createdBySuperAgent;
        
        if ($startingUser) {
            $chain = $hierarchyService->getCommissionChain($startingUser, $lead);
            foreach ($chain as $user) {
                if (!in_array($user->id, $processedPayeeIds)) {
                    $prompts[] = $this->buildPrompt($lead, $user, $user->role);
                    $processedPayeeIds[] = $user->id;
                }
            }
        }

        return $prompts;
    }

    private function buildPrompt(Lead $lead, User $payee, string $role): array
    {
        $hierarchyService = app(HierarchyService::class);
        $payerId = $hierarchyService->getLogicalParentId($payee, $lead);
        $payer = $payerId ? User::find($payerId) : null;

        $comm = Commission::query()->where(fn($q) => $q->where('lead_id', $lead->id))->where(fn($q) => $q->where('payee_id', $payee->id))->first();
        
        return [
            'payee_id' => $payee->id,
            'payee_name' => $payee->name,
            'payee_role' => $role,
            'payee_code' => $payee->role === 'super_agent' ? $payee->super_agent_code : ($payee->role === 'agent' ? $payee->agent_id : $payee->enumerator_id),
            'payee_type_label' => match($role) {
                'super_agent' => 'Business Development Manager',
                'agent' => 'Business Development Executive',
                'enumerator' => 'Enumerator',
                default => 'Executive',
            },
            'payer_id' => $payerId,
            'payer_name' => $payer?->name ?? 'Admin',
            'payer_role' => $payer ? $payer->role : 'admin',
            'status' => $comm ? 'entered' : 'pending',
            'amount' => $comm ? (float)$comm->amount : null,
            'suggested_amount' => $this->getSuggestedAmount($lead, $payee, $payerId),
            'payment_status' => $comm ? $comm->payment_status : null,
            'commission_id' => $comm ? $comm->id : null,
            'is_editable' => $comm ? (! $comm->isLocked() && ! $comm->isPaid()) : true,
        ];
    }

    private function getSuggestedAmount(Lead $lead, User $payee, ?int $payerId = null): float
    {
        $capacity = $lead->system_capacity ?: '1kw';
        $payerId = $payerId ?? $payee->parent_id;

        // 1. Try to find slab owned by the Payer
        $slabQuery = \App\Models\CommissionSlab::query()->where(fn($q) => $q->where('capacity', $capacity));
        
        if ($payerId) {
            $slab = (clone $slabQuery)->where(fn($q) => $q->where('super_agent_id', $payerId))->first();
        } else {
            $slab = (clone $slabQuery)->whereNull('super_agent_id')->first();
        }

        // 2. Fallback to System default (NULL super_agent_id)
        if (!$slab && $payerId) {
            $slab = (clone $slabQuery)->whereNull('super_agent_id')->first();
        }

        if (!$slab) return 0.0;

        return match($payee->role) {
            'agent' => (float)$slab->agent_commission,
            'super_agent' => (float)$slab->super_agent_override,
            'enumerator' => (float)$slab->enumerator_commission,
            default => 0.0
        };
    }

    /**
     * GET COMMISSION DATA FOR A LEAD
     * Returns a predictable list of all commissions associated with the lead.
     */
    public function getLeadCommissions(Lead $lead): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Commission> $commissions */
        $commissions = Commission::query()
            ->where(fn ($q) => $q->where('lead_id', $lead->id))
            ->with(['payee', 'enteredBy'])
            ->get();

        return $commissions->map(fn($c) => $this->formatCommissionForResponse($c))->toArray();
    }

    private function formatCommissionForResponse(Commission $c): array
    {
        return [
            'id' => $c->id,
            'amount' => (float) $c->amount,
            'payee_id' => $c->payee_id,
            'payee_role' => $c->payee_role,
            'payee_name' => $c->payee->name,
            'payee_code' => match($c->payee_role) {
                'super_agent' => $c->payee?->super_agent_code ?? '',
                'agent' => $c->payee?->agent_id ?? '',
                'enumerator' => $c->payee?->enumerator_id ?? '',
                default => '',
            },
            'entered_by' => $c->enteredBy->name,
            'entered_at' => $c->created_at->toIso8601String(),
            'payment_status' => $c->payment_status,
            'is_locked' => $c->isLocked(),
            'is_editable' => ! $c->isLocked(),
        ];
    }

    // ── Private notification helpers ──────────────────────────────────
    private function notifySuperAgentCommissionEntered(Commission $c, User $sa, Lead $lead, float $amount, User $admin): void
    {
        app(NotificationService::class)->send(
            $sa->id,
            'commission_entered',
            '💰 Commission Entered for You',
            "Admin has entered ₹{$amount} commission for lead {$lead->ulid} (Beneficiary: {$lead->beneficiary_name}).",
            ['lead_ulid' => $lead->ulid, 'commission_id' => $c->id, 'amount' => $amount]
        );
    }

    private function notifyAgentCommissionEntered(Commission $c, User $agent, Lead $lead, float $amount, User $sa): void
    {
        app(NotificationService::class)->send(
            $agent->id,
            'commission_entered',
            '💰 You Earned a Commission!',
            "Your Super Agent {$sa->name} has entered ₹{$amount} as your commission for lead {$lead->ulid}.",
            ['lead_ulid' => $lead->ulid, 'commission_id' => $c->id, 'amount' => $amount]
        );
    }

    private function notifyAgentDirectCommissionEntered(Commission $c, User $agent, Lead $lead, float $amount, User $admin): void
    {
        app(NotificationService::class)->send(
            $agent->id,
            'commission_entered',
            '💰 Commission Entered by Admin',
            "Admin has entered ₹{$amount} as your commission for lead {$lead->ulid}.",
            ['lead_ulid' => $lead->ulid, 'commission_id' => $c->id, 'amount' => $amount]
        );
    }

    private function notifyPaymentMade(Commission $commission): void
    {
        $payee = $commission->payee;
        $paidBy = $commission->paidBy;
        app(NotificationService::class)->send(
            $payee->id,
            'commission_paid',
            '✅ Commission Payment Received',
            "₹{$commission->amount} has been marked as paid by {$paidBy->name}. Ref: {$commission->payment_reference}",
            ['commission_id' => $commission->id, 'amount' => $commission->amount]
        );
    }

    private function notifyCommissionRevoked(Commission $commission, ?User $revokedBy = null): void
    {
        $payee = $commission->payee;
        $lead = $commission->lead;
        $revokedByName = $revokedBy ? $revokedBy->name : 'Admin (Status Update)';

        app(NotificationService::class)->send(
            $payee->id,
            'commission_revoked',
            '⚠️ Commission Revoked',
            "Your commission of ₹{$commission->amount} for lead {$lead->ulid} has been revoked due to a status update by {$revokedByName}.",
            ['lead_ulid' => $lead->ulid, 'amount' => $commission->amount]
        );
    }
}
