import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { IndianRupee, ClipboardCheck, Wallet, Clock, MapPin, ReceiptText, ChevronLeft, ChevronRight } from 'lucide-react';
import { agentCommissionsApi } from '@/api/commissions.api';
import { formatCurrency } from '@/utils/formatters';
import type { Commission } from '@/types';

const COMMISSION_STATUS_STYLE: Record<string, string> = {
    unpaid: 'bg-amber-100 text-amber-700 font-bold',
    paid: 'bg-green-100 text-green-700 font-bold outline outline-1 outline-green-200',
};

export default function AgentCommissionsPage() {
    const [earningsFilter, setEarningsFilter] = useState<'pending' | 'paid' | 'all'>('all');
    const [page, setPage] = useState(1);

    const backendFilter = earningsFilter === 'pending' ? 'pending_my_payment' : (earningsFilter === 'paid' ? 'fully_paid' : 'all');

    const { data: payoutsData, isLoading: loadingPayouts } = useQuery({
        queryKey: ['agent-commissions', backendFilter, page],
        queryFn: () => agentCommissionsApi.getAll({ filter: backendFilter, page: page, per_page: 20 }),
    });
    const payouts: Commission[] = payoutsData?.data?.data ?? [];
    const meta = (payoutsData?.data as any)?.meta ?? { last_page: 1, current_page: 1, total: 0 };

    const { data: summaryData } = useQuery({
        queryKey: ['agent-commissions-summary'],
        queryFn: () => agentCommissionsApi.getSummary(),
    });
    const summary = summaryData?.data?.data;

    return (
        <div className="space-y-6">
            {/* Header */}
            <div>
                <h1 className="text-2xl font-black text-slate-800 tracking-tight">Personal Earnings</h1>
                <p className="text-sm text-slate-500 mt-1">Track your commission disbursements and historical earnings.</p>
            </div>

            {/* Summary Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div className="flex justify-between items-start">
                        <div className="p-2 bg-amber-50 rounded-xl text-amber-600"><Clock size={20} /></div>
                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending Payout</span>
                    </div>
                    <div className="mt-4">
                        <div className="text-2xl font-black text-slate-800 tracking-tight">{formatCurrency(summary?.total_unpaid || 0)}</div>
                        <div className="text-[10px] text-amber-600 font-bold mt-1 uppercase tracking-tighter italic">Awaiting Settlement</div>
                    </div>
                </div>

                <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div className="flex justify-between items-start">
                        <div className="p-2 bg-emerald-50 rounded-xl text-emerald-600"><Wallet size={20} /></div>
                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Settled</span>
                    </div>
                    <div className="mt-4">
                        <div className="text-2xl font-black text-slate-800 tracking-tight">{formatCurrency(summary?.total_paid || 0)}</div>
                        <div className="text-[10px] text-slate-500 font-bold mt-1 uppercase tracking-tighter italic">Paid to Date</div>
                    </div>
                </div>

                <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div className="flex justify-between items-start">
                        <div className="p-2 bg-orange-50 rounded-xl text-orange-600"><IndianRupee size={20} /></div>
                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">This Month</span>
                    </div>
                    <div className="mt-4">
                        <div className="text-2xl font-black text-slate-800 tracking-tight">{formatCurrency(summary?.this_month || 0)}</div>
                        <div className="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-tighter">Current Earnings</div>
                    </div>
                </div>
            </div>

            {/* Toggles */}
            <div className="flex items-center justify-between gap-4">
                <div className="flex gap-2">
                    {(['pending', 'paid', 'all'] as const).map(f => (
                        <button key={f} onClick={() => { setEarningsFilter(f); setPage(1); }}
                            className={`px-5 py-2 rounded-xl text-xs font-bold capitalize transition-all ${earningsFilter === f ? 'bg-slate-800 text-white shadow-lg shadow-slate-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}>
                            {f === 'pending' ? 'Unpaid' : f}
                        </button>
                    ))}
                </div>
            </div>

            {/* Alert Box */}
            <div className="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 flex items-start gap-4 shadow-sm shadow-blue-100">
                <div className="p-2 rounded-xl shadow-sm bg-white text-blue-600">
                    <ReceiptText size={20} />
                </div>
                <div>
                    <h3 className="text-sm font-black text-blue-900">Payout Timeline Guidance</h3>
                    <p className="text-xs mt-1 font-medium leading-relaxed text-blue-700">
                        Once a lead is marked as <b>Installed</b> or <b>Completed</b>, your commission is recorded.
                        BDMs or Admins review and settle payments typically within 2-5 business days via your preferred bank method.
                    </p>
                </div>
            </div>

            {/* Table Container */}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden mt-4">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left">
                        <thead className="bg-slate-50/50 border-b border-slate-100">
                            <tr>
                                {['Date', 'Lead Identity', 'Amount', 'Status', 'Settlement Details'].map(h => (
                                    <th key={h} className="px-5 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px] whitespace-nowrap">{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {loadingPayouts ? (
                                <tr><td colSpan={5} className="text-center py-20 text-slate-400 animate-pulse font-bold tracking-widest">LOADING...</td></tr>
                            ) : payouts.length === 0 ? (
                                <tr><td colSpan={5} className="text-center py-24 text-slate-400">
                                    <div className="flex flex-col items-center gap-3">
                                        <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200"><ClipboardCheck size={32} /></div>
                                        <div className="font-bold">No commission records found.</div>
                                    </div>
                                </td></tr>
                            ) : payouts.map((c) => (
                                <tr key={c.id} className="hover:bg-slate-50/30 transition-colors group">
                                    <td className="px-5 py-5 whitespace-nowrap">
                                        <div className="text-[10px] font-black text-slate-400 font-mono tracking-tighter">REC: {c.id}</div>
                                        <div className="text-[11px] text-slate-500 font-bold mt-1">
                                            {new Date(c.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}
                                        </div>
                                    </td>
                                    <td className="px-5 py-5">
                                        <div className="text-xs font-black text-slate-800">{c.lead?.beneficiary_name}</div>
                                        <div className="flex items-center gap-1.5 text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-tighter truncate max-w-[200px]">
                                            <MapPin size={10} className="text-slate-300" /> {c.lead?.beneficiary_district} (Ref: {c.lead?.ulid?.slice(-8)})
                                        </div>
                                    </td>
                                    <td className="px-5 py-5">
                                        <div className="text-sm text-orange-600 font-black tracking-tight">{formatCurrency(c.amount)}</div>
                                    </td>
                                    <td className="px-5 py-5 whitespace-nowrap">
                                        <span className={`px-2.5 py-1 rounded-lg text-[9px] uppercase font-black tracking-widest ${COMMISSION_STATUS_STYLE[c.payment_status] || 'bg-slate-100 text-slate-600'}`}>
                                            {c.payment_status === 'paid' ? 'Settled' : 'Pending'}
                                        </span>
                                    </td>
                                    <td className="px-5 py-5">
                                        {c.payment_status === 'paid' ? (
                                            <div className="bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 group-hover:bg-white transition-colors">
                                                <div className="text-[10px] font-black text-green-600 flex items-center gap-1">
                                                    <div className="w-1.5 h-1.5 rounded-full bg-green-500" /> Settled
                                                </div>
                                                <div className="text-[9px] text-slate-400 font-mono mt-0.5 truncate max-w-[150px]">
                                                    Ref: {c.payment_reference || 'Ref Pending'} • {c.payment_method?.replace('_', ' ')}
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="text-[10px] text-slate-400 font-bold italic py-2 px-3 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                                Awaiting Disbursement
                                            </div>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {meta.last_page > 1 && (
                    <div className="px-5 py-4 border-t border-slate-100 flex items-center justify-between text-xs bg-slate-50/30">
                        <span className="text-slate-500 font-bold opacity-60">Page {page} of {meta.last_page}</span>
                        <div className="flex gap-2">
                            <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1}
                                className="inline-flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-600 font-bold hover:bg-slate-50 disabled:opacity-30 transition-all">
                                <ChevronLeft size={16} /> Prev
                            </button>
                            <button onClick={() => setPage(p => Math.min(meta.last_page, p + 1))} disabled={page === meta.last_page}
                                className="inline-flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-600 font-bold hover:bg-slate-50 disabled:opacity-30 transition-all">
                                Next <ChevronRight size={16} />
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
