import React from 'react';
import type { LeadOwnerType } from '@/types';

const config: Record<LeadOwnerType, { label: string; bgClass: string; textClass: string; dotClass: string }> = {
    admin_pool: {
        label: 'Admin Pool',
        bgClass: 'bg-amber-50',
        textClass: 'text-amber-800',
        dotClass: 'bg-amber-400',
    },
    super_agent_pool: {
        label: 'SA Pool',
        bgClass: 'bg-purple-50',
        textClass: 'text-purple-800',
        dotClass: 'bg-purple-400',
    },
    agent_pool: {
        label: 'Agent Pool',
        bgClass: 'bg-teal-50',
        textClass: 'text-teal-800',
        dotClass: 'bg-teal-400',
    },
};

interface Props {
    ownerType: LeadOwnerType;
    className?: string;
}

export const LeadOwnerBadge: React.FC<Props> = ({ ownerType, className = '' }) => {
    const cfg = config[ownerType];
    if (!cfg) return null;

    return (
        <span className={`inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium ${cfg.bgClass} ${cfg.textClass} ${className}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${cfg.dotClass}`} />
            {cfg.label}
        </span>
    );
};
