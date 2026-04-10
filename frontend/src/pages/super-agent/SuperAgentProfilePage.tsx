// @ts-nocheck
import { useState, useEffect } from 'react';
import { useMutation } from '@tanstack/react-query';
import {
    User, Phone, MapPin, Building2, 
    Calendar, Shield, BadgeCheck, Save,
    Camera, Mail, FileText, Lock, Download,
    Briefcase, Star, Check, RefreshCcw
} from 'lucide-react';
import { superAgentApi } from '@/services/superAgent.api';
import { authApi } from '@/services/auth.api';
import { useAuthStore } from '@/hooks/store/authStore';
import toast from 'react-hot-toast';
import { STATE_DISTRICTS, INDIAN_STATES } from '@/constants/locationData';
import ChangePasswordForm from '@/components/shared/ChangePasswordForm';
import DownloadJoiningLetterButton from '@/components/shared/DownloadJoiningLetterButton';
import { DownloadIdCardButton } from '@/components/shared/DownloadIdCardButton';

type ProfileTab = 'identity' | 'location' | 'documents' | 'security';

export function SuperAgentProfilePage() {
    const { user, setUser } = useAuthStore();
    const [editing, setEditing] = useState(false);
    const [activeTab, setActiveTab] = useState<ProfileTab>('identity');
    
    const [editForm, setEditForm] = useState({
        whatsapp_number: user?.whatsapp_number ?? '',
        father_name: (user as any)?.father_name ?? '',
        dob: (user as any)?.dob ? (user as any).dob.split('T')[0] : '',
        blood_group: (user as any)?.blood_group ?? '',
        state: user?.state ?? '',
        district: user?.district ?? '',
        area: user?.area ?? '',
    });

    useEffect(() => {
        if (user) {
            setEditForm({
                whatsapp_number: user.whatsapp_number ?? '',
                father_name: (user as any).father_name ?? '',
                dob: (user as any).dob ? (user as any).dob.split('T')[0] : '',
                blood_group: (user as any).blood_group ?? '',
                state: user.state ?? '',
                district: user.district ?? '',
                area: user.area ?? '',
            });
        }
    }, [user]);

    const uploadPhotoMutation = useMutation({
        mutationFn: authApi.uploadProfilePhoto,
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                toast.success('Profile photo updated');
            }
        },
        onError: () => {
            toast.error('Failed to upload photo');
        }
    });

    const updateProfileMutation = useMutation({
        mutationFn: superAgentApi.updateProfile,
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                toast.success('Profile credentials synchronized');
                setEditing(false);
            }
        },
        onError: (err: any) => {
            const msg = err.response?.data?.message || 'Failed to update profile';
            toast.error(msg);
        }
    });

    const handleSave = () => {
        updateProfileMutation.mutate(editForm);
    };

    return (
        <div className="max-w-6xl mx-auto space-y-8 animate-in fade-in duration-700 pb-20">
            {/* Header Area */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-200">
                <div className="flex items-center gap-4">
                    <div className="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center shadow-2xl shadow-indigo-100">
                        <Briefcase className="text-white w-7 h-7" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-display font-black text-slate-900 tracking-tight leading-none">Manager Command Center</h1>
                        <p className="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mt-2">Professional Identity & Resources</p>
                    </div>
                </div>
                {!editing ? (
                    <button onClick={() => setEditing(true)} className="px-8 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 hover:scale-105 transition-all shadow-xl">
                        Edit Manager Profile
                    </button>
                ) : (
                    <div className="flex gap-3">
                        <button onClick={() => setEditing(false)} className="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-100 rounded-2xl transition-all">Cancel</button>
                        <button 
                            disabled={updateProfileMutation.isPending}
                            onClick={handleSave} 
                            className="px-8 py-3 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest flex items-center gap-2 hover:bg-indigo-700 shadow-xl transition-all"
                        >
                            {updateProfileMutation.isPending ? <RefreshCcw size={14} className="animate-spin" /> : <Save size={14} />}
                            {updateProfileMutation.isPending ? 'Saving...' : 'Save Profile'}
                        </button>
                    </div>
                )}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                {/* Left Sidebar: Profile Identity */}
                <div className="lg:col-span-1 space-y-4">
                    <div className="bg-slate-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-indigo-100">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -mr-16 -mt-16 blur-2xl" />
                        <div className="relative z-10 flex flex-col items-center">
                            <div className="relative group">
                                <div className="w-32 h-32 rounded-3xl bg-slate-800 border-4 border-slate-800 overflow-hidden shadow-2xl mb-6 relative">
                                    {user?.profile_photo_url ? (
                                        <img src={user.profile_photo_url} alt={user.name} className="w-full h-full object-cover transition-transform group-hover:scale-110" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center bg-indigo-600/20 text-indigo-400 font-black text-4xl">
                                            {user?.name.charAt(0)}
                                        </div>
                                    )}
                                    <div className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Camera className="text-white" size={24} />
                                        <input 
                                            type="file" 
                                            className="absolute inset-0 opacity-0 cursor-pointer" 
                                            accept="image/*" 
                                            onChange={(e) => {
                                                const file = e.target.files?.[0];
                                                if (file) uploadPhotoMutation.mutate(file);
                                            }} 
                                        />
                                    </div>
                                </div>
                            </div>
                            <h3 className="text-xl font-display font-black tracking-tight text-center">{user?.name}</h3>
                            <div className="mt-2 bg-indigo-600/30 px-3 py-1 rounded-full border border-indigo-500/30 flex items-center gap-1.5">
                                <BadgeCheck size={12} className="text-indigo-400" />
                                <span className="text-[8px] font-black uppercase tracking-widest text-indigo-100">{user?.super_agent_code || 'Senior Manager'}</span>
                            </div>
                        </div>
                    </div>

                    <nav className="bg-white rounded-[2rem] border border-slate-200 p-2 shadow-sm space-y-1">
                        <NavTab id="identity" label="Manager Identity" icon={<User size={16} />} active={activeTab === 'identity'} onClick={setActiveTab} />
                        <NavTab id="location" label="Serving Location" icon={<MapPin size={16} />} active={activeTab === 'location'} onClick={setActiveTab} />
                        <NavTab id="documents" label="Official Resources" icon={<FileText size={16} />} active={activeTab === 'documents'} onClick={setActiveTab} />
                        <NavTab id="security" label="System Security" icon={<Lock size={16} />} active={activeTab === 'security'} onClick={setActiveTab} />
                    </nav>
                </div>

                {/* Right: Tab Content Area */}
                <div className="lg:col-span-3 space-y-8">
                    <div className="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden min-h-[500px]">
                        <div className="p-8 lg:p-10">
                            {activeTab === 'identity' && (
                                <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-indigo-50 rounded-xl text-indigo-600"><User size={18} /></div>
                                        <h2 className="font-display font-black text-xl text-slate-800 tracking-tight">Personal Credentials</h2>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <DisplayField label="Full Legal Name" value={user?.name} icon={<User size={14} />} />
                                        <DisplayField label="Primary Mobile" value={user?.mobile} icon={<Phone size={14} />} />
                                        
                                        <EditField 
                                            label="WhatsApp Number" 
                                            value={editForm.whatsapp_number} 
                                            editing={editing} 
                                            onChange={v => setEditForm(p => ({ ...p, whatsapp_number: v }))} 
                                            icon={<Phone size={14} />} 
                                            placeholder="10-digit number"
                                        />
                                        <EditField 
                                            label="Father's Name" 
                                            value={editForm.father_name} 
                                            editing={editing} 
                                            onChange={v => setEditForm(p => ({ ...p, father_name: v }))} 
                                            icon={<User size={14} />} 
                                            placeholder="S/o or D/o name"
                                        />
                                        
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Date of Birth</label>
                                            {editing ? (
                                                <input 
                                                    type="date" 
                                                    value={editForm.dob} 
                                                    onChange={e => setEditForm(p => ({ ...p, dob: e.target.value }))}
                                                    className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-800 font-bold focus:border-indigo-600 focus:bg-white outline-none transition-all"
                                                />
                                            ) : (
                                                <div className="px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl flex items-center gap-3 text-slate-800 font-bold">
                                                    <Calendar size={14} className="text-slate-300" />
                                                    {user?.dob ? new Date(user.dob).toLocaleDateString('en-IN') : 'Not Provided'}
                                                </div>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Blood Group</label>
                                            {editing ? (
                                                <select 
                                                    value={editForm.blood_group} 
                                                    onChange={e => setEditForm(p => ({ ...p, blood_group: e.target.value }))}
                                                    className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-800 font-bold focus:border-indigo-600 focus:bg-white outline-none transition-all appearance-none"
                                                >
                                                    <option value="">Select...</option>
                                                    {['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'].map(bg => <option key={bg} value={bg}>{bg}</option>)}
                                                </select>
                                            ) : (
                                                <div className="px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl flex items-center gap-3 text-slate-800 font-bold">
                                                    <Star size={14} className="text-slate-300" />
                                                    {(user as any)?.blood_group || 'Not Provided'}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {activeTab === 'location' && (
                                <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-indigo-50 rounded-xl text-indigo-600"><MapPin size={18} /></div>
                                        <h2 className="font-display font-black text-xl text-slate-800 tracking-tight">Serving Territory Authority</h2>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">State Authority</label>
                                            {editing ? (
                                                <select 
                                                    value={editForm.state} 
                                                    onChange={e => setEditForm(p => ({ ...p, state: e.target.value, district: '' }))}
                                                    className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-800 font-bold focus:border-indigo-600 focus:bg-white outline-none transition-all"
                                                >
                                                    <option value="">Select State</option>
                                                    {INDIAN_STATES.map(s => <option key={s} value={s}>{s}</option>)}
                                                </select>
                                            ) : (
                                                <div className="px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl text-slate-800 font-bold">{user?.state || 'Not Provided'}</div>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">District Boundary</label>
                                            {editing ? (
                                                <select 
                                                    value={editForm.district} 
                                                    onChange={e => setEditForm(p => ({ ...p, district: e.target.value }))}
                                                    disabled={!editForm.state}
                                                    className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-800 font-bold focus:border-indigo-600 focus:bg-white outline-none transition-all disabled:opacity-50"
                                                >
                                                    <option value="">Select District</option>
                                                    {(STATE_DISTRICTS[editForm.state] || []).map(d => <option key={d} value={d}>{d}</option>)}
                                                </select>
                                            ) : (
                                                <div className="px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl text-slate-800 font-bold">{user?.district || 'Not Provided'}</div>
                                            )}
                                        </div>

                                        <div className="md:col-span-2">
                                            <EditField 
                                                label="Assigned Serving Area" 
                                                value={editForm.area} 
                                                editing={editing} 
                                                onChange={v => setEditForm(p => ({ ...p, area: v }))} 
                                                icon={<MapPin size={14} />} 
                                                placeholder="Block, Taluka or Area Name"
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {activeTab === 'documents' && (
                                <div className="space-y-12 animate-in fade-in slide-in-from-bottom-4">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-indigo-50 rounded-xl text-indigo-600"><FileText size={18} /></div>
                                        <h2 className="font-display font-black text-xl text-slate-800 tracking-tight">Official Credentials Vault</h2>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        {/* ID Card Block */}
                                        <div className="bg-slate-50 rounded-[2.5rem] p-8 border border-slate-100 space-y-6 flex flex-col items-center text-center">
                                            <div className="w-20 h-20 rounded-3xl bg-white shadow-xl flex items-center justify-center text-indigo-600">
                                                <Download size={32} />
                                            </div>
                                            <div>
                                                <h4 className="text-xl font-display font-black text-slate-900 tracking-tight">Professional I-Card</h4>
                                                <p className="text-slate-500 text-xs font-bold leading-relaxed mt-2">
                                                    Download your official platform identity card with secure QR verification.
                                                </p>
                                            </div>
                                            <DownloadIdCardButton className="w-full h-14 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl" />
                                        </div>

                                        {/* Joining Letter Block */}
                                        <div className="bg-slate-50 rounded-[2.5rem] p-8 border border-slate-100 space-y-6 flex flex-col items-center text-center">
                                            <div className="w-20 h-20 rounded-3xl bg-white shadow-xl flex items-center justify-center text-indigo-600">
                                                <FileText size={32} />
                                            </div>
                                            <div>
                                                <h4 className="text-xl font-display font-black text-slate-900 tracking-tight">Joining Authorization</h4>
                                                <p className="text-slate-500 text-xs font-bold leading-relaxed mt-2">
                                                    Official appointment letter authorizing your position as a registered Senior Manager.
                                                </p>
                                            </div>
                                            <DownloadJoiningLetterButton user={user!} variant="primary" className="w-full h-14 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl" />
                                        </div>
                                    </div>

                                    <div className="p-6 bg-indigo-50 rounded-3xl flex items-start gap-4 border border-indigo-100 italic">
                                        <Shield className="text-indigo-600 shrink-0 mt-1" size={18} />
                                        <p className="text-xs text-indigo-800 font-bold leading-relaxed">
                                            These documents are cryptographically signed. Any tampering with the printed details or QR codes will invalidate the credentials during field verification.
                                        </p>
                                    </div>
                                </div>
                            )}

                            {activeTab === 'security' && (
                                <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-indigo-50 rounded-xl text-indigo-600"><Lock size={18} /></div>
                                        <h2 className="font-display font-black text-xl text-slate-800 tracking-tight">Account Access & Security</h2>
                                    </div>
                                    <div className="max-w-md">
                                        <ChangePasswordForm />
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {!editing && activeTab === 'identity' && (
                        <div className="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-indigo-200 relative overflow-hidden group">
                            <div className="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32 blur-3xl transition-transform group-hover:scale-110" />
                            <div className="relative z-10 flex items-start gap-6">
                                <div className="p-4 bg-white/10 rounded-3xl backdrop-blur-md">
                                    <Shield className="text-indigo-200 w-8 h-8" />
                                </div>
                                <div className="space-y-4">
                                    <h4 className="text-2xl font-display font-black tracking-tight">Profile Integrity Sync</h4>
                                    <p className="text-indigo-100 text-sm leading-relaxed max-w-xl font-medium opacity-80">
                                        Your identity is professional and verified. Ensuring your Father's Name and DOB matches your IDs is critical for the generation of valid official documents.
                                    </p>
                                    <div className="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-indigo-300">
                                        <Check size={14} /> End-to-end Encrypted
                                        <span className="opacity-30">|</span>
                                        <Check size={14} /> Verified Professional
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

const NavTab = ({ id, label, icon, active, onClick }: { id: any, label: string, icon: React.ReactNode, active: boolean, onClick: (id: any) => void }) => (
    <button 
        onClick={() => onClick(id)}
        className={`w-full flex items-center gap-3 px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all ${active ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'}`}
    >
        {icon}
        <span>{label}</span>
    </button>
);

const DisplayField = ({ label, value, icon }: { label: string, value?: string, icon: React.ReactNode }) => (
    <div className="space-y-2">
        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{label}</label>
        <div className="px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl flex items-center gap-3 text-slate-800 font-bold overflow-hidden">
            <span className="text-slate-300">{icon}</span>
            <span className="truncate">{value || '---'}</span>
        </div>
    </div>
);

const EditField = ({ label, value, editing, onChange, icon, placeholder, type = "text" }: { label: string, value: string, editing: boolean, onChange: (v: string) => void, icon: React.ReactNode, placeholder?: string, type?: string }) => (
    <div className="space-y-2">
        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{label}</label>
        {editing ? (
            <input 
                type={type} 
                value={value} 
                onChange={e => onChange(e.target.value)} 
                placeholder={placeholder}
                className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-800 font-bold focus:border-indigo-600 focus:bg-white outline-none transition-all" 
            />
        ) : (
            <div className="px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl flex items-center gap-3 text-slate-800 font-bold overflow-hidden">
                <span className="text-slate-300">{icon}</span>
                <span className="truncate">{value || 'Not Provided'}</span>
            </div>
        )}
    </div>
);
