import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link, useNavigate } from 'react-router-dom';
import { FileText, PlusCircle } from 'lucide-react';
import { leadsApi } from '@/api/leads.api';
import type { Lead } from '@/types';
import LeadStatusBadge from '@/components/shared/LeadStatusBadge';
import { VerificationStatusBadge } from '@/components/shared/VerificationStatusBadge';
import { RevertedLeadBanner } from '@/components/agent/RevertedLeadBanner';
import { formatDate } from '@/utils/formatters';

export default function AgentLeadsPage() {
    const [page, setPage] = useState(1);
    const navigate = useNavigate();

    const { data, isLoading } = useQuery({
        queryKey: ['agent-leads', page],
        queryFn: () => leadsApi.getAgentLeads({ page }),
    });

    const leads = data?.data?.data || [];
    const totalPages = data?.data?.last_page || 1;
    const revertedCount = (data?.data as any)?.meta?.reverted_count ??
        leads.filter((l: Lead) => l.verification_status === 'reverted_to_agent').length;

    return (
        <div className="space-y-6">
            {/* Reverted Leads Banner */}
            <RevertedLeadBanner
                count={revertedCount}
                onActionClick={() => navigate('/agent/leads?filter=reverted')}
            />

            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 className="text-xl font-bold text-slate-800">My Leads</h1>
                    <p className="text-sm text-slate-600 mt-0.5">Manage and track your submitted leads</p>
                </div>
                <div className="flex items-center gap-3">
                    <Link
                        to="/agent/leads/new"
                        className="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors"
                    >
                        <PlusCircle size={16} /> New Lead
                    </Link>
                </div>
            </div>

            <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                {/* Desktop Table View */}
                <div className="hidden lg:block overflow-x-auto">
                    <table className="min-w-full text-sm" aria-label="My Leads List">
                        <thead className="bg-slate-50 border-b border-slate-200">
                            <tr>
                                {[
                                    'Lead Ref', 'Beneficiary', 'Mobile', 'State', 'District',
                                    'DISCOM', 'Consumer No.', 'Capacity', 'Roof Size', 'Monthly Bill',
                                    'Aadhaar', 'Electricity Bill', 'PAN', 'Photo', 'Solar Roof', 'Passbook',
                                    'Commission', 'Pay Status', 'Verification', 'Status', 'Date', 'Action'
                                ].map(h => (
                                    <th key={h} scope="col" className="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide whitespace-nowrap">
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {isLoading ? (
                                <tr><td colSpan={22} className="text-center py-12 text-slate-400">Loading leads...</td></tr>
                            ) : leads.length === 0 ? (
                                <tr><td colSpan={22} className="text-center py-12 text-slate-400">No leads found.</td></tr>
                            ) : (
                                leads.map((lead) => (
                                    <tr
                                        key={lead.id}
                                        className={`hover:bg-slate-50 transition-colors ${lead.verification_status === 'reverted_to_agent'
                                            ? 'bg-red-50 border-l-4 border-red-500'
                                            : ''
                                            }`}
                                    >
                                        <td className="px-4 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">{lead.ulid?.slice(-8)}</td>
                                        <td className="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">
                                            <div className="flex flex-col">
                                                <span>{lead.beneficiary_name}</span>
                                                {lead.referral_agent_id && (
                                                    <span className="text-[10px] text-indigo-600 font-bold uppercase tracking-tighter">Referral Lead</span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_mobile}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_state}</td>
                                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_district}</td>

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
                                                            href={doc.download_url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="flex items-center gap-2 p-2 rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors group"
                                                            title="View Document"
                                                            aria-label={`View ${docType} for ${lead.beneficiary_name}`}
                                                        >
                                                            <FileText size={16} aria-hidden="true" />
                                                        </a>
                                                    ) : (
                                                        <span className="text-slate-400">—</span>
                                                    )}
                                                </td>
                                            );
                                        })}

                                        <td className="px-4 py-3 whitespace-nowrap text-slate-700 font-bold">
                                            {(lead.commissions as any)?.agent_commission?.amount ? `₹${(lead.commissions as any).agent_commission.amount}` : '—'}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            {(lead.commissions as any)?.agent_commission ? (
                                                <span className={`px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider ${(lead.commissions as any).agent_commission.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'
                                                    }`}>
                                                    {(lead.commissions as any).agent_commission.payment_status}
                                                </span>
                                            ) : '—'}
                                        </td>

                                        <td className="px-4 py-3 whitespace-nowrap">
                                            <VerificationStatusBadge
                                                status={lead.verification_status}
                                                revertCount={lead.revert_count}
                                            />
                                            {lead.verification_status === 'reverted_to_agent' && lead.revert_reason && (
                                                <p className="text-xs text-red-600 mt-1 max-w-[160px] truncate" title={lead.revert_reason}>
                                                    ↩ {lead.revert_reason}
                                                </p>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            <LeadStatusBadge status={lead.status} />
                                        </td>
                                        <td className="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">
                                            {formatDate(lead.created_at)}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            {lead.verification_status === 'reverted_to_agent' ? (
                                                <Link
                                                    to={`/agent/leads/${lead.ulid}/resubmit`}
                                                    className="px-2 py-1 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors"
                                                >
                                                    ↩ Edit & Resubmit
                                                </Link>
                                            ) : (
                                                <Link
                                                    to={`/agent/leads/${lead.ulid}`}
                                                    className="text-orange-600 hover:text-orange-700 font-medium text-xs"
                                                >
                                                    View →
                                                </Link>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Mobile Card View */}
                <div className="lg:hidden divide-y divide-slate-100">
                    {isLoading ? (
                        <div className="p-8 text-center text-slate-400">Loading leads...</div>
                    ) : leads.length === 0 ? (
                        <div className="p-8 text-center text-slate-400">No leads found.</div>
                    ) : (
                        leads.map((lead) => (
                            <div key={lead.id} className="p-4 space-y-4">
                                <div className="flex justify-between items-start">
                                    <div className="space-y-1">
                                        <Link to={`/agent/leads/${lead.ulid}`} className="font-bold text-slate-800 hover:text-orange-600 transition-colors block">
                                            {lead.beneficiary_name}
                                        </Link>
                                        <div className="flex items-center gap-2">
                                            <p className="text-[10px] text-slate-500 font-mono tracking-tighter uppercase">Ref: {lead.ulid?.slice(-8)}</p>
                                            {lead.referral_agent_id && (
                                                <span className="bg-indigo-50 text-indigo-600 text-[8px] font-black px-1 rounded border border-indigo-100 uppercase">Referral</span>
                                            )}
                                        </div>
                                    </div>
                                    <LeadStatusBadge status={lead.status} />
                                </div>

                                <div className="grid grid-cols-2 gap-x-4 gap-y-3">
                                    <div className="space-y-0.5">
                                        <p className="text-[10px] text-slate-500 uppercase font-semibold">Mobile</p>
                                        <p className="text-xs text-slate-700">{lead.beneficiary_mobile}</p>
                                    </div>
                                    <div className="space-y-0.5 text-right">
                                        <p className="text-[10px] text-slate-400 uppercase font-semibold">Location</p>
                                        <p className="text-xs text-slate-700 truncate">{lead.beneficiary_district}, {lead.beneficiary_state}</p>
                                    </div>
                                    <div className="space-y-0.5">
                                        <p className="text-[10px] text-slate-400 uppercase font-semibold">System / Capacity</p>
                                        <p className="text-xs text-slate-700">{lead.system_capacity?.replace(/_/g, ' ') || '—'}</p>
                                    </div>
                                    <div className="space-y-0.5 text-right">
                                        <p className="text-[10px] text-slate-400 uppercase font-semibold">Commission</p>
                                        <p className="text-xs font-bold text-slate-800">
                                            {(lead.commissions as any)?.agent_commission?.amount ? `₹${(lead.commissions as any).agent_commission.amount}` : '—'}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center justify-between pt-2 border-t border-slate-50">
                                    <span className="text-[10px] text-slate-500 font-medium">Added on {formatDate(lead.created_at)}</span>
                                    <Link
                                        to={`/agent/leads/${lead.ulid}`}
                                        className="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 text-orange-600 rounded-full text-[10px] font-bold uppercase tracking-wider hover:bg-orange-100 transition-colors"
                                        aria-label={`View details for ${lead.beneficiary_name}`}
                                    >
                                        Details <PlusCircle size={10} aria-hidden="true" />
                                    </Link>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                    <div className="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                        <button
                            onClick={() => setPage(p => Math.max(1, p - 1))}
                            disabled={page === 1}
                            aria-label="Previous Page"
                            className="text-xs px-3 py-1.5 rounded border border-slate-200 disabled:opacity-40 hover:bg-slate-50"
                        >
                            <span aria-hidden="true">←</span> Prev
                        </button>
                        <span className="text-xs text-slate-500" aria-label={`Page ${page} of ${totalPages}`}>Page {page} of {totalPages}</span>
                        <button
                            onClick={() => setPage(p => Math.min(totalPages, p + 1))}
                            disabled={page === totalPages}
                            aria-label="Next Page"
                            className="text-xs px-3 py-1.5 rounded border border-slate-200 disabled:opacity-40 hover:bg-slate-50"
                        >
                            Next <span aria-hidden="true">→</span>
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
