import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { CommissionSlab } from '../../types';
import { slabsApi } from '../../api/slabs.api';
import { 
    Plus, Search, Edit2, Trash2, 
    Settings, Power, ChevronRight, X 
} from 'lucide-react';
import { toast } from 'react-hot-toast';
import SEOHead from '../../components/shared/SEOHead';
import LoadingSpinner from '../../components/shared/LoadingSpinner';

export const AdminCommissionSlabsPage: React.FC = () => {
    const queryClient = useQueryClient();
    const [searchTerm, setSearchTerm] = useState('');
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingSlab, setEditingSlab] = useState<CommissionSlab | null>(null);

    const { data: slabsResponse, isLoading } = useQuery({
        queryKey: ['admin-commission-slabs'],
        queryFn: slabsApi.admin.getAll
    });

    const createMutation = useMutation({
        mutationFn: slabsApi.admin.create,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin-commission-slabs'] });
            toast.success('Commission slab created');
            closeModal();
        },
        onError: (err: any) => {
            toast.error(err.response?.data?.message || 'Failed to create slab');
        }
    });

    const updateMutation = useMutation({
        mutationFn: ({ id, payload }: { id: number, payload: Partial<CommissionSlab> }) => 
            slabsApi.admin.update(id, payload),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin-commission-slabs'] });
            toast.success('Commission slab updated');
            closeModal();
        },
        onError: (err: any) => {
            toast.error(err.response?.data?.message || 'Failed to update slab');
        }
    });

    const deleteMutation = useMutation({
        mutationFn: slabsApi.admin.delete,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin-commission-slabs'] });
            toast.success('Commission slab deleted');
        }
    });

    const openModal = (slab: CommissionSlab | null = null) => {
        setEditingSlab(slab);
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingSlab(null);
    };

    const handleDelete = (id: number) => {
        if (window.confirm('Delete this commission slab? Lead calculations using this capacity may be affected.')) {
            deleteMutation.mutate(id);
        }
    };

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const payload = {
            capacity: formData.get('capacity') as string,
            label: formData.get('label') as string,
            agent_commission: Number(formData.get('agent_commission')),
            super_agent_override: Number(formData.get('super_agent_override')),
            description: formData.get('description') as string,
            is_active: formData.get('is_active') === 'on'
        };

        if (editingSlab) {
            updateMutation.mutate({ id: editingSlab.id, payload });
        } else {
            createMutation.mutate(payload);
        }
    };

    const slabs = slabsResponse?.data || [];
    const filteredSlabs = slabs.filter(s => 
        s.label.toLowerCase().includes(searchTerm.toLowerCase()) || 
        s.capacity.toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (isLoading) return <LoadingSpinner />;

    return (
        <div className="p-6">
            <SEOHead title="Commission Slabs | Admin" />

            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 className="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <Settings className="text-indigo-600" />
                        Commission Slabs
                    </h1>
                    <p className="text-slate-500 font-medium">Define lead payout rates based on solar plant capacity (kW)</p>
                </div>
                <button 
                    onClick={() => openModal()}
                    className="flex items-center justify-center gap-2 bg-indigo-600 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-100 transition-all hover:bg-indigo-700 active:scale-95"
                >
                    <Plus size={20} />
                    Add New Slab
                </button>
            </div>

            {/* Search */}
            <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm mb-6 flex items-center">
                <div className="relative flex-grow max-w-md">
                    <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                    <input 
                        type="text" 
                        placeholder="Search slabs..."
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
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Capacity & Label</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">BDE Commission</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">SA Override</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {filteredSlabs.map((slab) => (
                                <tr key={slab.id} className="hover:bg-slate-50/30 transition-colors group">
                                    <td className="px-6 py-4">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs shrink-0 uppercase">
                                                {slab.capacity}
                                            </div>
                                            <div>
                                                <p className="font-bold text-slate-900 leading-tight">{slab.label}</p>
                                                <p className="text-[10px] text-slate-400 font-mono uppercase tracking-tighter">System ID: #{slab.id}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <div className="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-black text-sm">
                                            ₹{Number(slab.agent_commission).toLocaleString('en-IN')}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <div className="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 font-black text-sm">
                                            ₹{Number(slab.super_agent_override).toLocaleString('en-IN')}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4">
                                        <p className="text-xs text-slate-500 font-medium max-w-xs">{slab.description || 'No description provided'}</p>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black tracking-tight ${
                                            slab.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'
                                        }`}>
                                            <Power size={10} className={slab.is_active ? 'text-emerald-500' : 'text-slate-400'} />
                                            {slab.is_active ? 'ACTIVE' : 'INACTIVE'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <div className="flex items-center justify-end gap-2">
                                            <button 
                                                onClick={() => openModal(slab)}
                                                className="p-2 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all shadow-sm active:scale-95"
                                            >
                                                <Edit2 size={16} />
                                            </button>
                                            <button 
                                                onClick={() => handleDelete(slab.id)}
                                                className="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all shadow-sm active:scale-95"
                                            >
                                                <Trash2 size={16} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {filteredSlabs.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-6 py-20 text-center text-slate-400 font-medium italic">
                                        No commission slabs found matching your search.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal */}
            {isModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={closeModal} />
                    <div className="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden">
                        <div className="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                            <h2 className="text-xl font-black text-slate-900 tracking-tight">
                                {editingSlab ? 'Edit Commission Slab' : 'New Commission Slab'}
                            </h2>
                            <button onClick={closeModal} className="text-slate-400 hover:text-slate-600 transition-colors">
                                <X size={24} />
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="p-8">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Capacity Tag (Unique)</label>
                                    <input 
                                        name="capacity"
                                        required
                                        disabled={!!editingSlab}
                                        defaultValue={editingSlab?.capacity}
                                        className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium disabled:bg-slate-50 disabled:text-slate-400"
                                        placeholder="e.g., 3kw"
                                    />
                                    <p className="text-[10px] text-slate-400 mt-1 ml-1 italic">Used for internal mapping (immutable after creation)</p>
                                </div>

                                <div>
                                    <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Display Label</label>
                                    <input 
                                        name="label"
                                        required
                                        defaultValue={editingSlab?.label}
                                        className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                                        placeholder="e.g., 3 kW Solar Plant"
                                    />
                                </div>

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
                                    <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">SA Override (₹)</label>
                                    <input 
                                        name="super_agent_override"
                                        type="number"
                                        min="0"
                                        defaultValue={editingSlab?.super_agent_override}
                                        className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                                        placeholder="0.00"
                                    />
                                </div>

                                <div className="md:col-span-2">
                                    <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Internal Description</label>
                                    <textarea 
                                        name="description"
                                        rows={2}
                                        defaultValue={editingSlab?.description || ''}
                                        className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium"
                                        placeholder="Admin notes about this pricing slab..."
                                    />
                                </div>

                                <div className="md:col-span-2">
                                    <div className="flex items-center gap-3">
                                        <input 
                                            name="is_active"
                                            type="checkbox" 
                                            id="is_active_slab"
                                            defaultChecked={editingSlab?.is_active ?? true}
                                            className="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <label htmlFor="is_active_slab" className="text-sm font-bold text-slate-700">Mark as Active (Available for selection in tools)</label>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-10 flex gap-4">
                                <button 
                                    type="button" 
                                    onClick={closeModal}
                                    className="flex-grow py-4 rounded-2xl font-black text-slate-500 bg-slate-100 hover:bg-slate-200 transition-all active:scale-[0.98]"
                                >
                                    CANCEL
                                </button>
                                <button 
                                    type="submit"
                                    className="flex-[2] py-4 rounded-2xl font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                                    disabled={createMutation.isPending || updateMutation.isPending}
                                >
                                    {(createMutation.isPending || updateMutation.isPending) ? (
                                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                    ) : (
                                        <>
                                            {editingSlab ? 'UPDATE SLAB' : 'CREATE SLAB'}
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
