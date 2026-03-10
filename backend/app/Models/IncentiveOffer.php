<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveOffer extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'offer_type', 'target_installs', 'reward_value', 'valid_from', 'valid_until', 'is_active', 'super_agent_id'];

    public function superAgent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'super_agent_id');
    }

    protected function casts(): array
    {
        return [
            'valid_from'   => 'date',
            'valid_until'  => 'date',
            'is_active'    => 'boolean',
            'reward_value' => 'decimal:2',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
