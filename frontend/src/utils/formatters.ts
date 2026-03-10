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
    new: 'New',
    registered: 'Registered',
    installed: 'Installed',
    completed: 'Completed',
    rejected: 'Rejected',
    on_hold: 'On Hold',
};

export const STATUS_COLORS: Record<LeadStatus, string> = {
    new: 'badge-new',
    registered: 'badge-registered',
    installed: 'badge-installed',
    completed: 'badge-completed',
    rejected: 'badge-rejected',
    on_hold: 'badge-on_hold',
};

export const PIPELINE_STAGES: LeadStatus[] = [
    'new', 'registered', 'installed', 'completed'
];
