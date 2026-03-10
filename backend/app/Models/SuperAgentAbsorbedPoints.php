<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

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
        'absorbed_at'  => 'datetime',
        'claimed_at'   => 'datetime',
        'delivered_at' => 'datetime',
        'absorbed_installations' => 'integer',
        'agent_total_installations' => 'integer',
        'offer_target' => 'integer',
    ];

    public function superAgent(): BelongsTo   { return $this->belongsTo(User::class, 'super_agent_id'); }
    public function sourceAgent(): BelongsTo  { return $this->belongsTo(User::class, 'source_agent_id'); }
    public function offer(): BelongsTo        { return $this->belongsTo(Offer::class); }
    public function claimedBy(): BelongsTo    { return $this->belongsTo(User::class, 'claimed_by'); }
    public function approvedBy(): BelongsTo   { return $this->belongsTo(User::class, 'approved_by'); }

    // Scopes
    public function scopeUnclaimed(Builder $q): Builder { return $q->where('status', 'unclaimed'); }
    public function scopeClaimed(Builder $q): Builder   { return $q->where('status', 'claimed'); }
    public function scopeForSA(Builder $q, int $saId): Builder
    {
        return $q->where('super_agent_id', $saId);
    }
}
