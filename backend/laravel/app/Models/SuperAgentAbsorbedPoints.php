<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $super_agent_id
 * @property int $source_agent_id
 * @property int $offer_id
 * @property int $absorbed_installations Number of partial installations absorbed (the shortfall amount)
 * @property int $agent_total_installations Agent's total installations in this offer at time of expiry
 * @property int $offer_target The target_points of the offer — stored for historical accuracy
 * @property string $absorption_reason
 * @property \Illuminate\Support\Carbon $absorbed_at When the absorption was processed (offer expiry job ran)
 * @property string $status unclaimed: SA has not claimed yet · claimed: admin notified · delivered: admin confirmed
 * @property int|null $claimed_by
 * @property \Illuminate\Support\Carbon|null $claimed_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property int|null $approved_by
 * @property string|null $admin_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User|null $claimedBy
 * @property-read \App\Models\Offer $offer
 * @property-read \App\Models\User $sourceAgent
 * @property-read \App\Models\User $superAgent
 * @method static Builder<static>|SuperAgentAbsorbedPoints claimed()
 * @method static Builder<static>|SuperAgentAbsorbedPoints forSA(int $saId)
 * @method static Builder<static>|SuperAgentAbsorbedPoints newModelQuery()
 * @method static Builder<static>|SuperAgentAbsorbedPoints newQuery()
 * @method static Builder<static>|SuperAgentAbsorbedPoints query()
 * @method static Builder<static>|SuperAgentAbsorbedPoints unclaimed()
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereAbsorbedAt($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereAbsorbedInstallations($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereAbsorptionReason($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereAdminNotes($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereAgentTotalInstallations($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereApprovedBy($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereClaimedAt($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereClaimedBy($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereCreatedAt($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereDeliveredAt($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereId($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereOfferId($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereOfferTarget($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereSourceAgentId($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereStatus($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereSuperAgentId($value)
 * @method static Builder<static>|SuperAgentAbsorbedPoints whereUpdatedAt($value)
 */
class SuperAgentAbsorbedPoints extends Model
{
    protected $table = 'super_agent_absorbed_points';

    protected $fillable = [
        'super_agent_id', 'source_agent_id', 'offer_id',
        'absorbed_installations', 'agent_total_installations',
        'offer_target', 'absorption_reason', 'absorbed_at',
        'status', 'claimed_by', 'claimed_at', 'delivered_at',
        'approved_by', 'admin_notes',
    ];

    protected $casts = [
        'absorbed_at' => 'datetime',
        'claimed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'absorbed_installations' => 'integer',
        'agent_total_installations' => 'integer',
        'offer_target' => 'integer',
    ];

    public function superAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_agent_id');
    }

    public function sourceAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_agent_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeUnclaimed(Builder $q): Builder
    {
        return $q->where(fn ($q) => $q->where('status', 'unclaimed'));
    }

    public function scopeClaimed(Builder $q): Builder
    {
        return $q->where(fn ($q) => $q->where('status', 'claimed'));
    }

    public function scopeForSA(Builder $q, int $saId): Builder
    {
        return $q->where(fn ($q) => $q->where('super_agent_id', $saId));
    }
}
