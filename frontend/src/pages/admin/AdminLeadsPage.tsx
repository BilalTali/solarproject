import React, { useState, useEffect, useRef } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
    Search, Filter, ChevronLeft, ChevronRight, X,
    Phone, MapPin, User, Hash, FileText, AlertCircle, Calendar,
    Image as ImageIcon, CreditCard, FileBadge, Download
} from 'lucide-react';
import { openAuthenticatedFile } from '@/utils/documentUtils';
import { leadsApi } from '@/api/leads.api';
import { adminSuperAgentApi } from '@/api/adminSuperAgent.api';
import toast from 'react-hot-toast';
import type { Lead, ApiResponse, PaginatedResponse, CommissionPrompt } from '@/types';
import CommissionInlineEntry from '@/components/admin/CommissionInlineEntry';

// ── constants ─────────────────────────────────────────────────────────────────
const CAPACITY_LABEL: Record<string, string> = {
    '1kw': '1 kW', '2kw': '2 kW', '3kw': '3 kW', '3.3kw': '3.3 kW', '4kw': '4 kW', '5kw': '5 kW', '5.5kw': '5.5 kW', '6kw': '6 kW', '7kw': '7 kW', '8kw': '8 kW', '9kw': '9 kW', '10kw': '10 kW', 'above_10kw': 'Above 10 kW', 'above_3kw': 'Above 3 kW',
};

const STATUS_BADGE: Record<string, string> = {
    new: 'bg-blue-100 text-blue-700',
    registered: 'bg-cyan-100 text-cyan-700',
    installed: 'bg-green-100 text-green-700',
    completed: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
    on_hold: 'bg-yellow-100 text-yellow-700',
};

const ALL_STATUSES = Object.keys(STATUS_BADGE);

function label(status: string) { return status.replace(/_/g, ' '); }
function fmt(iso: string) { return new Date(iso).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); }

