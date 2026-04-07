import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { X, ClipboardCheck, CreditCard, ChevronLeft, ChevronRight, Shield, IndianRupee, CheckCircle } from 'lucide-react';
import { superAdminCommissionsApi } from '@/services/commissions.api';
import type { Commission, MarkCommissionPaidPayload, CommissionPaymentMethod } from '@/types';
import toast from 'react-hot-toast';

const STATUS_STYLE: Record<string, string> = {
    unpaid: 'bg-amber-100 text-amber-700 font-bold',
    paid:   'bg-green-100 text-green-700 font-bold outline outline-1 outline-green-200',
};

export default function SuperAdminCommissionsPage() {
    const qc = useQueryClient();

    const [page,   setPage]   = useState(1);
    const [filter, setFilter] = useState<'all' | 'unpaid' | 'paid'>('all');

    const [showPaidModal,    setShowPaidModal]    = useState<Commission | null>(null);
    const [paymentForm, setPaymentForm] = useState<{ method: CommissionPaymentMethod; ref: string; notes: string }>({ method: 'bank_transfer', ref: '', notes: '' });

    const { data: listData, isLoading } = useQuery({
        queryKey: ['super-admin-commissions', filter, page],
        queryFn:  () => superAdminCommissionsApi.getAll({
            page,
            per_page: 20,
            ...(filter !== 'all' ? { status: filter } : {}),
        }),
    });

    const commissions: Commission[] = listData?.data?.data?.data ?? [];
    const meta = listData?.data?.data?.meta;
    const lastPage = meta?.last_page ?? 1;

    const { data: summaryData } = useQuery({
        queryKey: ['super-admin-commissions-summary'],
        queryFn:  () => superAdminCommissionsApi.getSummary(),
    });
    const summary = summaryData?.data?.data;

    const settleMut = useMutation({
        mutationFn: ({ id, data }: { id: number; data: MarkCommissionPaidPayload }) =>
            superAdminCommissionsApi.settle(id, data),
        onSuccess: () => {
            qc.invalidateQueries({ queryKey: ['super-admin-commissions'] });
            qc.invalidateQueries({ queryKey: ['super-admin-commissions-summary'] });
            toast.success('Commission settled successfully!');
            setShowPaidModal(null);
            setPaymentForm({ method: 'bank_transfer', ref: '', notes: '' });
        },
        onError: (err: any) => toast.error(err.response?.data?.message || 'Failed to record settlement.'),
    });

    const handleSettle = () => {
        if (!showPaidModal || !paymentForm.ref) return;
        settleMut.mutate({
            id:   showPaidModal.id,
            data: {
                payment_method:    paymentForm.method,
                payment_reference: paymentForm.ref,
                payment_notes:     paymentForm.notes,
            },
        });
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex justify-between items-end">
                <div>
                    <h1 className="text-2xl font-black text-slate-800 tracking-tight">Admin Commission Settlement</h1>
                    <p className="text-sm text-slate-500 mt-1">Manage and settle commissions payable to Administrators.</p>
                </div>
            </div>

            {/* Summary Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div className="flex justify-between items-start">
                        <div className="p-2 bg-orange-50 rounded-xl text-orange-600"><IndianRupee size={20} /></div>
                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Unpaid (Admins)</span>
                    </div>
                    <div className="mt-4">
                        <div className="text-2xl font-black text-slate-800 tracking-tight">
                            ₹{Number(summary?.admin_unpaid_amount || 0).toLocaleString('en-IN')}
                        </div>
                        <div className="text-[10px] text-slate-500 font-bold mt-1 uppercase tracking-tighter">
                            {summary?.admin_unpaid_count || 0} Pending Records
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div className="flex justify-between items-start">
                        <div className="p-2 bg-emerald-50 rounded-xl text-emerald-600"><ClipboardCheck size={20} /></div>
                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Settled (Admins)</span>
                    </div>
                    <div className="mt-4">
                        <div className="text-2xl font-black text-slate-800 tracking-tight">
                            ₹{Number(summary?.admin_paid_amount || 0).toLocaleString('en-IN')}
                        </div>
                        <div className="text-[10px] text-slate-500 font-bold mt-1 uppercase tracking-tighter">Cleared Commissions</div>
                    </div>
                </div>

                <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div className="flex justify-between items-start">
                        <div className="p-2 bg-slate-50 rounded-xl text-slate-400"><IndianRupee size={20} /></div>
                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Disbursed (All)</span>
                    </div>
                    <div className="mt-4">
                        <div className="text-2xl font-black text-slate-800 tracking-tight">
                            ₹{Number(summary?.all_time_disbursed || 0).toLocaleString('en-IN')}
                        </div>
                        <div className="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-tighter italic">Platform-wide Payouts</div>
                    </div>
                </div>
            </div>

            {/* Info Banner */}
            <div className="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 flex items-start gap-4 shadow-sm">
                <div className="p-2 rounded-xl shadow-sm bg-white text-blue-600">
                    <Shield size={20} />
                </div>
                <div>
                    <h3 className="text-sm font-black text-blue-900">Admin Disbursements</h3>
                    <p className="text-xs mt-1 font-medium leading-relaxed text-blue-700">
                        This view shows commissions where you (Super Admin) are the payer and Admins are the payees.
                        Record the payment details below once you have made the transfer.
                    </p>
                </div>
            </div>

            {/* Filter Bar */}
            <div className="flex items-center gap-2">
                {(['all', 'unpaid', 'paid'] as const).map(f => (
                    <button
                        key={f}
                        onClick={() => { setFilter(f); setPage(1); }}
                        className={`px-5 py-2 rounded-xl text-xs font-bold capitalize transition-all ${filter === f ? 'bg-slate-800 text-white shadow-lg shadow-slate-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}
                    >
                        {f === 'unpaid' ? 'Unpaid / Pending' : f.charAt(0).toUpperCase() + f.slice(1)}
                    </button>
                ))}
            </div>

            {/* Table */}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left">
                        <thead className="bg-slate-50/50 border-b border-slate-100">
                            <tr>
                                {['Ref / Date', 'Admin (Payee)', 'Lead', 'Amount', 'Status', 'Action'].map(h => (
                                    <th key={h} className="px-5 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px] whitespace-nowrap">{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {isLoading ? (
                                <tr><td colSpan={6} className="text-center py-20 text-slate-400 animate-pulse font-bold tracking-widest">LOADING RECORDS...</td></tr>
                            ) : commissions.length === 0 ? (
                                <tr><td colSpan={6} className="text-center py-24 text-slate-400">
                                    <div className="flex flex-col items-center gap-3">
                                        <div className="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200"><ClipboardCheck size={32} /></div>
                                        <div className="font-bold">No Admin commissions found for this filter.</div>
                                    </div>
                                </td></tr>
                            ) : commissions.map(c => (
                                <tr key={c.id} className="hover:bg-slate-50/30 transition-colors">
                                    <td className="px-5 py-5 whitespace-nowrap">
                                        <div className="text-[10px] font-black text-slate-400 font-mono tracking-tighter">ID: {c.id}</div>
                                        <div className="text-[11px] text-slate-500 font-bold mt-1">
                                            {new Date(c.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })}
                                        </div>
                                    </td>
                                    <td className="px-5 py-5">
                                        <div className="flex items-center gap-2">
                                            <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                                <Shield size={14} className="text-blue-600" />
                                            </div>
                                            <div>
                                                <div className="text-sm text-slate-800 font-black">{c.payee?.name}</div>
                                                <div className="text-[9px] px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-black uppercase tracking-widest w-fit mt-0.5">Admin</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-5 py-5">
                                        <div className="text-xs font-bold text-slate-700">{c.lead?.beneficiary_name ?? '—'}</div>
                                        {c.lead?.ulid && (
                                            <div className="text-[10px] text-slate-400 font-mono mt-0.5 opacity-70">ULID: {c.lead.ulid.slice(-8)}</div>
                                        )}
                                    </td>
                                    <td className="px-5 py-5 whitespace-nowrap">
                                        <span className="text-base text-orange-600 font-black tracking-tight">
                                            ₹{Number(c.amount).toLocaleString('en-IN')}
                                        </span>
                                    </td>
                                    <td className="px-5 py-5 whitespace-nowrap">
                                        <span className={`px-2.5 py-1 rounded-lg text-[9px] uppercase font-black tracking-widest ${STATUS_STYLE[c.payment_status] || 'bg-slate-100 text-slate-600'}`}>
                                            {c.payment_status}
                                        </span>
                                    </td>
                                    <td className="px-5 py-5">
                                        {c.payment_status === 'unpaid' ? (
                                            <button
                                                onClick={() => { setShowPaidModal(c); setPaymentForm({ method: 'bank_transfer', ref: '', notes: '' }); }}
                                                className="px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 transform hover:-translate-y-0.5 active:translate-y-0"
                                            >
                                                Settle Now
                                            </button>
                                        ) : (
                                            <div className="bg-slate-50 border border-slate-100 rounded-xl px-3 py-2">
                                                <div className="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Reference</div>
                                                <div className="text-[10px] font-mono text-slate-600 font-black truncate max-w-[120px]">{c.payment_reference}</div>
                                                <div className="text-[9px] text-slate-400 mt-1 font-bold">
                                                    Paid: {c.paid_at ? new Date(c.paid_at).toLocaleDateString('en-IN') : '—'}
                                                </div>
                                            </div>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {lastPage > 1 && (
                    <div className="px-5 py-4 border-t border-slate-100 flex items-center justify-between text-xs bg-slate-50/30">
                        <span className="text-slate-500 font-bold opacity-60">Page {page} of {lastPage}</span>
                        <div className="flex gap-2">
                            <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1}
                                className="inline-flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-600 font-bold hover:bg-slate-50 disabled:opacity-30 transition-all">
                                <ChevronLeft size={16} /> Previous
                            </button>
                            <button onClick={() => setPage(p => Math.min(lastPage, p + 1))} disabled={page === lastPage}
                                className="inline-flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-600 font-bold hover:bg-slate-50 disabled:opacity-30 transition-all">
                                Next <ChevronRight size={16} />
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* ══════════ SETTLE MODAL ══════════ */}
            {showPaidModal && (
                <div className="fixed inset-0 bg-slate-900/70 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={() => setShowPaidModal(null)}>
                    <div className="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden" onClick={e => e.stopPropagation()}>
                        <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <h3 className="font-black text-slate-800 tracking-tight">Record Settlement</h3>
                            <button onClick={() => setShowPaidModal(null)} className="text-slate-400 hover:text-slate-600"><X size={20} /></button>
                        </div>
                        <div className="p-6 space-y-5">
                            {/* Payee + Amount banner */}
                            <div className="bg-primary rounded-2xl p-5 text-white shadow-xl shadow-primary/20 flex justify-between items-center relative overflow-hidden">
                                <div className="relative z-10">
                                    <div className="text-[10px] font-black text-white/70 uppercase tracking-widest mb-1">Paying To</div>
                                    <div className="text-lg font-black tracking-tight">{showPaidModal.payee?.name}</div>
                                    <div className="text-[10px] text-white/60 font-bold mt-0.5">Administrator</div>
                                </div>
                                <div className="text-right relative z-10">
                                    <div className="text-[10px] font-black text-white/70 uppercase tracking-widest mb-1 text-right">Amount</div>
                                    <div className="text-2xl font-black">₹{Number(showPaidModal.amount).toLocaleString('en-IN')}</div>
                                </div>
                                <div className="absolute -right-4 -bottom-4 text-white/10 rotate-12"><IndianRupee size={100} /></div>
                            </div>

                            {/* Payment method */}
                            <div>
                                <label className="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1 text-center">Payment Method</label>
                                <div className="grid grid-cols-2 gap-2">
                                    {(['bank_transfer', 'upi', 'cash', 'cheque'] as CommissionPaymentMethod[]).map(m => (
                                        <button
                                            key={m}
                                            onClick={() => setPaymentForm(prev => ({ ...prev, method: m }))}
                                            className={`py-3 px-3 border-2 rounded-2xl text-[11px] font-black capitalize flex flex-col items-center gap-1.5 transition-all ${paymentForm.method === m ? 'border-primary bg-primary/5 text-primary shadow-lg shadow-primary/10' : 'border-slate-100 text-slate-400 hover:bg-slate-50'}`}
                                        >
                                            <CreditCard size={18} />
                                            {m.replace('_', ' ')}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Reference */}
                            <div>
                                <label className="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Reference No. (UTR / RTGS / CHQ) *</label>
                                <input
                                    type="text"
                                    value={paymentForm.ref}
                                    onChange={e => setPaymentForm(prev => ({ ...prev, ref: e.target.value }))}
                                    placeholder="Enter transaction reference"
                                    className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-3.5 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all placeholder:text-slate-300"
                                />
                            </div>

                            {/* Notes (optional) */}
                            <div>
                                <label className="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Notes (optional)</label>
                                <input
                                    type="text"
                                    value={paymentForm.notes}
                                    onChange={e => setPaymentForm(prev => ({ ...prev, notes: e.target.value }))}
                                    placeholder="e.g. March commission batch"
                                    className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-3.5 text-sm text-slate-700 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all placeholder:text-slate-300"
                                />
                            </div>
                        </div>
                        <div className="p-6 bg-slate-50 border-t border-slate-100">
                            <button
                                onClick={handleSettle}
                                disabled={!paymentForm.ref || settleMut.isPending}
                                className="w-full py-4 bg-primary text-white rounded-2xl font-black text-sm shadow-xl shadow-primary/20 hover:bg-primary-dark disabled:opacity-50 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                            >
                                <CheckCircle size={18} />
                                {settleMut.isPending ? 'Recording...' : 'Confirm Settlement'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
