import React, { useState } from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { superAgentApi } from '@/api/superAgent.api';
import { useSettings } from '@/hooks/useSettings';

interface Props {
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
}

export const AddAgentModal: React.FC<Props> = ({ isOpen, onClose, onSuccess }) => {
    const queryClient = useQueryClient();
    const { companyName } = useSettings();
    const [form, setForm] = useState({
        name: '', mobile: '', email: '', district: '', state: '',
        whatsapp_number: '', area: '', password: 'Welcome@123',
    });

    const mutation = useMutation({
        mutationFn: () => superAgentApi.createAgent(form),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['sa-team'] });
            onSuccess();
            onClose();
            setForm({ name: '', mobile: '', email: '', district: '', state: '', whatsapp_number: '', area: '', password: 'Welcome@123' });
        },
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) =>
        setForm(prev => ({ ...prev, [e.target.name]: e.target.value }));

    if (!isOpen) return null;

    const fieldCls = 'w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500';
    const labelCls = 'block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide';

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden max-h-[90vh] flex flex-col">
                <div className="px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
                    <h3 className="text-lg font-semibold">Add Agent to Team</h3>
                    <p className="text-purple-200 text-sm mt-0.5">Agent requires Admin approval before they can log in</p>
                </div>
                <div className="p-6 overflow-y-auto space-y-4">
                    <div className="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2">
                        <span className="text-amber-500">ℹ</span>
                        <p className="text-amber-700 text-xs">This agent will be created with <strong>Pending</strong> status. Admin must approve before they can access the system.</p>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={labelCls}>Full Name *</label>
                            <input name="name" value={form.name} onChange={handleChange} required className={fieldCls} placeholder="Agent full name" />
                        </div>
                        <div>
                            <label className={labelCls}>Mobile *</label>
                            <input name="mobile" value={form.mobile} onChange={handleChange} required className={fieldCls} placeholder="10-digit mobile" maxLength={10} />
                        </div>
                        <div>
                            <label className={labelCls}>Email</label>
                            <input name="email" type="email" value={form.email} onChange={handleChange} className={fieldCls} placeholder={`agent@${companyName.toLowerCase().replace(/\s+/g, '')}.in`} />
                        </div>
                        <div>
                            <label className={labelCls}>WhatsApp</label>
                            <input name="whatsapp_number" value={form.whatsapp_number} onChange={handleChange} className={fieldCls} placeholder="WhatsApp number" maxLength={10} />
                        </div>
                        <div>
                            <label className={labelCls}>District *</label>
                            <input name="district" value={form.district} onChange={handleChange} required className={fieldCls} placeholder="District" />
                        </div>
                        <div>
                            <label className={labelCls}>State *</label>
                            <input name="state" value={form.state} onChange={handleChange} required className={fieldCls} placeholder="State" />
                        </div>
                        <div className="col-span-2">
                            <label className={labelCls}>Area / Locality</label>
                            <input name="area" value={form.area} onChange={handleChange} className={fieldCls} placeholder="Area or locality" />
                        </div>
                        <div className="col-span-2">
                            <label className={labelCls}>Temporary Password</label>
                            <input name="password" value={form.password} onChange={handleChange} className={fieldCls} />
                            <p className="text-xs text-gray-400 mt-1">Agent should change this after first login.</p>
                        </div>
                    </div>
                    {mutation.isError && (
                        <p className="text-red-600 text-sm">{(mutation.error as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'An error occurred'}</p>
                    )}
                </div>
                <div className="px-6 pb-6 flex gap-3 justify-end border-t pt-4">
                    <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium">Cancel</button>
                    <button
                        onClick={() => mutation.mutate()}
                        disabled={!form.name || !form.mobile || !form.district || !form.state || mutation.isPending}
                        className="px-5 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50 hover:bg-purple-700 transition-colors"
                    >
                        {mutation.isPending ? 'Adding…' : 'Add Agent (Pending Approval)'}
                    </button>
                </div>
            </div>
        </div>
    );
};
