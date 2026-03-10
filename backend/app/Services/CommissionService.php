<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CommissionAlreadyExistsException;
use App\Exceptions\CommissionLockedException;
use App\Exceptions\CommissionAccessDeniedException;
use App\Exceptions\LeadNotCompletedException;

class CommissionService
{
    /**
     * ENTER SUPER AGENT COMMISSION
     * Called by Admin after marking lead as completed.
     * Creates commission record where payee = super agent of that lead's agent.
     */
    public function enterSuperAgentCommission(
        Lead $lead,
        float $amount,
        User $admin
    ): Commission {
        if ($lead->status !== 'completed') {
            throw new LeadNotCompletedException('Commission can only be entered for completed leads.');
        }

        $existing = Commission::withTrashed()->where('lead_id', $lead->id)
                               ->where('payee_role', 'super_agent')
                               ->first();
        if ($existing) {
            if ($existing->isPaid()) {
                throw new CommissionAlreadyExistsException('Super agent commission already paid for this lead.');
            }
            // If it exists but is unpaid (even if soft-deleted), we force delete it to allow fresh entry
            $existing->forceDelete();
        }

        $agent = $lead->assignedAgent;
        $superAgent = null;

        if ($agent) {
            $superAgent = $agent->superAgent;
        } else {
            // Fallback for leads created by Super Agent directly (no agent assigned)
            $superAgentId = $lead->assigned_super_agent_id ?? $lead->created_by_super_agent_id;
            if ($superAgentId) {
                $superAgent = User::find($superAgentId);
            }
        }

        if (!$superAgent) {
            if ($agent) {
                // If there's an agent but no super agent, it's a direct agent
                return $this->enterDirectAgentCommission($lead, $amount, $admin);
            }
            throw new \InvalidArgumentException('Lead has no assigned agent or super agent. Cannot enter commission.');
        }

        return DB::transaction(function () use ($lead, $amount, $admin, $superAgent) {
            $commission = Commission::create([
                'lead_id'    => $lead->id,
                'payee_id'   => $superAgent->id,
                'payee_role' => 'super_agent',
                'amount'     => $amount,
                'entered_by' => $admin->id,
                'locked_at'  => now()->addHours(24),
            ]);

            $this->refreshLeadCommissionStatus($lead);
            $this->notifySuperAgentCommissionEntered($commission, $superAgent, $lead, $amount, $admin);

            return $commission;
        });
    }

    /**
     * ENTER AGENT COMMISSION (by Super Agent)
     */
    public function enterAgentCommission(
        Lead $lead,
        float $amount,
        User $superAgent
    ): Commission {
        if ($lead->status !== 'completed') {
            throw new LeadNotCompletedException('Commission can only be entered for completed leads.');
        }

        $agent = $lead->assignedAgent;
        if (!$agent) {
            throw new \InvalidArgumentException('Lead has no assigned agent.');
        }

        if ((int)$agent->super_agent_id !== (int)$superAgent->id) {
            throw new CommissionAccessDeniedException(
                'You can only enter commissions for agents in your team.'
            );
        }

        $existing = Commission::withTrashed()->where('lead_id', $lead->id)
                               ->where('payee_role', 'agent')
                               ->first();
        if ($existing) {
            if ($existing->isPaid()) {
                throw new CommissionAlreadyExistsException('Agent commission already paid for this lead.');
            }
            $existing->forceDelete();
        }

        return DB::transaction(function () use ($lead, $amount, $superAgent, $agent) {
            $commission = Commission::create([
                'lead_id'    => $lead->id,
                'payee_id'   => $agent->id,
                'payee_role' => 'agent',
                'amount'     => $amount,
                'entered_by' => $superAgent->id,
                'locked_at'  => now()->addHours(24),
            ]);

            $this->refreshLeadCommissionStatus($lead);
            $this->notifyAgentCommissionEntered($commission, $agent, $lead, $amount, $superAgent);

            return $commission;
        });
    }

