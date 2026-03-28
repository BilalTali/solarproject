import { format } from 'date-fns';
import type { LeadStatus } from '@/types';

export const formatCurrency = (amount: number | null | undefined): string => {
    if (amount == null) return '—';
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(amount);
};

export const formatDate = (dateStr: string | null | undefined): string => {
    if (!dateStr) return '—';
    return format(new Date(dateStr), 'dd MMM yyyy');
};

export const formatDateTime = (dateStr: string | null | undefined): string => {
    if (!dateStr) return '—';
    return format(new Date(dateStr), 'dd MMM yyyy, hh:mm a');
};

export const STATUS_LABELS: Record<LeadStatus, string> = {
    NEW: 'New Application',
    CONTACTED: 'Contacted',
    DOCUMENTS_COLLECTED: 'Documents Collected',
    REGISTERED: 'Registered at MNRE',
    SITE_SURVEY: 'Site Survey Done',
    INSTALLATION_PENDING: 'Installation Pending',
    INSTALLED: 'Solar Installed',
    PROJECT_COMMISSIONING: 'Project Commissioning',
    SUBSIDY_REQUEST: 'Subsidy Request Filed',
    SUBSIDY_APPLIED: 'Subsidy Applied',
    SUBSIDY_DISBURSED: 'Subsidy Disbursed',
    COMPLETED: 'Successfully Completed',
    REJECTED: 'Rejected',
    ON_HOLD: 'On Hold',
    AT_BANK: 'At Bank',
    INVALID: 'Invalid Registration',
    DUPLICATE: 'Duplicate Entry',
};

export const STATUS_COLORS: Record<LeadStatus, string> = {
    NEW: 'badge-new',
    CONTACTED: 'badge-contacted',
    DOCUMENTS_COLLECTED: 'badge-docs',
    REGISTERED: 'badge-registered',
    SITE_SURVEY: 'badge-survey',
    INSTALLATION_PENDING: 'badge-pending',
    INSTALLED: 'badge-installed',
    PROJECT_COMMISSIONING: 'badge-commissioning',
    SUBSIDY_REQUEST: 'badge-subsidy-req',
    SUBSIDY_APPLIED: 'badge-subsidy',
    SUBSIDY_DISBURSED: 'badge-subsidy-paid',
    COMPLETED: 'badge-completed',
    REJECTED: 'badge-rejected',
    ON_HOLD: 'badge-on_hold',
    AT_BANK: 'badge-at_bank',
    INVALID: 'badge-invalid',
    DUPLICATE: 'badge-duplicate',
};

export const PIPELINE_STAGES: LeadStatus[] = [
    'NEW', 'REGISTERED', 'INSTALLED', 'PROJECT_COMMISSIONING', 'SUBSIDY_REQUEST', 'SUBSIDY_APPLIED', 'SUBSIDY_DISBURSED', 'COMPLETED'
];