// ── component ─────────────────────────────────────────────────────────────────
export default function AdminLeadsPage() {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [source, setSource] = useState('');
    const [page, setPage] = useState(1);
    const [detail, setDetail] = useState<Lead | null>(null);
    // for status-update panel
    const [newStatus, setNewStatus] = useState('');
    const [statusNote, setStatusNote] = useState('');
    // for assign panel
    const [assignSuperAgent, setAssignSuperAgent] = useState<number | string>('');
    const [activePrompts, setActivePrompts] = useState<Record<string, CommissionPrompt>>({});
    // commission prompt shown inside sidebar after status change
    const [sidebarCommissionPrompt, setSidebarCommissionPrompt] = useState<CommissionPrompt | null>(null);

    const closeButtonRef = useRef<HTMLButtonElement>(null);

    // Focus management for detail panel
    useEffect(() => {
        if (detail) {
            // small delay to ensure DOM is ready
            const timer = setTimeout(() => {
                closeButtonRef.current?.focus();
            }, 100);

            const handleEsc = (e: KeyboardEvent) => {
                if (e.key === 'Escape') setDetail(null);
            };
            window.addEventListener('keydown', handleEsc);
            return () => {
                window.removeEventListener('keydown', handleEsc);
                clearTimeout(timer);
            };
        }
    }, [detail]);

    const qc = useQueryClient();

    // ── list query ────────────────────────────────────────────────────────────
    const { data, isLoading } = useQuery<ApiResponse<PaginatedResponse<Lead>>>({
        queryKey: ['admin-leads', search, status, source, page],
        queryFn: () => leadsApi.getAdminLeads({
            search: search || undefined,
            status: status || undefined,
            source: source || undefined,
            page,
            per_page: 20,
        }),
    });



    const leads: Lead[] = data?.data?.data ?? [];
    const total = data?.data?.total ?? 0;
    const lastPage = data?.data?.last_page ?? 1;

    // ── detail query (loads full lead with status logs) ────────────────────────
    const { data: detailData, isLoading: detailLoading } = useQuery<ApiResponse<Lead>>({
        queryKey: ['admin-lead-detail', detail?.ulid],
        queryFn: () => leadsApi.getAdminLeadByUlid(detail!.ulid),
        enabled: !!detail?.ulid,
    });
    const fullLead = detailData?.data ?? detail;

    // ── active super agents (for assign dropdown) ────────────────────────────
    const { data: superAgentsData } = useQuery({
        queryKey: ['admin-super-agents-for-assign'],
        queryFn: () => adminSuperAgentApi.getSuperAgents({ status: 'active', per_page: 200 }),
        staleTime: 60_000,
    });
    const activeSuperAgents = (superAgentsData?.data?.data ?? []) as { id: number; name: string; super_agent_code: string | null }[];

    // ── mutations ─────────────────────────────────────────────────────────────
    const statusMut = useMutation({
        mutationFn: ({ ulid, status, notes }: { ulid: string; status: string; notes?: string }) =>
            leadsApi.updateLeadStatus(ulid, { status, notes }),
        onSuccess: (res: any) => {
            const returnedData = res?.data;
            if (returnedData?.commission_prompt?.should_prompt) {
                // Show commission prompt INSIDE the sidebar (don't close it)
                setSidebarCommissionPrompt(returnedData.commission_prompt);
                // also track in activePrompts for the table row (retained for backwards compat)
                setActivePrompts(p => ({ ...p, [detail?.ulid || '']: returnedData.commission_prompt }));
            } else {
                setSidebarCommissionPrompt(null);
                setActivePrompts(p => {
                    const newPrompts = { ...p };
                    delete newPrompts[detail?.ulid || ''];
                    return newPrompts;
                });
            }
            qc.invalidateQueries({ queryKey: ['admin-leads'] });
            qc.invalidateQueries({ queryKey: ['admin-lead-detail'] });
            toast.success('Lead status updated');
            setNewStatus(''); setStatusNote('');
        },
        onError: () => toast.error('Failed to update status.'),
    });

    const assignMut = useMutation({
        mutationFn: ({ ulid, super_agent_id }: { ulid: string; super_agent_id: number }) =>
            leadsApi.assignLead(ulid, super_agent_id),
        onSuccess: () => {
            qc.invalidateQueries({ queryKey: ['admin-leads'] });
            qc.invalidateQueries({ queryKey: ['admin-lead-detail'] });
            toast.success('Lead assigned to Business Development Manager successfully');
            setAssignSuperAgent('');
        },
        onError: () => toast.error('Failed to assign lead.'),
    });

    const updateLeadMut = useMutation({
        mutationFn: ({ ulid, data }: { ulid: string; data: Record<string, any> }) =>
            leadsApi.updateAdminLead(ulid, data),
        onSuccess: () => {
            qc.invalidateQueries({ queryKey: ['admin-leads'] });
            qc.invalidateQueries({ queryKey: ['admin-lead-detail'] });
            toast.success('Lead updated');
        },
        onError: () => toast.error('Failed to update lead.'),
    });

    const openDetail = (lead: Lead) => {
        setDetail(lead);
        setNewStatus(lead.status);
        setStatusNote('');
        setAssignSuperAgent('');
        setSidebarCommissionPrompt(null); // clear previous prompt
    };

    // ─────────────────────────────────────────────────────────────────────────
    return (
        <div className="space-y-5">
            {/* Header */}
            <div>
                <h1 className="text-xl font-bold text-slate-800">All Leads</h1>
                <p className="text-sm text-slate-600 mt-0.5">
                    View &amp; manage all leads across the platform
                    {total > 0 && <span className="ml-1 text-slate-500">({total} total)</span>}
                </p>
            </div>

            {/* Filters */}
            <div className="flex flex-wrap gap-3">
                <div className="relative">
                    <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Beneficiary name, mobile, lead ref…"
                        value={search}
                        onChange={e => { setSearch(e.target.value); setPage(1); }}
                        aria-label="Search leads"
                        className="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 w-80"
                    />
                </div>
                <select
                    value={status}
                    onChange={e => { setStatus(e.target.value); setPage(1); }}
                    aria-label="Filter by status"
                    className="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                    <option value="">All Statuses</option>
                    {ALL_STATUSES.map(s => (
                        <option key={s} value={s}>{label(s)}</option>
                    ))}
                </select>
                <select
                    value={source}
                    onChange={e => { setSource(e.target.value); setPage(1); }}
                    aria-label="Filter by source"
                    className="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                    <option value="">All Sources</option>
                    <option value="public_form">Public Form</option>
                    <option value="agent_submission">Business Development Executive Submission</option>
                </select>
                {(search || status || source) && (
                    <button
                        onClick={() => { setSearch(''); setStatus(''); setSource(''); setPage(1); }}
                        className="flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700 border border-slate-200 rounded-lg px-3 py-2"
                    >
                        <Filter size={13} /> Clear filters
                    </button>
                )}
            </div>

            {/* Table */}
            {/* Table / Card View */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                {/* Desktop Table View */}
                <div className="hidden lg:block overflow-x-auto">
                    <table className="min-w-full text-sm" aria-label="Leads list">
                        <thead className="bg-slate-50 border-b border-slate-200">
                            <tr>
                                {[
                                    'Ref', 'Beneficiary', 'Mobile', 'State', 'District', 'Address',
                                    'DISCOM', 'Consumer No.', 'Capacity', 'Roof Size', 'Monthly Bill',
                                    'Aadhaar', 'Electricity Bill', 'PAN', 'Photo', 'Solar Roof', 'Passbook',
                                    'Source', 'Business Development Manager', 'Business Development Executive', 'Status', 'Date', 'Action'
                                ].map(h => (
                                    <th key={h} scope="col" className="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide whitespace-nowrap">{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {isLoading ? (
                                <tr><td colSpan={23} className="text-center py-12 text-slate-400">Loading leads…</td></tr>
                            ) : leads.length === 0 ? (
                                <tr>
                                    <td colSpan={23} className="text-center py-12">
                                        <FileText size={32} className="mx-auto text-slate-300 mb-2" />
                                        <p className="text-slate-400">No leads found{search || status || source ? ' matching your filters' : ''}.</p>
                                    </td>
                                </tr>
                            ) : leads.map(lead => (
                                <React.Fragment key={lead.id}>
                                    <tr
                                        role="button"
                                        tabIndex={0}
                                        aria-label={`View details for ${lead.beneficiary_name}`}
                                        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDetail(lead); } }}
                                        className="hover:bg-slate-50 transition-colors cursor-pointer"
                                        onClick={() => openDetail(lead)}
                                    >
                                        {/* Ref */}
                                        <td className="px-4 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">
                                            {lead.ulid?.slice(-10)}
                                        </td>
                                        {/* Beneficiary */}
                                        <td className="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">{lead.beneficiary_name}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_mobile}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_state}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_district}</td>
                                        <td className="px-4 py-3 text-slate-600 max-w-[200px] truncate" title={lead.beneficiary_address ?? ''}>{lead.beneficiary_address ?? '—'}</td>

                                        <td className="px-4 py-3 text-slate-800 whitespace-nowrap">{lead.discom_name || '—'}</td>
                                        <td className="px-4 py-3 text-slate-600 font-mono whitespace-nowrap">{lead.consumer_number || '—'}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.system_capacity?.replace(/_/g, ' ') || '—'}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.roof_size?.replace(/_/g, ' ') || '—'}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.monthly_bill_amount ? `₹${lead.monthly_bill_amount}` : '—'}</td>

                                        {/* Documents */}
                                        {['aadhaar', 'electricity_bill', 'other', 'photo', 'solar_roof_photo', 'bank_passbook'].map(docType => {
                                            const doc = lead.documents?.find(d => d.document_type === docType);
                                            return (
                                                <td key={docType} className="px-4 py-3 text-center">
                                                    {doc ? (
                                                        <a
                                                            href={import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '') + '/storage/' + doc.file_path}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-orange-600 hover:text-orange-700 inline-flex"
                                                            title="View Document"
                                                            aria-label={`View ${docType} for ${lead.beneficiary_name}`}
                                                            onClick={e => e.stopPropagation()}
                                                        >
                                                            <FileText size={16} aria-hidden="true" />
                                                        </a>
                                                    ) : (
                                                        <span className="text-slate-400">—</span>
                                                    )}
                                                </td>
                                            );
                                        })}
                                        {/* Source */}
                                        <td className="px-4 py-3">
                                            <span className={`px-2 py-0.5 rounded text-xs font-medium ${lead.source === 'public_form' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700'}`}>
                                                {lead.source === 'public_form' ? 'Public' : 'Executive'}
                                            </span>
                                        </td>
                                        {/* Business Development Manager */}
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                                            {lead.assigned_super_agent?.name ?? <span className="text-slate-400 italic">Unassigned</span>}
                                        </td>
                                        {/* Business Development Executive */}
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                                            {lead.submitted_by_agent?.name ?? lead.assigned_agent?.name ?? <span className="text-slate-500 italic">Direct</span>}
                                        </td>
                                        {/* Status */}
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            <span className={`px-2 py-0.5 rounded text-xs font-medium capitalize ${STATUS_BADGE[lead.status] ?? 'bg-slate-100 text-slate-600'}`}>
                                                {label(lead.status)}
                                            </span>
                                        </td>
                                        {/* Date */}
                                        <td className="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">
                                            {fmt(lead.created_at)}
                                        </td>
                                        {/* Action */}
                                        <td className="px-4 py-3" onClick={e => e.stopPropagation()}>
                                            <button
                                                onClick={() => openDetail(lead)}
                                                className="text-xs text-orange-600 hover:text-orange-700 font-semibold whitespace-nowrap"
                                                aria-label={`View details for ${lead.beneficiary_name}`}
                                            >
                                                View →
                                            </button>
                                        </td>
                                    </tr>
                                    {activePrompts[lead.ulid] && (
                                        <tr key={`${lead.id}-comm`}>
                                            <td colSpan={23} className="p-0 border-b border-slate-200 bg-orange-50/50">
                                                <CommissionInlineEntry
                                                    leadUlid={lead.ulid}
                                                    leadStatus={lead.status}
                                                    commissionPrompt={activePrompts[lead.ulid]}
                                                    existingCommission={lead.formatted_commissions?.super_agent_commission || lead.formatted_commissions?.agent_commission || null}
                                                    onSaved={() => {
                                                        setActivePrompts(p => { const o = { ...p }; delete o[lead.ulid]; return o; });
                                                    }}
                                                    onSkip={() => {
                                                        setActivePrompts(p => { const o = { ...p }; delete o[lead.ulid]; return o; });
                                                    }}
                                                />
                                            </td>
                                        </tr>
                                    )}
                                </React.Fragment>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Mobile Card View */}
                <div className="lg:hidden divide-y divide-slate-100">
                    {isLoading ? (
                        <div className="p-8 text-center text-slate-400">Loading leads…</div>
                    ) : leads.length === 0 ? (
                        <div className="p-8 text-center">
                            <FileText size={32} className="mx-auto text-slate-300 mb-2" aria-hidden="true" />
                            <p className="text-slate-400">No leads found.</p>
                        </div>
                    ) : leads.map(lead => (
                        <div
                            key={lead.id}
                            role="button"
                            tabIndex={0}
                            aria-label={`View details for ${lead.beneficiary_name}`}
                            onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDetail(lead); } }}
                            className="p-4 space-y-4 cursor-pointer hover:bg-slate-50 transition-all outline-none focus-visible:ring-2 focus-visible:ring-orange-400 focus-visible:ring-inset"
                            onClick={() => openDetail(lead)}
                        >
                            <div className="flex justify-between items-start">
                                <div className="space-y-1">
                                    <h3 className="font-bold text-slate-800 leading-tight">{lead.beneficiary_name}</h3>
                                    <p className="text-[10px] text-slate-500 font-mono tracking-tighter uppercase">Ref: {lead.ulid?.slice(-8)}</p>
                                </div>
                                <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${STATUS_BADGE[lead.status] ?? 'bg-slate-100 text-slate-600'}`}>
                                    {label(lead.status)}
                                </span>
                            </div>

                            <div className="grid grid-cols-2 gap-x-4 gap-y-3">
                                <div className="space-y-0.5">
                                    <p className="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Mobile</p>
                                    <p className="text-xs text-slate-700">{lead.beneficiary_mobile}</p>
                                </div>
                                <div className="space-y-0.5 text-right">
                                    <p className="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">District</p>
                                    <p className="text-xs text-slate-700 truncate">{lead.beneficiary_district}</p>
                                </div>
                                <div className="space-y-0.5">
                                    <p className="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Manager</p>
                                    <p className="text-xs text-slate-700 truncate">{lead.assigned_super_agent?.name ?? '—'}</p>
                                </div>
                                <div className="space-y-0.5 text-right">
                                    <p className="text-[10px] text-slate-500 uppercase font-semibold tracking-wider">Executive</p>
                                    <p className="text-xs text-slate-700 truncate">{lead.submitted_by_agent?.name ?? lead.assigned_agent?.name ?? 'Direct'}</p>
                                </div>
                            </div>

                            <div className="flex items-center justify-between pt-2 border-t border-slate-50">
                                <span className="text-[10px] text-slate-500 font-medium">Added on {fmt(lead.created_at)}</span>
                                <button
                                    onClick={(e) => { e.stopPropagation(); openDetail(lead); }}
                                    className="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 text-orange-600 rounded-full text-[10px] font-bold uppercase tracking-wider hover:bg-orange-100 transition-colors"
                                    aria-label={`Manage ${lead.beneficiary_name}`}
                                >
                                    Manage
                                </button>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Pagination */}
                {lastPage > 1 && (
                    <div className="px-4 py-3 border-t border-slate-100 flex items-center justify-between text-sm">
                        <span className="text-slate-500">Page {page} of {lastPage}</span>
                        <div className="flex gap-2">
                            <button
                                onClick={() => setPage(p => Math.max(1, p - 1))}
                                disabled={page === 1}
                                aria-label="Previous Page"
                                className="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                <ChevronLeft size={14} aria-hidden="true" /> Prev
                            </button>
                            <button
                                onClick={() => setPage(p => Math.min(lastPage, p + 1))}
                                disabled={page === lastPage}
                                aria-label="Next Page"
                                className="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                Next <ChevronRight size={14} aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* ═══════════════ LEAD DETAIL PANEL ═══════════════ */}
            {
                detail && (
                    <div
                        className="fixed inset-0 bg-black/50 z-50 flex justify-end"
                        onClick={() => setDetail(null)}
                        role="presentation"
                    >
                        <div
                            className="bg-white w-full max-w-lg h-full flex flex-col shadow-2xl"
                            onClick={e => e.stopPropagation()}
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="detail-title"
                        >
                            {/* Header */}
                            <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between sticky top-0 bg-white z-10">
                                <div>
                                    <h2 id="detail-title" className="text-base font-bold text-slate-800">Lead Details</h2>
                                    <p className="text-xs font-mono text-slate-500">{fullLead?.ulid}</p>
                                </div>
                                <button
                                    ref={closeButtonRef}
                                    onClick={() => setDetail(null)}
                                    aria-label="Close detail panel"
                                    className="p-1 rounded-lg hover:bg-slate-100 transition-colors"
                                >
                                    <X size={20} className="text-slate-500 hover:text-slate-600" />
                                </button>
                            </div>

                            <div className="flex-1 overflow-y-auto px-6 py-5 space-y-6">

                                {detailLoading ? (
                                    <p className="text-center text-slate-400 py-8">Loading…</p>
                                ) : (
                                    <>
                                        {/* ── Beneficiary ── */}
                                        <section>
                                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Beneficiary</p>
                                            <div className="space-y-2">
                                                {([
                                                    [<User size={13} />, 'Name', fullLead?.beneficiary_name],
                                                    [<Phone size={13} />, 'Mobile', fullLead?.beneficiary_mobile],
                                                    [<MapPin size={13} />, 'State', fullLead?.beneficiary_state],
                                                    [<MapPin size={13} />, 'District', fullLead?.beneficiary_district],
                                                    [<MapPin size={13} />, 'Address', fullLead?.beneficiary_address ?? '—'],
                                                    [<Hash size={13} />, 'Consumer No.', fullLead?.consumer_number ?? '—'],
                                                    [<Hash size={13} />, 'DISCOM', fullLead?.discom_name ?? '—'],
                                                ] as [React.ReactNode, string, string | React.ReactNode][]).map(([icon, lbl, val]) => (
                                                    <div key={lbl as string} className="flex gap-3 items-start">
                                                        <span className="text-slate-500 mt-0.5 shrink-0">{icon}</span>
                                                        <span className="text-xs text-slate-600 w-28 shrink-0">{lbl}</span>
                                                        <span className="text-xs font-medium text-slate-800 break-all">{val}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </section>

                                        {/* ── System ── */}
                                        <section>
                                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">System Info</p>
                                            <div className="space-y-2">
                                                {([
                                                    ['Source', fullLead?.source === 'public_form' ? 'Public Form' : 'Business Development Executive Submission'],
                                                    ['Roof Size', fullLead?.roof_size?.replace(/_/g, ' ') ?? '—'],
                                                    ['Capacity', (
                                                        <div className="flex gap-2">
                                                            <select
                                                                value={fullLead?.system_capacity ?? ''}
                                                                onChange={e => updateLeadMut.mutate({ ulid: fullLead!.ulid, data: { system_capacity: e.target.value } })}
                                                                className="border border-slate-200 rounded px-2 py-0.5 text-xs bg-white"
                                                            >
                                                                <option value="">Select Capacity…</option>
                                                                {Object.entries(CAPACITY_LABEL).map(([k, v]) => (
                                                                    <option key={k} value={k}>{v}</option>
                                                                ))}
                                                            </select>
                                                            {updateLeadMut.isPending && <span className="text-[10px] text-slate-400">Saving…</span>}
                                                        </div>
                                                    )],
                                                    ['Monthly Bill', fullLead?.monthly_bill_amount ? `₹${fullLead.monthly_bill_amount}` : '—'],
                                                    ['Govt. App No.', fullLead?.govt_application_number ?? '—'],
                                                    ['Business Development Manager Comm.', fullLead?.formatted_commissions?.super_agent_commission?.amount ? `₹${fullLead.formatted_commissions.super_agent_commission.amount}` : '—'],
                                                    ['Business Development Executive Comm.', fullLead?.formatted_commissions?.agent_commission?.amount ? `₹${fullLead.formatted_commissions.agent_commission.amount}` : '—'],
                                                    ['Created', fullLead?.created_at ? fmt(fullLead.created_at) : '—'],
                                                ] as [string, string][]).map(([lbl, val]) => (
                                                    <div key={lbl} className="flex gap-3 items-start">
                                                        <span className="text-xs text-slate-600 w-28 shrink-0">{lbl}</span>
                                                        <span className="text-xs font-medium text-slate-800">{val}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </section>

                                        {/* ── Business Development Manager Assignment ── */}
                                        <section>
                                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Business Development Manager Assignment</p>

                                            {fullLead?.assigned_super_agent ? (
                                                <div className="flex gap-3">
                                                    <span className="text-xs text-slate-600 w-28 shrink-0">Assigned to</span>
                                                    <span className="text-xs font-medium text-slate-800">{fullLead.assigned_super_agent.name} ({fullLead.assigned_super_agent.mobile})</span>
                                                </div>
                                            ) : (
                                                <div>
                                                    <p className="text-xs text-slate-600 mb-2">Not yet assigned to a manager</p>
                                                    <div className="flex gap-2">
                                                        <select
                                                            value={assignSuperAgent}
                                                            onChange={e => setAssignSuperAgent(e.target.value ? Number(e.target.value) : '')}
                                                            className="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500"
                                                        >
                                                            <option value="">Select active manager…</option>
                                                            {activeSuperAgents.map(a => (
                                                                <option key={a.id} value={a.id}>{a.name} {a.super_agent_code ? `(${a.super_agent_code})` : ''}</option>
                                                            ))}
                                                        </select>
                                                        <button
                                                            disabled={!assignSuperAgent || assignMut.isPending}
                                                            onClick={() => assignMut.mutate({ ulid: fullLead!.ulid, super_agent_id: assignSuperAgent as number })}
                                                            className="px-3 py-2 bg-orange-500 text-white text-xs rounded-lg hover:bg-orange-600 disabled:opacity-40 shrink-0"
                                                        >
                                                            {assignMut.isPending ? '…' : 'Assign'}
                                                        </button>
                                                    </div>
                                                </div>
                                            )}
                                        </section>
                                        {/* ── Documents ── */}
                                        <section>
                                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Documents</p>
                                            {(fullLead?.documents && fullLead.documents.length > 0) ? (
                                                <div className="grid grid-cols-2 gap-3">
                                                    {fullLead.documents.map(doc => {
                                                        const labelMap: Record<string, string> = {
                                                            aadhaar: 'Aadhaar Card',
                                                            electricity_bill: 'Electricity Bill',
                                                            photo: 'Beneficiary Photo',
                                                            solar_roof_photo: 'Solar Roof Photo',
                                                            bank_passbook: 'Bank Passbook',
                                                            other: 'PAN / Other'
                                                        };
                                                        const iconMap: Record<string, React.ReactNode> = {
                                                            aadhaar: <FileBadge size={16} />,
                                                            electricity_bill: <FileText size={16} />,
                                                            photo: <ImageIcon size={16} />,
                                                            solar_roof_photo: <ImageIcon size={16} />,
                                                            bank_passbook: <CreditCard size={16} />,
                                                            other: <FileText size={16} />
                                                        };

                                                        return (
                                                            <button
                                                                key={doc.id}
                                                                onClick={() => openAuthenticatedFile(doc.download_url, doc.original_filename)}
                                                                className="flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:border-orange-300 hover:bg-orange-50 transition-all group w-full text-left"
                                                            >
                                                                <span className="text-slate-500 group-hover:text-orange-500">{iconMap[doc.document_type] ?? <FileText size={16} />}</span>
                                                                <div className="flex-1 min-w-0">
                                                                    <p className="text-[11px] font-semibold text-slate-800 truncate">{labelMap[doc.document_type] ?? 'Document'}</p>
                                                                    <p className="text-[9px] text-slate-400 truncate">{doc.original_filename}</p>
                                                                </div>
                                                                <Download size={12} className="text-slate-300 group-hover:text-orange-400" />
                                                            </button>
                                                        );
                                                    })}
                                                </div>
                                            ) : (
                                                <div className="bg-slate-50 border border-slate-100 rounded-lg p-3 text-center">
                                                    <p className="text-[10px] text-slate-400 italic">No documents uploaded yet.</p>
                                                </div>
                                            )}
                                        </section>

                                        {/* ── Update Status ── */}
                                        <section>
                                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Update Lead Status</p>
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-2">
                                                    <span className={`px-2 py-0.5 rounded text-xs font-medium capitalize ${STATUS_BADGE[fullLead?.status ?? ''] ?? 'bg-slate-100 text-slate-600'}`}>
                                                        {label(fullLead?.status ?? '')}
                                                    </span>
                                                    <span className="text-slate-400 text-xs">→</span>
                                                    <select
                                                        value={newStatus}
                                                        onChange={e => setNewStatus(e.target.value)}
                                                        className="flex-1 border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500"
                                                    >
                                                        {ALL_STATUSES.map(s => (
                                                            <option key={s} value={s}>{label(s)}</option>
                                                        ))}
                                                    </select>
                                                </div>
                                                <textarea
                                                    rows={2}
                                                    value={statusNote}
                                                    onChange={e => setStatusNote(e.target.value)}
                                                    placeholder="Optional note for this status change…"
                                                    className="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"
                                                />
                                                <button
                                                    disabled={newStatus === fullLead?.status || statusMut.isPending}
                                                    onClick={() => statusMut.mutate({ ulid: fullLead!.ulid, status: newStatus, notes: statusNote || undefined })}
                                                    className="w-full py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 disabled:opacity-40 transition-colors"
                                                >
                                                    {statusMut.isPending ? 'Saving…' : 'Save Status Change'}
                                                </button>
                                            </div>
                                        </section>

                                        {/* ── Commission Entry (Persistent Management) ── */}
                                        {(fullLead?.status === 'completed' || fullLead?.status === 'installed') && (
                                            <section className="bg-orange-50 border border-orange-200 rounded-xl p-4">
                                                <div className="flex items-center justify-between mb-3">
                                                    <p className="text-xs font-semibold text-orange-600 uppercase tracking-widest">💰 Commission Management</p>
                                                    {fullLead?.formatted_commissions?.super_agent_commission && (
                                                        <span className="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-black uppercase tracking-tighter">Already recorded</span>
                                                    )}
                                                </div>

                                                {(() => {
                                                    const agent = fullLead.assigned_agent ?? fullLead.submitted_by_agent;
                                                    const superAgent = fullLead.assigned_super_agent;

                                                    // Use sidebarCommissionPrompt if it exists (meaning we just changed state)
                                                    // Otherwise derive it from the lead to allow persistent editing/entry
                                                    let prompt = sidebarCommissionPrompt;
                                                    if (!prompt) {
                                                        if (superAgent) {
                                                            prompt = {
                                                                should_prompt: true,
                                                                payee_role: 'super_agent',
                                                                payee_id: superAgent.id,
                                                                payee_name: superAgent.name,
                                                                payee_code: (superAgent as any).super_agent_code || '',
                                                                payee_type_label: 'Business Development Manager',
                                                            };
                                                        } else if (agent) {
                                                            prompt = {
                                                                should_prompt: true,
                                                                payee_role: 'agent',
                                                                payee_id: agent.id,
                                                                payee_name: agent.name,
                                                                payee_code: (agent as any).agent_id || (agent as any).code || '',
                                                                payee_type_label: 'Business Development Executive (Direct)',
                                                            };
                                                        }
                                                    }

                                                    if (!prompt) return <p className="text-[10px] text-slate-400 italic">No eligible payee found for this lead.</p>;

                                                    return (
                                                        <CommissionInlineEntry
                                                            leadUlid={fullLead.ulid}
                                                            leadStatus={fullLead.status}
                                                            commissionPrompt={prompt}
                                                            existingCommission={fullLead.formatted_commissions?.super_agent_commission || fullLead.formatted_commissions?.agent_commission || null}
                                                            onSaved={() => {
                                                                setSidebarCommissionPrompt(null);
                                                                qc.invalidateQueries({ queryKey: ['admin-lead-detail', fullLead.ulid] });
                                                            }}
                                                            onSkip={() => setSidebarCommissionPrompt(null)}
                                                        />
                                                    );
                                                })()}
                                            </section>
                                        )}

                                        {/* ── Admin Notes ── */}
                                        {fullLead?.admin_notes && (
                                            <section>
                                                <p className="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Admin Notes</p>
                                                <div className="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5 flex gap-2">
                                                    <AlertCircle size={14} className="text-amber-500 shrink-0 mt-0.5" />
                                                    <p className="text-xs text-amber-800">{fullLead.admin_notes}</p>
                                                </div>
                                            </section>
                                        )}

                                        {/* ── Status History ── */}
                                        {(fullLead?.status_logs?.length ?? 0) > 0 && (
                                            <section>
                                                <p className="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Status History</p>
                                                <ol className="relative border-l border-slate-200 space-y-4 ml-2">
                                                    {fullLead!.status_logs!.map(log => (
                                                        <li key={log.id} className="ml-4">
                                                            <span className="absolute -left-1.5 w-3 h-3 rounded-full bg-orange-400 border-2 border-white" />
                                                            <div className="flex items-center gap-2 flex-wrap">
                                                                <span className={`px-1.5 py-0.5 rounded text-xs ${STATUS_BADGE[log.from_status] ?? 'bg-slate-100 text-slate-600'}`}>{label(log.from_status)}</span>
                                                                <span className="text-slate-400 text-xs">→</span>
                                                                <span className={`px-1.5 py-0.5 rounded text-xs ${STATUS_BADGE[log.to_status] ?? 'bg-slate-100 text-slate-600'}`}>{label(log.to_status)}</span>
                                                            </div>
                                                            <div className="flex items-center gap-2 mt-1">
                                                                <Calendar size={11} className="text-slate-400" />
                                                                <span className="text-xs text-slate-500">{fmt(log.created_at)}</span>
                                                                {log.changedBy && <span className="text-xs text-slate-400">by {log.changedBy.name}</span>}
                                                            </div>
                                                            {log.notes && <p className="text-xs text-slate-500 mt-0.5 italic">"{log.notes}"</p>}
                                                        </li>
                                                    ))}
                                                </ol>
                                            </section>
                                        )}
                                    </>
                                )}
                            </div>

                            {/* Footer close */}
                            <div className="px-6 py-4 border-t border-slate-100 sticky bottom-0 bg-white">
                                <button onClick={() => setDetail(null)}
                                    className="w-full py-2.5 border border-slate-200 text-slate-600 rounded-lg font-medium hover:bg-slate-50 transition-colors text-sm">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                )
            }
        </div>
    );
}
