/**
 * Centralized Lead Status Configuration
 * 
 * This file defines the human-readable labels and color-coded badges for all lead statuses.
 * It ensures consistency between the backend and all frontend screens (Admin, Super Agent, Agent).
 */

export const LEAD_STATUS_CONFIG: Record<string, { label: string; color: string; badgeClass: string }> = {
    'NEW': { 
        label: 'New Application', 
        color: 'bg-gray-100 text-gray-700',
        badgeClass: 'badge-new'
    },
    'ON_HOLD': { 
        label: 'On Hold', 
        color: 'bg-amber-50 text-amber-700',
        badgeClass: 'badge-on_hold'
    },
    'INVALID': { 
        label: 'Invalid Registration', 
        color: 'bg-rose-50 text-rose-700',
        badgeClass: 'badge-invalid'
    },
    'DUPLICATE': { 
        label: 'Duplicate Entry', 
        color: 'bg-slate-50 text-slate-700',
        badgeClass: 'badge-duplicate'
    },
    'REJECTED': { 
        label: 'Rejected', 
        color: 'bg-red-50 text-red-700',
        badgeClass: 'badge-rejected'
    },
    'REGISTERED': { 
        label: 'Registered at MNRE', 
        color: 'bg-indigo-50 text-indigo-700',
        badgeClass: 'badge-registered'
    },
    'SITE_SURVEY': { 
        label: 'Site Survey Done', 
        color: 'bg-purple-50 text-purple-700',
        badgeClass: 'badge-survey'
    },
    'AT_BANK': { 
        label: 'At Bank', 
        color: 'bg-violet-50 text-violet-700',
        badgeClass: 'badge-at_bank'
    },
    'COMPLETED': { 
        label: 'Successfully Completed', 
        color: 'bg-green-50 text-green-700',
        badgeClass: 'badge-completed'
    },
    'PROJECT_COMMISSIONING': { 
        label: 'Project Commissioning', 
        color: 'bg-emerald-50 text-emerald-700',
        badgeClass: 'badge-commissioning'
    },
    'SUBSIDY_REQUEST': { 
        label: 'Subsidy Request Filed', 
        color: 'bg-teal-50 text-teal-700',
        badgeClass: 'badge-subsidy-req'
    },
    'SUBSIDY_APPLIED': { 
        label: 'Subsidy Applied', 
        color: 'bg-cyan-50 text-cyan-700',
        badgeClass: 'badge-subsidy'
    },
    'SUBSIDY_DISBURSED': { 
        label: 'Subsidy Disbursed', 
        color: 'bg-green-50 text-green-700',
        badgeClass: 'badge-subsidy-paid'
    },
};

export const LEAD_STATUS_OPTIONS = Object.entries(LEAD_STATUS_CONFIG).map(([value, { label }]) => ({
    value,
    label
}));

export function getLeadStatusLabel(status: string): string {
    return LEAD_STATUS_CONFIG[status]?.label || status.replace(/_/g, ' ');
}

export function getLeadStatusColor(status: string): string {
    return LEAD_STATUS_CONFIG[status]?.color || 'bg-slate-100 text-slate-600';
}

export function getLeadStatusBadgeClass(status: string): string {
    return LEAD_STATUS_CONFIG[status]?.badgeClass || 'badge-new';
}
