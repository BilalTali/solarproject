import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Search, Filter } from 'lucide-react';
import type { Lead } from '@/types';
import { VerificationStatusBadge } from '@/components/shared/VerificationStatusBadge';
import { LeadSourceBadge } from '@/components/shared/LeadSourceBadge';
import { VerifyLeadModal } from '@/components/super-agent/VerifyLeadModal';
import { RevertLeadModal } from '@/components/super-agent/RevertLeadModal';
import { leadsApi } from '@/api/leads.api';
import { superAgentCommissionsApi } from '@/api/commissions.api';
import CommissionInlineEntryForAgent from '@/components/super-agent/CommissionInlineEntryForAgent';
import React from 'react';
import { LEAD_STATUS_OPTIONS, getLeadStatusLabel, getLeadStatusColor } from '@/constants/leadStatuses';


type TabType = 'needs_verification' | 'my_leads' | 'team_leads' | 'all';

export default function SuperAgentLeadsPage() {
    const [tab, setTab] = useState<TabType>('all');
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [page, setPage] = useState(1);
    const [verifyLead, setVerifyLead] = useState<Lead | null>(null);
    const [revertLead, setRevertLead] = useState<Lead | null>(null);
    const [hiddenPrompts, setHiddenPrompts] = useState<Record<string, boolean>>({});

    const verificationParam: Record<TabType, Record<string, string | undefined>> = {
        needs_verification: { verification_status: 'pending_super_agent_verification' },
        my_leads: { scope: 'my_leads' },
        team_leads: { scope: 'team_leads' },
        all: {},
    };

    const { data, isLoading, refetch } = useQuery({
        queryKey: ['sa-leads', tab, search, status, page],
        queryFn: () => leadsApi.getSALeads({
            search: search || undefined,
            status: status || undefined,
            page,
            per_page: 20,
            ...verificationParam[tab],
        }),
        refetchInterval: tab === 'needs_verification' ? 30_000 : undefined,
    });

    const leads: Lead[] = data?.data?.data ?? [];
    const totalPages: number = data?.data?.last_page ?? 1;
    const pendingVerificationCount: number = (data?.data as any)?.total ?? (data?.data?.data?.filter(
        (l: Lead) => l.verification_status === 'pending_super_agent_verification'
    ).length ?? 0);

    const tabs: { key: TabType; label: string; badge?: number }[] = [
        { key: 'needs_verification', label: '⏳ Needs Verification', badge: pendingVerificationCount },
        { key: 'my_leads', label: 'My Leads' },
        { key: 'team_leads', label: 'Team Leads' },
        { key: 'all', label: 'All' },
    ];

    return (
        <div className="space-y-5">
            <div>
                <h1 className="text-xl font-bold text-slate-800">Leads</h1>
                <p className="text-sm text-slate-600 mt-0.5">Manage and verify leads in your pipeline</p>
            </div>

            {/* Tabs */}
            <div className="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit">
                {tabs.map(t => (
                    <button
                        key={t.key}
                        onClick={() => { setTab(t.key); setPage(1); }}
                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-all flex items-center gap-2 ${tab === t.key
                            ? 'bg-white text-slate-800 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700'
                            }`}
                    >
                        {t.label}
                        {t.badge !== undefined && t.badge > 0 && (
                            <span className="bg-amber-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full animate-pulse min-w-[20px] text-center">
                                {t.badge}
                            </span>
                        )}
                    </button>
                ))}
            </div>

            {/* Filters */}
            <div className="flex flex-wrap gap-3">
                <div className="relative">
                    <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Search name, mobile, lead ID..."
                        value={search}
                        onChange={(e) => { setSearch(e.target.value); setPage(1); }}
                        className="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 w-72"
                    />
                </div>
                {tab !== 'needs_verification' && (
                    <select
                        value={status}
                        onChange={(e) => { setStatus(e.target.value); setPage(1); }}
                        className="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">All Statuses</option>
                        {LEAD_STATUS_OPTIONS.map(opt => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                )}
                <div className="flex items-center gap-1 text-slate-600 text-xs">
                    <Filter size={14} />
                    {data?.data?.total ?? 0} leads
                </div>
            </div>

            {/* Lead Table */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div className="hidden lg:block overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 border-b border-slate-200">
                            <tr>
                                {[
                                    'Lead Ref', 'Beneficiary', 'Source', 'Verification',
                                    'Mobile', 'District', 'DISCOM', 'Consumer No.',
                                    'Business Development Manager', 'Business Development Executive / Enumerator', 'Status', 'Date', 'Action'
                                ].map(h => (
                                    <th key={h} scope="col" className="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide whitespace-nowrap">
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {isLoading ? (
                                <tr><td colSpan={12} className="text-center py-12 text-slate-400">Loading leads...</td></tr>
                            ) : leads.length === 0 ? (
                                <tr>
                                    <td colSpan={12} className="text-center py-12">
                                        {tab === 'needs_verification' ? (
                                            <div className="space-y-2">
                                                <p className="text-2xl">✅</p>
                                                <p className="text-slate-500 font-medium">No leads pending verification</p>
                                                <p className="text-slate-400 text-xs">All submissions are up to date</p>
                                            </div>
                                        ) : <p className="text-slate-400">No leads found.</p>}
                                    </td>
                                </tr>
                            ) : (
                                leads.map((lead) => {
                                    const isPending = lead.verification_status === 'pending_super_agent_verification';

                                    return (
                                        <React.Fragment key={lead.id}>
                                            <tr
                                                className={`hover:bg-slate-50 transition-colors ${isPending ? 'bg-amber-50/40' : ''}`}
                                            >
                                                <td className="px-4 py-3 font-mono text-xs text-slate-600">{lead.ulid?.slice(-8)}</td>
                                                <td className="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">{lead.beneficiary_name}</td>
                                                <td className="px-4 py-3">
                                                    <LeadSourceBadge source={lead.source} />
                                                </td>
                                                <td className="px-4 py-3">
                                                    <VerificationStatusBadge status={lead.verification_status} revertCount={lead.revert_count} />
                                                </td>
                                                <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_mobile}</td>
                                                <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_district}</td>
                                                <td className="px-4 py-3 text-slate-800 whitespace-nowrap">{lead.discom_name || '—'}</td>
                                                <td className="px-4 py-3 text-slate-600 font-mono whitespace-nowrap">{lead.consumer_number || '—'}</td>
                                                <td className="px-4 py-3 text-slate-600 whitespace-nowrap text-xs">
                                                    {lead.submitted_by_enumerator?.enumerator_creator_role === 'admin' ? (
                                                        <span className="text-emerald-700 font-semibold italic text-xs uppercase tracking-tighter">Direct Settlement</span>
                                                    ) : (
                                                        lead.assigned_super_agent?.name ?? <span className="text-slate-400 italic">Unassigned</span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-slate-600 whitespace-nowrap text-xs text-center">
                                                    {lead.submitted_by_enumerator ? (
                                                        <div className="flex flex-col leading-tight">
                                                            <span>{lead.submitted_by_enumerator.name}</span>
                                                            <span className="text-[9px] text-emerald-600 font-mono font-bold uppercase tracking-tighter">Enm: {lead.submitted_by_enumerator.enumerator_id}</span>
                                                        </div>
                                                    ) : (
                                                        lead.submitted_by_agent?.name ?? lead.assigned_agent?.name ?? <span className="text-slate-500 italic">Direct</span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 whitespace-nowrap">
                                                    <span className={`px-2 py-0.5 rounded text-xs font-medium ${getLeadStatusColor(lead.status)}`}>
                                                        {getLeadStatusLabel(lead.status)}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">
                                                    {new Date(lead.created_at).toLocaleDateString('en-IN')}
                                                </td>
                                                <td className="px-4 py-3 whitespace-nowrap">
                                                    {isPending ? (
                                                        <div className="flex items-center gap-2">
                                                            <button
                                                                onClick={() => setVerifyLead(lead)}
                                                                className="px-2 py-1 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors"
                                                            >
                                                                ✅ Verify
                                                            </button>
                                                            <button
                                                                onClick={() => setRevertLead(lead)}
                                                                className="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200 transition-colors"
                                                            >
                                                                ↩ Revert
                                                            </button>
                                                        </div>
                                                    ) : (
                                                        <Link
                                                            to={`/super-agent/leads/${lead.ulid}`}
                                                            className="text-orange-600 hover:text-orange-700 font-medium text-xs"
                                                        >
                                                            View →
                                                        </Link>
                                                    )}
                                                </td>
                                            </tr>
                                            {(() => {
                                                const prompts = lead.commission_status?.prompts || [];
                                                if (prompts.length === 0 || hiddenPrompts[lead.ulid]) return null;

                                                return prompts.map((p, idx) => {
                                                    const existing = lead.formatted_commissions?.all?.find(c => 
                                                        (c.payee_id === p.payee_id || c.payee_role === p.payee_role)
                                                    );

                                                    return (
                                                        <tr key={`${lead.id}-comm-${idx}`}>
                                                            <td colSpan={13} className="p-0 border-b border-slate-200 bg-orange-50/50">
                                                                <CommissionInlineEntryForAgent
                                                                    leadUlid={lead.ulid}
                                                                    leadStatus={lead.status}
                                                                    commissionPrompt={p}
                                                                    existingCommission={existing || null}
                                                                    agentName={p.payee_name || ''}
                                                                    agentCode={p.payee_code || ''}
                                                                    commissionsApi={superAgentCommissionsApi}
                                                                    onSaved={() => {
                                                                        refetch();
                                                                        // If it was the only prompt, hide it
                                                                        if (prompts.length === 1) {
                                                                            setHiddenPrompts(prev => ({ ...prev, [lead.ulid]: true }));
                                                                        }
                                                                    }}
                                                                    onSkip={() => {
                                                                        setHiddenPrompts(prev => ({ ...prev, [lead.ulid]: true }));
                                                                    }}
                                                                />
                                                            </td>
                                                        </tr>
                                                    );
                                                });
                                            })()}
                                        </React.Fragment>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Mobile Cards */}
                <div className="lg:hidden divide-y divide-slate-100">
                    {isLoading ? (
                        <div className="p-8 text-center text-slate-400">Loading leads...</div>
                    ) : leads.length === 0 ? (
                        <div className="p-8 text-center text-slate-400">No leads found.</div>
                    ) : (
                        leads.map((lead) => {
                            const isPending = lead.verification_status === 'pending_super_agent_verification';
                            return (
                                <div key={lead.id} className="p-4 space-y-3">
                                    <div className="flex justify-between items-start">
                                        <div>
                                            <Link to={`/super-agent/leads/${lead.ulid}`} className="font-bold text-slate-800 hover:text-orange-600 block">
                                                {lead.beneficiary_name}
                                            </Link>
                                            <p className="text-[10px] text-slate-500 font-mono">{lead.ulid?.slice(-8)}</p>
                                        </div>
                                        <div className="flex flex-col gap-1 items-end">
                                            <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${getLeadStatusColor(lead.status)}`}>
                                                {getLeadStatusLabel(lead.status)}
                                            </span>
                                            <VerificationStatusBadge status={lead.verification_status} revertCount={lead.revert_count} />
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-2 text-xs text-slate-600">
                                        <span>{lead.beneficiary_mobile}</span>
                                        <span className="text-right">{lead.beneficiary_district}</span>
                                    </div>
                                    {isPending && (
                                        <div className="flex gap-2 pt-1">
                                            <button onClick={() => setVerifyLead(lead)} className="flex-1 py-2 bg-green-600 text-white text-xs font-medium rounded-lg">✅ Verify</button>
                                            <button onClick={() => setRevertLead(lead)} className="flex-1 py-2 bg-red-100 text-red-700 text-xs font-medium rounded-lg">↩ Revert</button>
                                        </div>
                                    )}
                                    {(() => {
                                        const prompts = lead.commission_status?.prompts || [];
                                        if (prompts.length === 0 || hiddenPrompts[lead.ulid]) return null;

                                        return prompts.map((p, idx) => {
                                            const existing = lead.formatted_commissions?.all?.find(c => 
                                                (c.payee_id === p.payee_id || c.payee_role === p.payee_role)
                                            );

                                            return (
                                                <div key={`${lead.id}-comm-mob-${idx}`} className="bg-orange-50 p-3 rounded-lg border border-orange-100 mt-2">
                                                    <CommissionInlineEntryForAgent
                                                        leadUlid={lead.ulid}
                                                        leadStatus={lead.status}
                                                        commissionPrompt={p}
                                                        existingCommission={existing || null}
                                                        agentName={p.payee_name || ''}
                                                        agentCode={p.payee_code || ''}
                                                        commissionsApi={superAgentCommissionsApi}
                                                        onSaved={() => {
                                                            refetch();
                                                            if (prompts.length === 1) {
                                                                setHiddenPrompts(prev => ({ ...prev, [lead.ulid]: true }));
                                                            }
                                                        }}
                                                        onSkip={() => {
                                                            setHiddenPrompts(prev => ({ ...prev, [lead.ulid]: true }));
                                                        }}
                                                    />
                                                </div>
                                            );
                                        });
                                    })()}
                                </div>
                            );
                        })
                    )}
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                    <div className="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                        <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1} className="text-xs px-3 py-1.5 rounded border border-slate-200 disabled:opacity-40 hover:bg-slate-50">
                            ← Prev
                        </button>
                        <span className="text-xs text-slate-500">Page {page} of {totalPages}</span>
                        <button onClick={() => setPage(p => Math.min(totalPages, p + 1))} disabled={page === totalPages} className="text-xs px-3 py-1.5 rounded border border-slate-200 disabled:opacity-40 hover:bg-slate-50">
                            Next →
                        </button>
                    </div>
                )}
            </div>

            {/* Modals */}
            {verifyLead && (
                <VerifyLeadModal
                    lead={verifyLead}
                    isOpen={true}
                    onClose={() => setVerifyLead(null)}
                    onSuccess={() => { refetch(); setVerifyLead(null); }}
                />
            )}
            {revertLead && (
                <RevertLeadModal
                    lead={revertLead}
                    isOpen={true}
                    onClose={() => setRevertLead(null)}
                    onSuccess={() => { refetch(); setRevertLead(null); }}
                />
            )}
        </div>
    );
}
