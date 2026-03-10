<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferRedemption extends Model
{
    protected $fillable = [
        'offer_id', 'user_id', 'redemption_number', 'installations_used',
        'status', 'notes', 'approved_by', 'approved_at', 'delivered_at', 'claimed_at',
    ];

    protected $casts = [
        'approved_at'       => 'datetime',
        'delivered_at'      => 'datetime',
        'claimed_at'        => 'datetime',
        'redemption_number' => 'integer',
        'installations_used'=> 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
