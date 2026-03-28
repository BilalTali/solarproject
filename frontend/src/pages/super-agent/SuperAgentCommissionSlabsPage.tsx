import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { slabsApi } from '../../api/slabs.api';
import { 
    Search, Edit2, 
    Settings, Power, ChevronRight, X,
    ShieldAlert, InfoIcon
} from 'lucide-react';
import { toast } from 'react-hot-toast';
import SEOHead from '../../components/shared/SEOHead';
import LoadingSpinner from '../../components/shared/LoadingSpinner';

export const SuperAgentCommissionSlabsPage: React.FC = () => {
    const queryClient = useQueryClient();
    const [searchTerm, setSearchTerm] = useState('');
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingSlab, setEditingSlab] = useState<any | null>(null);

    const { data: slabsResponse, isLoading } = useQuery({
        queryKey: ['super-agent-commission-slabs'],
        queryFn: slabsApi.superAgent.getEffective
    });

    const saveMutation = useMutation({
        mutationFn: slabsApi.superAgent.saveCustom,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['super-agent-commission-slabs'] });
            toast.success('Commission settings updated successfully');
            closeModal();
        },
        onError: (err: any) => {
            toast.error(err.response?.data?.message || 'Failed to save settings');
        }
    });

    const openModal = (slab: any) => {
        setEditingSlab(slab);
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingSlab(null);
    };

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const payload = {
            capacity: editingSlab.capacity,
            label: formData.get('label') as string,
            agent_commission: Number(formData.get('agent_commission')),
            super_agent_override: Number(formData.get('super_agent_override')),
            enumerator_commission: Number(formData.get('enumerator_commission')),
        };

        saveMutation.mutate(payload);
    };

    const slabs = slabsResponse?.data || [];
    const filteredSlabs = slabs.filter(s => 
        (s.label || '').toLowerCase().includes(searchTerm.toLowerCase()) || 
        (s.capacity || '').toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (isLoading) return <LoadingSpinner />;

    return (
        <div className="p-6">
            <SEOHead title="Commission Settings | Manager" />

            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Settings className="text-indigo-600" />
                        Commission Settings
                    </h1>
                    <p className="text-slate-500 font-medium">Control the payouts for your Business Development Executives (BDEs) & Enumerators</p>
                </div>
            </div>

            <div className="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-8 flex items-start gap-4">
                <div className="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                    <InfoIcon size={20} />
                </div>
                <div>
                    <h3 className="text-indigo-900 font-black text-sm uppercase tracking-wider">Dynamic Payout System</h3>
                    <p className="text-indigo-800/80 text-xs font-medium leading-relaxed mt-1">
                        By default, the system uses Admin settings. You can click "Customize" on any slab to set a specific payout rate for your own team. 
                        These rates apply to both your BDEs and Enumerators.
                    </p>
                </div>
            </div>

            {/* Search */}
            <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm mb-6 flex items-center">
                <div className="relative flex-grow max-w-md">
                    <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                    <input 
                        type="text" 
                        placeholder="Search slabs (3kw, 5kw...)"
                        className="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            {/* Table */}
            <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="bg-slate-50/50 border-b border-slate-100">
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Slab Type</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">BDE Commission</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">ENU Commission</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Manager Override</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {filteredSlabs.map((slab) => (
                                <tr key={slab.capacity} className="hover:bg-slate-50/30 transition-colors group">
                                    <td className="px-6 py-4">
                                        <div className="flex items-center gap-3">
                                            <div className={`w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs shrink-0 uppercase shadow-sm ${
                                                slab.is_custom ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-400'
                                            }`}>
                                                {slab.capacity}
                                            </div>
                                            <div>
                                                <p className="font-bold text-slate-900">{slab.label}</p>
                                                {slab.is_custom && (
                                                    <span className="text-[9px] font-black bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded uppercase tracking-tighter">Custom Set</span>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <div className="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-black text-sm">
                                            ₹{Number(slab.agent_commission).toLocaleString('en-IN')}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <div className="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-orange-50 text-orange-700 font-black text-sm">
                                            ₹{Number(slab.enumerator_commission || 0).toLocaleString('en-IN')}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <div className="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 font-black text-sm">
                                            ₹{Number(slab.super_agent_override).toLocaleString('en-IN')}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black tracking-tight ${
                                            slab.is_custom ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500'
                                        }`}>
                                            <Power size={10} className={slab.is_custom ? 'text-indigo-500' : 'text-slate-400'} />
                                            {slab.is_custom ? 'ACTIVE OVERRIDE' : 'USING DEFAULT'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <button 
                                            onClick={() => openModal(slab)}
                                            className="flex items-center gap-2 ml-auto bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-xs font-black shadow-sm transition-all hover:border-indigo-500 hover:text-indigo-600 active:scale-95"
                                        >
                                            <Edit2 size={14} />
                                            {slab.is_custom ? 'EDIT RATES' : 'CUSTOMIZE'}
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal */}
            {isModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={closeModal} />
                    <div className="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                        <div className="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <div>
                                <h2 className="text-xl font-black text-slate-900 tracking-tight">
                                    Customize {editingSlab.capacity.toUpperCase()} Slab
                                </h2>
                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Override Admin Defaults</p>
                            </div>
                            <button onClick={closeModal} className="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-all shadow-sm">
                                <X size={20} />
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="p-8">
                            <div className="space-y-6">
                                <div>
                                    <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Custom Display Label</label>
                                    <input 
                                        name="label"
                                        required
                                        defaultValue={editingSlab?.label}
                                        className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                                        placeholder="e.g., Standard 3 kW Plan"
                                    />
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">BDE Payout (₹)</label>
                                        <input 
                                            name="agent_commission"
                                            type="number"
                                            required
                                            min="0"
                                            defaultValue={editingSlab?.agent_commission}
                                            className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">ENU Payout (₹)</label>
                                        <input 
                                            name="enumerator_commission"
                                            type="number"
                                            required
                                            min="0"
                                            defaultValue={editingSlab?.enumerator_commission ?? 0}
                                            className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                                            placeholder="0.00"
                                        />
                                    </div>

                                    <div className="sm:col-span-2">
                                        <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Your (Manager) Override (₹)</label>
                                        <input 
                                            name="super_agent_override"
                                            type="number"
                                            required
                                            min="0"
                                            defaultValue={editingSlab?.super_agent_override}
                                            className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                                            placeholder="0.00"
                                        />
                                    </div>
                                </div>

                                <div className="bg-amber-50 border border-amber-100 rounded-xl p-3 flex gap-3">
                                    <ShieldAlert size={16} className="text-amber-600 shrink-0 mt-0.5" />
                                    <p className="text-[10px] text-amber-900 font-medium leading-relaxed">
                                        Updating this will NOT affect already entered commissions, but will be the default for all future leads of this capacity.
                                    </p>
                                </div>
                            </div>

                            <div className="mt-10 flex gap-4">
                                <button 
                                    type="submit"
                                    className="w-full py-4 rounded-2xl font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                                    disabled={saveMutation.isPending}
                                >
                                    {saveMutation.isPending ? (
                                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                    ) : (
                                        <>
                                            SAVE SETTINGS
                                            <ChevronRight size={20} />
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};
