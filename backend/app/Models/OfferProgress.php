<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferProgress extends Model
{
    protected $table = 'offer_progress';

    protected $fillable = [
        'offer_id', 'user_id', 'role_context',
        'total_installations', 'redeemed_installations', 'redemption_count',
        'unredeemed_installations', 'can_redeem', 'pending_redemption_count',
        'first_installation_at', 'last_installation_at', 'last_redeemed_at',
        'offer_ended_zeroed_at',
    ];

    protected $casts = [
        'can_redeem'             => 'boolean',
        'first_installation_at'  => 'datetime',
        'last_installation_at'   => 'datetime',
        'last_redeemed_at'       => 'datetime',
        'total_installations'    => 'integer',
        'redeemed_installations' => 'integer',
        'redemption_count'       => 'integer',
        'unredeemed_installations'=> 'integer',
        'pending_redemption_count'=> 'integer',
        'offer_ended_zeroed_at' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absorbedPoints()
    {
        return $this->hasOne(SuperAgentAbsorbedPoints::class, 'source_agent_id', 'user_id')
                    ->where('offer_id', $this->offer_id);
    }

    /**
     * Recalculate all derived fields from raw counters.
     * Call this after any change to total_installations or redeemed_installations.
     */
    public function recalculate(): void
    {
        $target    = $this->offer->target_installations;
        
        // Ensure target is greater than 0 to avoid division by zero
        if ($target <= 0) return;

        $unredeemed = $this->total_installations - $this->redeemed_installations;

        $this->update([
            'unredeemed_installations' => $unredeemed,
            'can_redeem'               => $unredeemed >= $target,
            'pending_redemption_count' => (int) floor($unredeemed / $target),
        ]);
    }
}
