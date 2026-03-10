<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScanLog extends Model
{
    public $timestamps = false;
    const CREATED_AT = 'scanned_at';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
