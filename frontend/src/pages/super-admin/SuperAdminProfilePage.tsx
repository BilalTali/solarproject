import { useState, useEffect } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { User, Mail, Shield, BadgeCheck, Save, Camera, Settings, Key, AlertCircle, LayoutDashboard, Building2, Upload, Image } from 'lucide-react';
import { settingsApi } from '@/services/settings.api';
import { authApi } from '@/services/auth.api';
import { useAuthStore } from '@/hooks/store/authStore';
import api from '@/services/axios';
import toast from 'react-hot-toast';
import ChangePasswordForm from '@/components/shared/ChangePasswordForm';
import { useSettings } from '@/hooks/useSettings';

export default function SuperAdminProfilePage() {
    const queryClient = useQueryClient();
    const { user, setUser } = useAuthStore();
    const { } = useSettings();
    const [editing, setEditing] = useState(false);
    const [editForm, setEditForm] = useState({
        name: user?.name ?? '',
        email: user?.email ?? '',
    });

    // Branding State (Managed by unified 'editing')
    const [localBranding, setLocalBranding] = useState<Record<string, string>>({});
    const [pendingBrandingFiles, setPendingBrandingFiles] = useState<Record<string, File>>({});

    const { data: settingsData } = useQuery({
        queryKey: ['admin-settings'],
        queryFn: settingsApi.getSettings
    });

    useEffect(() => {
        if (settingsData?.success && settingsData.data) {
            const flat: Record<string, string> = { ...localBranding };
            Object.values(settingsData.data).flat().forEach(item => {
                flat[item.key] = item.value || '';
            });
            setLocalBranding(flat);
        }
    }, [settingsData]);

    const updateBrandingMutation = useMutation({
        mutationFn: settingsApi.updateSettings,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin-settings'] });
            queryClient.invalidateQueries({ queryKey: ['public-settings'] });
            toast.success('System branding persisted');
        }
    });

    const uploadFileMutation = useMutation({
        mutationFn: ({ key, file }: { key: string; file: File }) => settingsApi.uploadSettingsFile(key, file),
        onSuccess: (data, { key }) => {
            if (data?.data?.url) {
                const url: string = data.data.url;
                const relativePath = url.includes('/storage/') ? url.split('/storage/')[1] : url;
                setLocalBranding(prev => ({ ...prev, [key]: relativePath }));
            }
            queryClient.invalidateQueries({ queryKey: ['admin-settings'] });
            queryClient.invalidateQueries({ queryKey: ['public-settings'] });
        }
    });

    const uploadPhotoMutation = useMutation({
        mutationFn: authApi.uploadProfilePhoto,
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                toast.success('Profile photo updated');
            }
        },
        onError: () => toast.error('Failed to upload photo')
    });

    const updateProfileMutation = useMutation({
        mutationFn: async (data: { name: string; email: string }) => {
            // Admin prefix used as identified in api.php
            const res = await api.put('/admin/profile', data);
            return res.data;
        },
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                toast.success('Profile updated successfully');
                setEditing(false);
            }
        },
        onError: (err: any) => {
            const msg = err.response?.data?.message || 'Update failed';
            toast.error(msg);
        }
    });

    const handleSave = async () => {
        if (!editForm.name.trim() || !editForm.email.trim()) {
            return toast.error('Name and Email are required');
        }
        
        try {
            // 1. Personal Profile
            updateProfileMutation.mutate(editForm);

            // 2. Branding (if Super Admin)
            if (user?.role === 'super_admin') {
                const fileKeys = ['company_logo_2', 'company_favicon']; // Only Authority assets for SuperAdmin
                // Upload files first
                for (const key of fileKeys) {
                    const file = pendingBrandingFiles[key];
                    if (file) await uploadFileMutation.mutateAsync({ key, file });
                }

                // Update text fields
                const textKeys = ['company_registration_no', 'company_affiliated_with'];
                const settingsToSave = textKeys.map(k => ({ key: k, value: localBranding[k] || '' }));
                await updateBrandingMutation.mutateAsync(settingsToSave);
                
                setPendingBrandingFiles({});
            }
            
            setEditing(false);
            toast.success('Profile & Authority updated');
        } catch (err) {
            toast.error('Failed to sync changes');
        }
    };

    // Use handleSave unified above

    return (
        <div className="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            {/* Header Section */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-200">
                <div className="flex items-center gap-4">
                    <div className="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center shadow-indigo-100 shadow-xl">
                        <Settings className="text-white w-6 h-6" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-display font-black text-slate-900 tracking-tight leading-none">
                            Profile & Authority
                        </h1>
                        <p className="text-slate-400 text-xs font-black uppercase tracking-[0.2em] mt-2">{localBranding.company_affiliated_with || 'Master Identity Authority'}</p>
                    </div>
                </div>

                {!editing ? (
                    <button
                        onClick={() => setEditing(true)}
                        className="px-8 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition-all hover:scale-105 active:scale-95 shadow-xl shadow-slate-200"
                    >
                        Edit Root Profile
                    </button>
                ) : (
                    <div className="flex gap-3">
                        <button
                            onClick={() => setEditing(false)}
                            className="px-6 py-3 border border-slate-200 text-slate-500 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all"
                        >
                            Cancel
                        </button>
                        <button
                            onClick={handleSave}
                            disabled={updateProfileMutation.isPending}
                            className="flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition-all hover:scale-105 shadow-xl shadow-indigo-100 disabled:opacity-50"
                        >
                            <Save size={14} /> {updateProfileMutation.isPending ? 'Propagating...' : 'Save Changes'}
                        </button>
                    </div>
                )}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Left: Authority Card */}
                <div className="lg:col-span-1">
                    <div className="bg-slate-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-slate-300">
                        <div className="absolute top-0 right-0 w-40 h-40 bg-indigo-500/10 rounded-full -mr-20 -mt-20 blur-3xl pointer-events-none" />
                        
                        <div className="flex flex-col items-center relative z-10">
                            <div className="relative group">
                                <div className="w-40 h-40 rounded-[2.5rem] bg-slate-800 border-4 border-slate-800 overflow-hidden shadow-2xl">
                                    {user?.profile_photo_url ? (
                                        <img src={user.profile_photo_url} alt={user.name} className="w-full h-full object-cover transition-transform group-hover:scale-110" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center bg-indigo-600/20">
                                            <Shield size={60} className="text-indigo-400" />
                                        </div>
                                    )}
                                </div>
                                <div className="absolute inset-0 bg-slate-900/60 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 rounded-[2.5rem] cursor-pointer">
                                    <Camera className="w-8 h-8 text-white mb-2" />
                                    <span className="text-[10px] font-black uppercase tracking-widest text-white/80">Upload Photo</span>
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

                            <div className="mt-8 text-center">
                                <h3 className="text-2xl font-display font-black tracking-tight">{user?.name}</h3>
                                <div className="flex items-center justify-center gap-2 mt-2 bg-indigo-600/20 px-4 py-1.5 rounded-full border border-indigo-500/30">
                                    <BadgeCheck size={14} className="text-indigo-400" />
                                    <span className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-100">Root Administrator</span>
                                </div>
                            </div>

                            <div className="w-full mt-10 space-y-3 pb-4">
                                <AuthorityMeta label="Account Level" value="Level 0 (System Root)" />
                                <AuthorityMeta label="Access Control" value="Global Full Access" />
                                <AuthorityMeta label="Two-Factor" value="Standard" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right: Core Settings */}
                <div className="lg:col-span-2 space-y-8">
                    {/* Identity Module */}
                    <div className="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                        <div className="p-8 lg:p-10">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                {!editing ? (
                                    <>
                                        <StaticBlock icon={<User size={14} />} label="Administrator Name" value={user?.name} />
                                        <StaticBlock icon={<Mail size={14} />} label="Security Email" value={user?.email} />
                                        <StaticBlock icon={<Shield size={14} />} label="Primary Mobile" value={user?.mobile} />
                                        <StaticBlock icon={<LayoutDashboard size={14} />} label="Access Mode" value={localBranding.company_affiliated_with || 'Master Identity'} />
                                    </>
                                ) : (
                                    <>
                                        <InputBlock label="Full Legal Name" value={editForm.name} onChange={v => setEditForm({...editForm, name: v})} />
                                        <InputBlock label="Security Email" type="email" value={editForm.email} onChange={v => setEditForm({...editForm, email: v})} />
                                        <div className="md:col-span-2 p-6 bg-amber-50 rounded-3xl border border-amber-100 flex items-start gap-4">
                                            <AlertCircle className="text-amber-600 w-5 h-5 shrink-0 mt-1" />
                                            <div>
                                                <h4 className="font-bold text-amber-900 text-sm">Sensitive Update Notice</h4>
                                                <p className="text-amber-800/80 text-xs mt-1 leading-relaxed">
                                                    Updating your security email requires a new verification during the next login. Ensure you have access to this email address.
                                                </p>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Master Authority Module (Focus: Affiliation Logo & Registration) */}
                    <div className="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                        <div className="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="p-2 bg-white rounded-xl shadow-sm border border-slate-100">
                                    <Building2 className="w-4 h-4 text-slate-400" />
                                </div>
                                <h3 className="font-display font-black text-sm text-slate-800 uppercase tracking-widest">Master Identity Authority</h3>
                            </div>
                        </div>
                        <div className="p-8 lg:p-10">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                {!editing ? (
                                    <>
                                        <StaticBlock icon={<Shield size={14} />} label="Global Registration No" value={localBranding.company_registration_no} />
                                        <StaticBlock icon={<Building2 size={14} />} label="Affiliated With" value={localBranding.company_affiliated_with} />
                                        <div className="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-50">
                                            <StaticLogo label="Affiliation Logo (Master)" path={localBranding.company_logo_2} />
                                            <StaticLogo label="System Favicon" path={localBranding.company_favicon} />
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <InputBlock label="Global Registration No" value={localBranding.company_registration_no || ''} onChange={v => setLocalBranding({...localBranding, company_registration_no: v})} />
                                        <InputBlock label="Affiliated With" value={localBranding.company_affiliated_with || ''} onChange={v => setLocalBranding({...localBranding, company_affiliated_with: v})} />
                                        
                                        <div className="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-50">
                                            <FileUploadBlock 
                                                label="Affiliation Logo (Master)" 
                                                currentPath={localBranding.company_logo_2} 
                                                pendingFile={pendingBrandingFiles.company_logo_2}
                                                onSelect={f => setPendingBrandingFiles(p => ({...p, company_logo_2: f}))}
                                            />
                                            <FileUploadBlock 
                                                label="System Favicon" 
                                                currentPath={localBranding.company_favicon} 
                                                pendingFile={pendingBrandingFiles.company_favicon}
                                                onSelect={f => setPendingBrandingFiles(p => ({...p, company_favicon: f}))}
                                            />
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>

                    {!editing && (
                        <div className="animate-in fade-in slide-in-from-bottom-2 duration-1000">
                            <div className="mb-6 flex items-center gap-3 px-4">
                                <div className="p-2 bg-indigo-600 rounded-xl shadow-indigo-100 shadow-lg">
                                    <Key className="w-4 h-4 text-white" />
                                </div>
                                <h3 className="font-display font-black text-sm text-slate-800 uppercase tracking-widest">Security Override</h3>
                            </div>
                            <ChangePasswordForm />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

const StaticBlock = ({ icon, label, value }: { icon: React.ReactNode; label: string; value?: string }) => (
    <div className="space-y-2 group">
        <div className="flex items-center gap-2 text-slate-400 group-hover:text-indigo-600 transition-colors">
            {icon}
            <label className="text-[10px] font-black uppercase tracking-[0.2em]">{label}</label>
        </div>
        <p className="font-display font-black text-slate-800 text-xl border-b-2 border-slate-50 pb-2 group-hover:border-indigo-100 transition-all">{value || 'Not Configured'}</p>
    </div>
);

const InputBlock = ({ label, value, onChange, type = "text" }: { label: string; value: string; onChange: (v: string) => void; type?: string }) => (
    <div className="space-y-3">
        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">{label}</label>
        <input
            type={type}
            value={value}
            onChange={e => onChange(e.target.value)}
            className="w-full bg-slate-50/50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-slate-800 font-bold focus:border-indigo-600 focus:bg-white outline-none transition-all shadow-inner"
        />
    </div>
);

const AuthorityMeta = ({ label, value }: { label: string; value: string }) => (
    <div className="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5 group hover:border-indigo-500/30 transition-all">
        <span className="text-[10px] font-black text-white/40 uppercase tracking-widest group-hover:text-white/60">{label}</span>
        <span className="text-[10px] font-black text-indigo-400 uppercase tracking-widest group-hover:text-indigo-300">{value}</span>
    </div>
);

const getFileUrl = (path: string | null | undefined) => {
    if (!path) return '';
    if (path.startsWith('http') || path.startsWith('blob:') || path.startsWith('data:')) return path;
    const baseUrl = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1').split('/api/v1')[0];
    return `${baseUrl}/storage/${path}`;
};

const StaticLogo = ({ label, path }: { label: string; path?: string }) => (
    <div className="space-y-4">
        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{label}</label>
        <div className="h-24 w-full rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-4">
            {path ? (
                <img src={getFileUrl(path)} alt={label} className="max-h-full max-w-full object-contain" />
            ) : (
                <Image className="text-slate-200" size={30} />
            )}
        </div>
    </div>
);

const FileUploadBlock = ({ label, currentPath, pendingFile, onSelect }: { label: string; currentPath?: string; pendingFile?: File; onSelect: (f: File) => void }) => {
    const previewUrl = pendingFile ? URL.createObjectURL(pendingFile) : getFileUrl(currentPath);
    return (
        <div className="space-y-4">
            <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{label}</label>
            <div className="relative group">
                <div className="h-32 w-full rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center p-4 transition-all group-hover:border-indigo-400">
                    {previewUrl ? (
                        <div className="relative h-full w-full flex items-center justify-center">
                            <img src={previewUrl} alt={label} className="max-h-full max-w-full object-contain" />
                            {pendingFile && <div className="absolute top-0 right-0 bg-indigo-600 text-white text-[8px] px-2 py-0.5 rounded-full font-black uppercase">Pending</div>}
                        </div>
                    ) : (
                        <Upload className="text-slate-300 group-hover:text-indigo-400 transition-colors" size={30} />
                    )}
                    <input type="file" className="absolute inset-0 opacity-0 cursor-pointer" onChange={e => e.target.files?.[0] && onSelect(e.target.files[0])} />
                </div>
            </div>
        </div>
    );
};
