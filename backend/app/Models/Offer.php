<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'prize_label', 'prize_amount', 'prize_image_path',
        'offer_from', 'offer_to', 'target_installations', 'offer_type',
        'visible_to', 'status', 'current_installations',
        'collective_redeemed', 'collective_redeemed_at',
        'display_order', 'is_featured', 'created_by',
        'grace_period_days', 'is_annual', 'absorption_processed_at',
    ];

    protected $casts = [
        'offer_from'            => 'date',
        'offer_to'              => 'date',
        'prize_amount'          => 'decimal:2',
        'is_featured'           => 'boolean',
        'collective_redeemed'   => 'boolean',
        'collective_redeemed_at'=> 'datetime',
        'current_installations' => 'integer',
        'target_installations'  => 'integer',
        'is_annual'               => 'boolean',
        'absorption_processed_at' => 'datetime',
        'grace_period_days'       => 'integer',
    ];

    public function progress(): HasMany
    {
        return $this->hasMany(OfferProgress::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class);
    }

    public function installationLogs(): HasMany
    {
        return $this->hasMany(OfferInstallationLog::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function absorbedPoints(): HasMany
    {
        return $this->hasMany(SuperAgentAbsorbedPoints::class);
    }

    public function expiryLogs(): HasMany
    {
        return $this->hasMany(OfferExpiryLog::class);
    }

    // Scopes
    public function scopeLive(Builder $q): Builder
    {
        return $q->where('status', 'active')
                 ->whereDate('offer_from', '<=', today())
                 ->whereDate('offer_to',   '>=', today());
    }

    public function scopeVisibleTo(Builder $q, string $role): Builder
    {
        // 'agent' role sees: visible_to IN ('agents', 'both')
        // 'super_agent' role sees: visible_to IN ('super_agents', 'both')
        // These are SEPARATE — super agents do NOT see 'agents'-only offers
        $allowed = match ($role) {
            'agent'       => ['agents', 'both'],
            'super_agent' => ['super_agents', 'both'],
            'admin'       => ['agents', 'super_agents', 'both'],
            default       => [],
        };
        return $q->whereIn('visible_to', $allowed);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->whereDate('offer_from', '>', today());
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->whereDate('offer_to', '<', today());
    }

    // Accessors
    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'active'
            && today()->between($this->offer_from, $this->offer_to);
    }

    public function getDaysRemainingAttribute(): int
    {
        return $this->getIsLiveAttribute() ? max(0, (int) today()->diffInDays($this->offer_to, false)) : 0;
    }

    public function getPrizeImageUrlAttribute(): ?string
    {
        return $this->prize_image_path ? asset('storage/' . $this->prize_image_path) : null;
    }

    // For collective: installations left until team prize
    public function getCollectiveRemainingAttribute(): int
    {
        return max(0, $this->target_installations - $this->current_installations);
    }

    public function getGracePeriodEndsAtAttribute(): ?\Carbon\Carbon
    {
        return $this->offer_to?->copy()->addDays($this->grace_period_days);
    }

    public function getIsClaimableAttribute(): bool
    {
        return $this->status === 'active'
            && now()->lte($this->grace_period_ends_at ?? $this->offer_to);
    }

    // Annual detection — auto-flag if duration >= 300 days:
    protected static function booted(): void
    {
        static::saving(function (Offer $offer) {
            if ($offer->offer_from && $offer->offer_to) {
                // For Eloquent models, offer_from/to are likely still Carbon objects if they are being saved
                // but diffInDays on Carbon should work.
                $days = $offer->offer_from->diffInDays($offer->offer_to);
                $offer->is_annual = $days >= 300;
            }
        });
    }

    protected $appends = [
        'is_live', 'days_remaining', 'prize_image_url',
        'collective_remaining', 'grace_period_ends_at', 'is_claimable'
    ];
}