    /**
     * ENTER DIRECT AGENT COMMISSION (by Admin when agent has no super agent)
     */
    public function enterDirectAgentCommission(
        Lead $lead,
        float $amount,
        User $admin
    ): Commission {
        $agent = $lead->assignedAgent;

        $existing = Commission::withTrashed()->where('lead_id', $lead->id)
                               ->where('payee_role', 'agent')
                               ->first();
        if ($existing) {
            if ($existing->isPaid()) {
                throw new CommissionAlreadyExistsException('Agent commission already paid for this lead.');
            }
            $existing->forceDelete();
        }

        return DB::transaction(function () use ($lead, $amount, $admin, $agent) {
            $commission = Commission::create([
                'lead_id'    => $lead->id,
                'payee_id'   => $agent->id,
                'payee_role' => 'agent',
                'amount'     => $amount,
                'entered_by' => $admin->id,
                'locked_at'  => now()->addHours(24),
            ]);

            $this->refreshLeadCommissionStatus($lead);
            $this->notifyAgentDirectCommissionEntered($commission, $agent, $lead, $amount, $admin);

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
        if ($commission->isLocked() && !$editor->isAdmin()) {
            throw new CommissionLockedException('This commission cannot be edited. It was locked 24 hours after creation.');
        }

        if ($commission->isPaid()) {
            throw new CommissionLockedException('Paid commissions cannot be edited.');
        }

        if ($editor->id !== $commission->entered_by && !$editor->isAdmin()) {
            throw new CommissionAccessDeniedException('You are not authorized to edit this commission.');
        }

        $commission->update([
            'amount'     => $newAmount,
            'entered_by' => $editor->id,
        ]);

        $this->refreshLeadCommissionStatus($commission->lead);

        return $commission;
    }

    /**
     * MARK COMMISSION AS PAID
     */
    public function markAsPaid(
        Commission $commission,
        array $paymentData,
        User $payer
    ): Commission {
        if ($commission->isForSuperAgent() && !$payer->isAdmin()) {
            throw new CommissionAccessDeniedException('Only admin can mark super agent commission as paid.');
        }

        if ($commission->isForAgent() && !$payer->isSuperAgent() && !$payer->isAdmin()) {
            throw new CommissionAccessDeniedException('Only a super agent (or admin for direct) can mark agent commission as paid.');
        }

        if ($commission->isForAgent() && $payer->isSuperAgent()) {
            $agent = $commission->payee;
            if ((int)$agent->super_agent_id !== (int)$payer->id) {
                throw new CommissionAccessDeniedException('You can only mark payments for agents in your team.');
            }
        }

        if ($commission->isPaid()) {
            throw new \InvalidArgumentException('Commission is already marked as paid.');
        }

        $commission->update([
            'payment_status'    => 'paid',
            'paid_at'           => now(),
            'paid_by'           => $payer->id,
            'payment_method'    => $paymentData['payment_method'],
            'payment_reference' => $paymentData['payment_reference'],
            'payment_notes'     => $paymentData['payment_notes'] ?? null,
        ]);

        $this->notifyPaymentMade($commission);

        return $commission;
    }

    /**
     * REVOKE UNPAID COMMISSIONS
     * Called when lead status moves away from 'completed'.
     */
    public function revokeUnpaidCommissions(Lead $lead, ?User $revokedBy = null): void
    {
        $commissions = Commission::where('lead_id', $lead->id)->unpaid()->get();

        foreach ($commissions as $commission) {
            $this->notifyCommissionRevoked($commission, $revokedBy);
            $commission->forceDelete();
        }

        if ($commissions->isNotEmpty()) {
            $this->refreshLeadCommissionStatus($lead);
        }
    }

    /**
     * Re-calculate and update lead.commission_entry_status
     */
    private function refreshLeadCommissionStatus(Lead $lead): void
    {
        $lead->refresh();
        $hasSA    = Commission::where('lead_id', $lead->id)->where('payee_role', 'super_agent')->exists();
        $hasAgent = Commission::where('lead_id', $lead->id)->where('payee_role', 'agent')->exists();

        $status = match (true) {
            $hasSA && $hasAgent => 'both_entered',
            $hasSA              => 'super_agent_entered',
            $hasAgent           => 'agent_entered',
            default             => 'none',
        };

        $lead->update(['commission_entry_status' => $status]);
    }

    /**
     * GET COMMISSION DATA FOR A LEAD
     */
    public function getLeadCommissions(Lead $lead): array
    {
        $saCommission    = Commission::where('lead_id', $lead->id)->where('payee_role', 'super_agent')->with('payee', 'enteredBy')->first();
        $agentCommission = Commission::where('lead_id', $lead->id)->where('payee_role', 'agent')->with('payee', 'enteredBy')->first();

        return [
            'super_agent_commission' => $saCommission ? [
                'id'             => $saCommission->id,
                'amount'         => (float) $saCommission->amount,
                'payee_name'     => $saCommission->payee->name,
                'payee_code'     => $saCommission->payee->super_agent_code ?? null,
                'entered_by'     => $saCommission->enteredBy->name,
                'entered_at'     => $saCommission->created_at->toIso8601String(),
                'payment_status' => $saCommission->payment_status,
                'is_locked'      => $saCommission->isLocked(),
                'is_editable'    => !$saCommission->isLocked(),
            ] : null,
            'agent_commission' => $agentCommission ? [
                'id'             => $agentCommission->id,
                'amount'         => (float) $agentCommission->amount,
                'payee_name'     => $agentCommission->payee->name,
                'payee_code'     => $agentCommission->payee->agent_id ?? null,
                'entered_by'     => $agentCommission->enteredBy->name,
                'entered_at'     => $agentCommission->created_at->toIso8601String(),
                'payment_status' => $agentCommission->payment_status,
                'is_locked'      => $agentCommission->isLocked(),
                'is_editable'    => !$agentCommission->isLocked(),
            ] : null,
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
