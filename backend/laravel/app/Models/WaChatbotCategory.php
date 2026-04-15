<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int         $id
 * @property string      $name
 * @property string|null $description
 * @property string|null $icon_emoji
 * @property int         $sort_order
 * @property bool        $is_active
 */
class WaChatbotCategory extends Model
{
    use HasFactory;

    protected $table = 'wa_chatbot_categories';

    protected $fillable = [
        'name',
        'description',
        'icon_emoji',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // ── Linked FAQs ──────────────────────────────────────────────────

    /**
     * Fetch linked FAQs from the existing faqs table.
     * The link is by matching name string — zero data duplication.
     * Admin manages Q&A via the existing AdminFAQPage.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, FAQ>
     */
    public function faqs(): \Illuminate\Database\Eloquent\Collection
    {
        return FAQ::where('category', $this->name)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Returns a numbered text representation for use in WhatsApp messages.
     * e.g. "1. 💰 Subsidy — Subsidy amounts & scheme details"
     */
    public function toMenuLine(int $number): string
    {
        $emoji = $this->icon_emoji ? "{$this->icon_emoji} " : '';
        $desc  = $this->description ? " — {$this->description}" : '';

        return "{$number}. {$emoji}{$this->name}{$desc}";
    }
}
