<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferExpiryLog extends Model
{
    public $timestamps = false; // We use processed_at

    protected $fillable = [
        'offer_id',
        'agents_processed',
        'agents_absorbed',
        'agents_grace_period_expired',
        'total_points_absorbed',
        'total_points_discarded',
        'agent_breakdown',
        'processed_at',
    ];

    protected $casts = [
        'agent_breakdown' => 'array',
        'processed_at'    => 'datetime',
        'agents_processed' => 'integer',
        'agents_absorbed' => 'integer',
        'agents_grace_period_expired' => 'integer',
        'total_points_absorbed' => 'integer',
        'total_points_discarded' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
