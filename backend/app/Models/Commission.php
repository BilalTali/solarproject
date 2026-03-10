<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Commission extends Model
{
    use SoftDeletes;

    protected $table = 'commissions';

    protected $fillable = [
        'lead_id', 'payee_id', 'payee_role', 'amount',
        'entered_by', 'payment_status', 'paid_at', 'paid_by',
        'payment_method', 'payment_reference', 'payment_notes', 'locked_at',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'paid_at'  => 'datetime',
        'locked_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────
    public function scopeForSuperAgents(Builder $q): Builder
    {
        return $q->where('payee_role', 'super_agent');
    }

    public function scopeForAgents(Builder $q): Builder
    {
        return $q->where('payee_role', 'agent');
    }

    public function scopeUnpaid(Builder $q): Builder
    {
        return $q->where('payment_status', 'unpaid');
    }

    public function scopePaid(Builder $q): Builder
    {
        return $q->where('payment_status', 'paid');
    }

    public function scopeForPayee(Builder $q, int $userId): Builder
    {
        return $q->where('payee_id', $userId);
    }

    // ── Helper Methods ────────────────────────────────────────────────
    public function isLocked(): bool
    {
        // Commission is locked if it is already paid OR it was created more than 24 hours ago
        return $this->isPaid() || ($this->created_at && $this->created_at->addHours(24)->isPast());
    }

    public function isForSuperAgent(): bool
    {
        return $this->payee_role === 'super_agent';
    }

    public function isForAgent(): bool
    {
        return $this->payee_role === 'agent';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
