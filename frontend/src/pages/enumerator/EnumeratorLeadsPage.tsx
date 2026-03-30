import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { FileText, PlusCircle, ChevronLeft, ChevronRight } from 'lucide-react';
import { enumeratorApi } from '@/services/enumerator.api';
import LeadStatusBadge from '@/components/shared/LeadStatusBadge';
import { VerificationStatusBadge } from '@/components/shared/VerificationStatusBadge';
import { formatDate } from '@/utils/formatters';
import type { Lead } from '@/types';

export default function EnumeratorLeadsPage() {
    const [page, setPage] = useState(1);

    const { data, isLoading } = useQuery({
        queryKey: ['enumerator-leads', page],
        queryFn: () => enumeratorApi.getLeads({ page }),
    });

    const leads: Lead[] = data?.data?.data ?? [];
    const totalPages = data?.data?.last_page ?? 1;

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <FileText className="w-5 h-5 text-emerald-600" />
                        My Leads
                    </h1>
                    <p className="text-sm text-slate-500 mt-0.5">Leads you have submitted</p>
                </div>
                <Link
                    to="/enumerator/leads/new"
                    className="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors"
                >
                    <PlusCircle className="w-4 h-4" /> New Lead
                </Link>
            </div>

            <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                {/* Desktop Table */}
                <div className="hidden md:block overflow-x-auto">
                    <table className="min-w-full text-sm" aria-label="My Submitted Leads">
                        <thead className="bg-slate-50 border-b border-slate-200">
                            <tr>
                                {['Ref', 'Beneficiary', 'Mobile', 'District', 'Capacity', 'Verification', 'Status', 'Date'].map(h => (
                                    <th key={h} className="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {isLoading ? (
                                <tr><td colSpan={8} className="text-center py-12 text-slate-400">Loading leads...</td></tr>
                            ) : leads.length === 0 ? (
                                <tr><td colSpan={8} className="text-center py-12 text-slate-400">No leads found. Submit your first lead!</td></tr>
                            ) : leads.map((lead) => (
                                <tr key={lead.id} className="hover:bg-slate-50 transition-colors">
                                    <td className="px-4 py-3 font-mono text-xs text-slate-500">{lead.ulid?.slice(-8)}</td>
                                    <td className="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">{lead.beneficiary_name}</td>
                                    <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_mobile}</td>
                                    <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.beneficiary_district}</td>
                                    <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{lead.system_capacity?.replace(/_/g, ' ') || '—'}</td>
                                    <td className="px-4 py-3 whitespace-nowrap">
                                        <VerificationStatusBadge status={lead.verification_status} revertCount={lead.revert_count} />
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap">
                                        <LeadStatusBadge status={lead.status} />
                                    </td>
                                    <td className="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{formatDate(lead.created_at)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Mobile Cards */}
                <div className="md:hidden divide-y divide-slate-100">
                    {isLoading ? (
                        <div className="p-8 text-center text-slate-400">Loading...</div>
                    ) : leads.length === 0 ? (
                        <div className="p-8 text-center text-slate-400">No leads yet.</div>
                    ) : leads.map((lead) => (
                        <div key={lead.id} className="p-4 space-y-3">
                            <div className="flex justify-between items-start">
                                <div>
                                    <p className="font-semibold text-slate-800">{lead.beneficiary_name}</p>
                                    <p className="text-xs font-mono text-slate-400">Ref: {lead.ulid?.slice(-8)}</p>
                                </div>
                                <LeadStatusBadge status={lead.status} />
                            </div>
                            <div className="grid grid-cols-2 gap-2 text-xs text-slate-600">
                                <span>📱 {lead.beneficiary_mobile}</span>
                                <span>📍 {lead.beneficiary_district}</span>
                                <span>⚡ {lead.system_capacity?.replace(/_/g, ' ') || '—'}</span>
                                <span>📅 {formatDate(lead.created_at)}</span>
                            </div>
                            <VerificationStatusBadge status={lead.verification_status} revertCount={lead.revert_count} />
                        </div>
                    ))}
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                    <div className="px-4 py-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1}
                            className="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">
                            <ChevronLeft className="w-3 h-3" /> Prev
                        </button>
                        <span className="text-slate-500">Page {page} of {totalPages}</span>
                        <button onClick={() => setPage(p => Math.min(totalPages, p + 1))} disabled={page === totalPages}
                            className="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">
                            Next <ChevronRight className="w-3 h-3" />
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
