<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSlab extends Model
{
    use HasFactory;

    protected $fillable = ['capacity', 'label', 'agent_commission', 'super_agent_override', 'description', 'is_active', 'super_agent_id'];

    public function superAgent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'super_agent_id');
    }

    protected function casts(): array
    {
        return [
            'agent_commission'    => 'decimal:2',
            'super_agent_override'=> 'decimal:2',
            'reward_value'        => 'decimal:2',
            'is_active'           => 'boolean',
        ];
    }

    // Ordered by capacity
    public static function ordered(): \Illuminate\Database\Eloquent\Collection
    {
        $order = ['1kw', '2kw', '3kw', 'above_3kw'];
        return static::all()->sortBy(fn ($s) => array_search($s->capacity, $order))->values();
    }
}
