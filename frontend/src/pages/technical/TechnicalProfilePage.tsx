import { useState, useEffect } from 'react';
import { useAuthStore } from '@/store/authStore';
import { User, Phone, Save, RefreshCcw, LogOut } from 'lucide-react';
import { useMutation } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import api from '@/services/axios';

export default function TechnicalProfilePage() {
    const { user, setUser } = useAuthStore();
    const [form, setForm] = useState({
        name: '',
        email: '',
    });

    useEffect(() => {
        if (user) {
            setForm({
                name: user.name || '',
                email: user.email || '',
            });
        }
    }, [user]);

    const updateProfileMutation = useMutation({
        mutationFn: async (data: any) => {
            const res = await api.put('/enumerator/profile', data); // Since SharedProfileController manages all sub-roles if registered
            return res.data;
        },
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                toast.success('Profile updated successfully');
            }
        },
        onError: (err: any) => {
            toast.error(err.response?.data?.message || 'Failed to update profile');
        }
    });

    return (
        <div className="max-w-2xl mx-auto space-y-8">
            <div>
                <h1 className="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                    <User className="text-orange-500" /> My Profile
                </h1>
                <p className="text-slate-600 text-sm">Manage your technical account details</p>
            </div>

            <div className="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
                <div>
                    <label className="text-[10px] font-black tracking-widest uppercase text-slate-400 mb-2 block">Full Name</label>
                    <input
                        type="text"
                        value={form.name}
                        onChange={e => setForm({ ...form, name: e.target.value })}
                        className="w-full px-4 py-3 rounded-xl border-2 border-slate-100 focus:border-orange-500 outline-none transition-all font-bold text-slate-800 bg-slate-50"
                    />
                </div>
                
                <div>
                    <label className="text-[10px] font-black tracking-widest uppercase text-slate-400 mb-2 block">Email Address</label>
                    <input
                        type="email"
                        value={form.email}
                        onChange={e => setForm({ ...form, email: e.target.value })}
                        className="w-full px-4 py-3 rounded-xl border-2 border-slate-100 focus:border-orange-500 outline-none transition-all font-bold text-slate-800 bg-slate-50"
                    />
                </div>

                <div>
                    <label className="text-[10px] font-black tracking-widest uppercase text-slate-400 mb-2 flex items-center gap-1"><Phone size={12}/> Registered Mobile Number</label>
                    <div className="w-full px-4 py-3 rounded-xl border-2 border-slate-100 text-slate-400 bg-slate-50 font-bold">
                        {(user as any)?.mobile}
                    </div>
                </div>
                
                <div>
                    <label className="text-[10px] font-black tracking-widest uppercase text-slate-400 mb-2 flex items-center gap-1">Role Type</label>
                    <div className="w-full px-4 py-3 rounded-xl border-2 border-slate-100 text-slate-400 bg-slate-50 font-bold uppercase">
                        {(user as any)?.technician_type || 'Field Technician'}
                    </div>
                </div>

                <div className="pt-4 flex justify-end">
                    <button
                        onClick={() => updateProfileMutation.mutate(form)}
                        disabled={updateProfileMutation.isPending}
                        className="flex items-center gap-2 px-6 py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 transition-all shadow-sm shadow-orange-200 disabled:opacity-50"
                    >
                        {updateProfileMutation.isPending ? <RefreshCcw className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    );
}
