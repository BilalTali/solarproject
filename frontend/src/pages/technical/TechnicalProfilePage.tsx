import { useState, useEffect } from 'react';
import { useAuthStore } from '@/store/authStore';
import { User, Phone, Save, Lock, Shield, Settings, Mail } from 'lucide-react';
import { useMutation } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import api from '@/services/axios';
import ChangePasswordForm from '@/components/shared/ChangePasswordForm';

type TabId = 'profile' | 'security';

export default function TechnicalProfilePage() {
    const { user, setUser } = useAuthStore();
    const [activeTab, setActiveTab] = useState<TabId>('profile');
    const [isEditing, setIsEditing] = useState(false);
    
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
            const res = await api.put('/enumerator/profile', data); // Roles like Technical/Enumerator use SharedProfileController
            return res.data;
        },
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                setIsEditing(false);
                toast.success('Technical profile updated');
            }
        },
        onError: (err: any) => {
            toast.error(err.response?.data?.message || 'Failed to update profile');
        }
    });

    return (
        <div className="max-w-6xl mx-auto space-y-8 animate-in fade-in duration-700">
            {/* Header */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-200">
                <div className="flex items-center gap-4">
                    <div className="w-14 h-14 rounded-2xl bg-orange-500 flex items-center justify-center shadow-2xl shadow-orange-100">
                        <User className="text-white w-7 h-7" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-display font-black text-slate-900 tracking-tight leading-none">Technical Hub</h1>
                        <p className="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mt-2">Manage your account & platform security</p>
                    </div>
                </div>
                {!isEditing && activeTab === 'profile' ? (
                    <button onClick={() => setIsEditing(true)} className="px-8 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-orange-600 hover:scale-105 transition-all shadow-xl">
                        Edit My Profile
                    </button>
                ) : isEditing ? (
                    <div className="flex gap-3">
                        <button onClick={() => setIsEditing(false)} className="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-100 rounded-2xl">Cancel</button>
                        <button onClick={() => updateProfileMutation.mutate(form)} className="px-8 py-3 bg-orange-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest flex items-center gap-2 hover:bg-orange-700 shadow-xl">
                            <Save size={14} /> Save Profile
                        </button>
                    </div>
                ) : null}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                {/* Left: Navigator */}
                <div className="lg:col-span-1 space-y-4">
                    <div className="bg-orange-500 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-orange-100">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl" />
                        <div className="relative z-10 flex flex-col items-center">
                            <div className="w-32 h-32 rounded-3xl bg-white/20 border-4 border-white/30 overflow-hidden shadow-2xl mb-6 flex items-center justify-center">
                                {user?.profile_photo_url ? (
                                    <img src={user.profile_photo_url} alt={user.name} className="w-full h-full object-cover" />
                                ) : (
                                    <User size={48} className="text-white" />
                                )}
                            </div>
                            <h3 className="text-xl font-display font-black tracking-tight text-center">{user?.name}</h3>
                            <div className="mt-2 bg-white/20 px-3 py-1 rounded-full border border-white/30 flex items-center gap-1.5">
                                <Shield size={12} className="text-white" />
                                <span className="text-[8px] font-black uppercase tracking-widest text-white">Technician</span>
                            </div>
                        </div>
                    </div>

                    <nav className="bg-white rounded-[2rem] border border-slate-200 p-2 shadow-sm space-y-1">
                        <NavItem id="profile" label="Personal Info" icon={<Settings size={16} />} active={activeTab === 'profile'} onClick={setActiveTab} />
                        <NavItem id="security" label="Account Security" icon={<Lock size={16} />} active={activeTab === 'security'} onClick={setActiveTab} />
                    </nav>
                </div>

                {/* Right: Content */}
                <div className="lg:col-span-3">
                    <div className="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden min-h-[500px] p-8 lg:p-10">
                        {activeTab === 'profile' && (
                            <div className="space-y-10 animate-in fade-in slide-in-from-bottom-4">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="p-2 bg-orange-50 rounded-xl text-orange-600"><User size={18} /></div>
                                    <h2 className="font-display font-black text-xl text-slate-800 tracking-tight">Identity Information</h2>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <ProfileBlock 
                                        label="Full Technician Name" 
                                        value={form.name} 
                                        editing={isEditing} 
                                        onChange={v => setForm({...form, name: v})} 
                                        icon={<User size={14} />} 
                                    />
                                    <ProfileBlock 
                                        label="Official Email" 
                                        value={form.email} 
                                        editing={isEditing} 
                                        onChange={v => setForm({...form, email: v})} 
                                        icon={<Mail size={14} />} 
                                    />
                                    <div className="space-y-1.5 opacity-60">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 flex items-center gap-1"><Phone size={12}/> Registered Mobile</label>
                                        <div className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-400 font-bold">{(user as any)?.mobile}</div>
                                        <p className="text-[8px] text-slate-400 font-bold uppercase tracking-tight ml-1">Contact Admin to change mobile</p>
                                    </div>
                                    <div className="space-y-1.5 opacity-60">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 flex items-center gap-1"><Shield size={12}/> Role Visibility</label>
                                        <div className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-400 font-bold uppercase tracking-widest">{(user as any)?.technician_type || 'Field Technician'}</div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeTab === 'security' && (
                            <div className="space-y-10 animate-in fade-in slide-in-from-bottom-4">
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="p-2 bg-red-50 rounded-xl text-red-600"><Lock size={18} /></div>
                                    <h2 className="font-display font-black text-xl text-slate-800 tracking-tight">Account Security</h2>
                                </div>
                                <div className="max-w-md">
                                    <ChangePasswordForm />
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

const NavItem = ({ id, label, icon, active, onClick }: { id: TabId, label: string, icon: React.ReactNode, active: boolean, onClick: (id: TabId) => void }) => (
    <button 
        onClick={() => onClick(id)}
        className={`w-full flex items-center gap-3 px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all ${active ? 'bg-orange-500 text-white shadow-lg shadow-orange-100' : 'text-slate-500 hover:bg-slate-50'}`}
    >
        {icon}
        <span>{label}</span>
    </button>
);

const ProfileBlock = ({ label, value, editing, onChange, icon }: { label: string, value: string, editing: boolean, onChange: (v: string) => void, icon: React.ReactNode }) => (
    <div className="space-y-1.5 w-full">
        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 flex items-center gap-1">{icon} {label}</label>
        {editing ? (
            <input 
                type="text" 
                value={value} 
                onChange={e => onChange(e.target.value)} 
                className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-800 font-bold focus:border-orange-500 focus:bg-white outline-none transition-all" 
            />
        ) : (
            <div className="w-full bg-slate-50 border-2 border-slate-50 rounded-2xl px-6 py-4 text-slate-800 font-bold">{value || '---'}</div>
        )}
    </div>
);
