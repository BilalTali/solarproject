<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'beneficiary_name', 'beneficiary_mobile', 'beneficiary_email',
        'beneficiary_state', 'beneficiary_district', 'beneficiary_address', 'beneficiary_pincode',
        'consumer_number', 'discom_name', 'monthly_bill_amount',
        'roof_size', 'system_capacity', 'query_message', 'notes', 'follow_up_date',
        'revert_reason'
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date'    => 'date',
            'commission_paid_at'=> 'datetime',
            'commission_paid'   => 'boolean',
            'verified_at'       => 'datetime',
            'reverted_at'       => 'datetime',
            'revert_count'      => 'integer',
        ];
    }

    protected $appends = ['formatted_commissions'];

    // ── Relationships ─────────────────────────────────────────────────

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function assignedSuperAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_super_agent_id');
    }

    public function submittedByAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_agent_id');
    }

    public function createdBySuperAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_super_agent_id');
    }

    public function verifiedBySuperAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_super_agent_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(LeadStatusLog::class)->orderBy('created_at', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LeadDocument::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(LeadVerification::class)->orderBy('created_at', 'asc');
    }

    // ── Helper Methods ────────────────────────────────────────────────

    public function isInAdminPool(): bool
    {
        return $this->owner_type === 'admin_pool';
    }

    public function isInSuperAgentPool(): bool
    {
        return $this->owner_type === 'super_agent_pool';
    }

    public function needsVerification(): bool
    {
        return in_array($this->verification_status, [
            'pending_super_agent_verification',
            'reverted_to_agent',
        ]);
    }

    public function isVerified(): bool
    {
        return in_array($this->verification_status, [
            'super_agent_verified',
            'not_required',
            'admin_override',
        ]);
    }

    public function hasReachedMaxReverts(): bool
    {
        return $this->revert_count >= 3;
    }

    // ── Query Scopes ──────────────────────────────────────────────────

    /**
     * Leads visible to a specific super agent:
     * 1. Explicitly assigned to them (assigned_super_agent_id)
     * 2. Created by them (created_by_super_agent_id)
     * 3. Submitted by an agent in their team (agent.super_agent_id)
     * 4. Assigned to an agent in their team (legacy: assigned_agent_id → agent.super_agent_id)
     */
    public function scopeVisibleToSuperAgent(Builder $q, int $superAgentId): Builder
    {
        return $q->where(function ($query) use ($superAgentId) {
            $query->where('assigned_super_agent_id', $superAgentId)
                  ->orWhere('created_by_super_agent_id', $superAgentId)
                  ->orWhereHas('submittedByAgent', function ($q2) use ($superAgentId) {
                      $q2->where('super_agent_id', $superAgentId);
                  })
                  ->orWhereHas('assignedAgent', function ($q2) use ($superAgentId) {
                      $q2->where('super_agent_id', $superAgentId);
                  });
        });
    }

    /**
     * Leads that need verification by a specific super agent:
     * pending AND (assigned to SA, OR submitted by their team agent)
     */
    public function scopeNeedsVerificationBySuperAgent(Builder $q, int $superAgentId): Builder
    {
        return $q->where('verification_status', 'pending_super_agent_verification')
                 ->where(function ($query) use ($superAgentId) {
                     $query->where('assigned_super_agent_id', $superAgentId)
                           ->orWhere('created_by_super_agent_id', $superAgentId)
                           ->orWhereHas('submittedByAgent', function ($q2) use ($superAgentId) {
                               $q2->where('super_agent_id', $superAgentId);
                           });
                 });
    }

    /**
     * Leads visible to a specific agent:
     * submitted by them OR directly assigned to them
     */
    public function scopeVisibleToAgent(Builder $q, int $agentId): Builder
    {
        return $q->where(function ($query) use ($agentId) {
            $query->where('submitted_by_agent_id', $agentId)
                  ->orWhere('assigned_agent_id', $agentId);
        });
    }

    // ── Virtual Attribute: formatted commissions ──────────────────────

    /**
     * Virtual attribute to provide formatted commissions for the frontend.
     */
    public function getFormattedCommissionsAttribute(): array
    {
        $commissions = $this->getRelationValue('commissions');

        $sa = $commissions->firstWhere('payee_role', 'super_agent');
        $ag = $commissions->firstWhere('payee_role', 'agent');

        return [
            'super_agent_commission' => $sa ? $this->formatComm($sa) : null,
            'agent_commission'       => $ag ? $this->formatComm($ag) : null,
        ];
    }

    private function formatComm($c): array
    {
        return [
            'id'              => $c->id,
            'amount'          => (float) $c->amount,
            'payee_name'      => $c->payee?->name ?? 'Unknown',
            'payee_code'      => $c->payee_role === 'super_agent' ? ($c->payee?->super_agent_code ?? '') : ($c->payee?->agent_id ?? ''),
            'entered_by_name' => $c->enteredBy?->name ?? 'System',
            'payment_status'  => $c->payment_status,
            'is_locked'       => $c->isLocked(),
            'is_editable'     => !$c->isLocked(),
        ];
    }
}
