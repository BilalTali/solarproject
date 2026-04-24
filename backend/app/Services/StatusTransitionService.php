<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;

/**
 * StatusTransitionService
 *
 * Single source of truth for which statuses each role may transition a lead to.
 * All status-change endpoints (admin, technical) MUST call canTransition() before
 * persisting a status change. Admin always passes (no restrictions by design).
 *
 * Status strings match the UPPERCASE convention used throughout the application.
 */
class StatusTransitionService
{
    // ── Complete status set — admin can reach any of these ─────────────
    public const ALL_STATUSES = [
        'NEW',
        'ON_HOLD',
        'INVALID',
        'DUPLICATE',
        'REJECTED',
        'REGISTERED',
        'SITE_SURVEY',
        'AT_BANK',
        'COMPLETED',
        'PROJECT_COMMISSIONING',
        'SUBSIDY_REQUEST',
        'SUBSIDY_APPLIED',
        'SUBSIDY_DISBURSED',
    ];

    // ── Statuses the field_technical_team may set via geo-tagged visit ──
    public const TECHNICAL_STATUSES = [
        'SITE_SURVEY',
        'COMPLETED',
    ];

    // ── Statuses that REQUIRE a geotag (photo + GPS) when set by tech ─
    public const GEOTAG_REQUIRED_STATUSES = [
        'SITE_SURVEY',
        'COMPLETED',
    ];

    /**
     * Return the list of statuses the given user is allowed to transition
     * the lead to. Admin always gets the full list (no restrictions).
     *
     * @return string[]
     */
    public function getAllowedStatuses(User $user, Lead $lead): array
    {
        // Admin / Super Admin — unrestricted
        if ($user->isAdmin()) {
            return self::ALL_STATUSES;
        }

        // Operator — same as admin for status changes
        if ($user->isOperator()) {
            return self::ALL_STATUSES;
        }

        // Field Technical Team — limited to geo-tagged statuses
        if ($user->isFieldTechnician()) {
            return self::TECHNICAL_STATUSES;
        }

        // Super Agent / Agent / Enumerator — no direct status change permission
        return [];
    }

    /**
     * Check whether a user can transition a specific lead to $newStatus.
     * Returns true for admin regardless of current status (override by design).
     */
    public function canTransition(User $user, Lead $lead, string $newStatus): bool
    {
        return in_array($newStatus, $this->getAllowedStatuses($user, $lead), true);
    }

    /**
     * Return true when the given status transition REQUIRES a geotag photo
     * to be submitted alongside the status change.
     */
    public function requiresGeotag(string $newStatus): bool
    {
        return in_array($newStatus, self::GEOTAG_REQUIRED_STATUSES, true);
    }

    /**
     * Return the human-readable label for a status string.
     * Keeps backend consistent with the frontend leadStatuses constant.
     */
    public function getLabel(string $status): string
    {
        return match ($status) {
            'NEW'                    => 'New Application',
            'ON_HOLD'                => 'On Hold',
            'INVALID'                => 'Invalid Registration',
            'DUPLICATE'              => 'Duplicate Entry',
            'REJECTED'               => 'Rejected',
            'REGISTERED'             => 'Registered at MNRE',
            'SITE_SURVEY'            => 'Site Survey Done',
            'AT_BANK'                => 'At Bank',
            'COMPLETED'              => 'Successfully Completed',
            'PROJECT_COMMISSIONING'  => 'Project Commissioning',
            'SUBSIDY_REQUEST'        => 'Subsidy Request Filed',
            'SUBSIDY_APPLIED'        => 'Subsidy Applied',
            'SUBSIDY_DISBURSED'      => 'Subsidy Disbursed',
            default                  => str_replace('_', ' ', $status),
        };
    }
}
