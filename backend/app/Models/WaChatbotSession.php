<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Tracks the conversation state for each WhatsApp user.
 *
 * @property int         $id
 * @property string      $wa_phone          E.164 format e.g. "919876543210"
 * @property string      $state             menu|category|register|done
 * @property array|null  $context           JSON state data (see shape below)
 * @property \Carbon\Carbon|null $last_message_at
 *
 * Context shape:
 * {
 *   "selected_category": "Subsidy",        // When state = category
 *   "reg_step": 3,                          // Current step index in registration
 *   "reg_data": {                           // Accumulated registration data
 *     "name": "Ramesh Kumar",
 *     "mobile": "9876543210",
 *     "state": "Uttar Pradesh",
 *     "district": "Lucknow",
 *     "area": "Aliganj",
 *     "elec_bill_media_id": "Meta_media_id_string",
 *     "aadhaar_media_id": "Meta_media_id_string",
 *     "referral_code": "SM-2026-1042"
 *   }
 * }
 */
class WaChatbotSession extends Model
{
    use HasFactory;

    protected $table = 'wa_chatbot_sessions';

    protected $fillable = [
        'wa_phone',
        'state',
        'context',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'context'         => 'array',
            'last_message_at' => 'datetime',
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Check if this session has been idle for more than the given minutes.
     * Used to auto-reset to main menu.
     */
    public function isIdle(int $minutes = 30): bool
    {
        if (! $this->last_message_at) {
            return true;
        }

        return $this->last_message_at->diffInMinutes(now()) >= $minutes;
    }

    /**
     * Reset session to main menu (clears context).
     */
    public function resetToMenu(): void
    {
        $this->update([
            'state'           => 'menu',
            'context'         => null,
            'last_message_at' => now(),
        ]);
    }

    /**
     * Merge new data into the reg_data portion of context.
     */
    public function mergeRegData(array $data): void
    {
        $context = $this->context ?? [];
        $context['reg_data'] = array_merge($context['reg_data'] ?? [], $data);
        $this->update(['context' => $context]);
    }

    /**
     * Get a single field from reg_data or return default.
     */
    public function getRegField(string $key, mixed $default = null): mixed
    {
        return $this->context['reg_data'][$key] ?? $default;
    }

    /**
     * Get the current registration step (1-indexed).
     */
    public function getRegStep(): int
    {
        return $this->context['reg_step'] ?? 1;
    }

    /**
     * Advance to the next registration step.
     */
    public function advanceRegStep(): void
    {
        $context = $this->context ?? [];
        $context['reg_step'] = ($context['reg_step'] ?? 1) + 1;
        $this->update(['context' => $context, 'last_message_at' => now()]);
    }

    /**
     * Find or create a session for the given phone number.
     */
    public static function findOrCreateForPhone(string $phone): static
    {
        return static::firstOrCreate(
            ['wa_phone' => $phone],
            [
                'state'           => 'menu',
                'context'         => null,
                'last_message_at' => now(),
            ]
        );
    }
}
