/**
 * statusTransitions.ts — F1
 *
 * Frontend mirror of the backend StatusTransitionService constants.
 * This is the single source of truth for role-based status availability
 * on the frontend. All status dropdowns should be derived from ROLE_STATUS_MAP
 * rather than hard-coded inline filters.
 *
 * Keep in sync with: backend/app/Services/StatusTransitionService.php
 */

import type { UserRole } from '@/types';
import type { LeadStatus } from '@/types';

// Full status set — admin/operator can reach any of these
export const ALL_STATUSES: LeadStatus[] = [
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

// Statuses the field_technical_team may set via geo-tagged visit
export const TECHNICAL_STATUSES: LeadStatus[] = ['SITE_SURVEY', 'COMPLETED'];

// Statuses that require a geotag (photo + GPS) when set by technical team
export const GEOTAG_REQUIRED_STATUSES: LeadStatus[] = ['SITE_SURVEY', 'COMPLETED'];

/**
 * Map of role → allowed status transitions.
 * Admin/operator get the full set (unrestricted override).
 * Technical team is limited to their 2 geo-tagged statuses.
 * All other roles have no direct status-change permission.
 */
export const ROLE_STATUS_MAP: Partial<Record<UserRole, LeadStatus[]>> = {
    admin:                ALL_STATUSES,
    super_admin:          ALL_STATUSES,
    operator:             ALL_STATUSES,
    field_technical_team: TECHNICAL_STATUSES,
    // super_agent, agent, enumerator: intentionally absent (no permission)
};

/**
 * Return the statuses available for the given role.
 * Falls back to an empty array if the role has no transition rights.
 */
export function getStatusesForRole(role: UserRole | null | undefined): LeadStatus[] {
    if (!role) return [];
    return ROLE_STATUS_MAP[role] ?? [];
}

/**
 * Return true when the given status requires a geotag photo.
 * Used to show/hide the geotag warning in the admin override panel.
 */
export function requiresGeotag(status: LeadStatus | string): boolean {
    return (GEOTAG_REQUIRED_STATUSES as string[]).includes(status);
}
