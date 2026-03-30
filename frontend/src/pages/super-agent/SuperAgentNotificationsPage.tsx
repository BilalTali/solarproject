// @ts-nocheck
import { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
    ArrowLeft, User, Phone, MapPin, Hash, Building2, FileText,
    Calendar, AlertCircle, FileDigit, Download, Image as ImageIcon,
    Camera, Mail, Shield, BadgeCheck, Save
} from 'lucide-react';
import { superAgentApi } from '@/api/superAgent.api';
import { superAgentCommissionsApi } from '@/api/commissions.api';
import { openAuthenticatedFile } from '@/utils/documentUtils';
import { authApi } from '@/api/auth.api';
import { useAuthStore } from '@/store/authStore';
import toast from 'react-hot-toast';
import type { Lead, CommissionPrompt } from '@/types';
import CommissionInlineEntryForAgent from '@/components/super-agent/CommissionInlineEntryForAgent';
import { STATE_DISTRICTS, INDIAN_STATES } from '@/constants/locationData';
import ChangePasswordForm from '@/components/shared/ChangePasswordForm';

const STATUS_BADGE: Record<string, string> = {
    new: 'bg-blue-100 text-blue-700',
    contacted: 'bg-purple-100 text-purple-700',
    documents_collected: 'bg-indigo-100 text-indigo-700',
    registered: 'bg-cyan-100 text-cyan-700',
    site_survey: 'bg-teal-100 text-teal-700',
    installation_pending: 'bg-orange-100 text-orange-700',
    installed: 'bg-green-100 text-green-700',
    subsidy_applied: 'bg-lime-100 text-lime-700',
    completed: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
    on_hold: 'bg-yellow-100 text-yellow-700',
};



function label(status: string) { return status.replace(/_/g, ' '); }
function fmt(iso: string) { return new Date(iso).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); }

export function SuperAgentNotificationsPage() {
    return (
        <div className="py-8 px-4">
            <h1 className="text-xl font-bold text-slate-800 mb-4">Notifications</h1>
            <div className="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">
                Your notifications will appear here.
            </div>
        </div>
    );
}

