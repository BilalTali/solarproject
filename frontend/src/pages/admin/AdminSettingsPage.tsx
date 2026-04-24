import React, { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
    Settings as SettingsIcon, Building2, Globe, Save, RefreshCcw,
    User, CreditCard, Image, Trophy, MessageSquare,
    Upload, FileText, ExternalLink, Camera, Phone, MapPin, 
    Calendar, Lock, Star
} from 'lucide-react';
import toast from 'react-hot-toast';
import { settingsApi } from '@/services/settings.api';
import { authApi } from '@/services/auth.api';
import { useAuthStore } from '@/store/authStore';
import { AchievementManager } from '@/components/admin/AchievementManager';
import { FeedbackManager } from '@/components/admin/FeedbackManager';
import api from '@/services/axios';
import ChangePasswordForm from '@/components/shared/ChangePasswordForm';
import MobileInput from '@/components/shared/MobileInput';
import { useSettings } from '@/hooks/useSettings';
import { STATE_DISTRICTS, INDIAN_STATES } from '@/constants/locationData';
import SuperAdminCrmOptionsPage from '@/pages/super-admin/SuperAdminCrmOptionsPage';

type TabId = 'company' | 'branding' | 'achievements' | 'feedback' | 'portal' | 'icard' | 'profile' | 'letter' | 'incentive' | 'crm_options';

// ─── Standalone sub-components ───

function Field({ k, label, type = 'text', value, onChange, placeholder }: {
    k: string; label: string; type?: string;
    value: string; onChange: (key: string, val: string) => void;
    placeholder?: string;
}) {
    return (
        <div className="space-y-1.5">
            <label className="text-xs font-bold text-slate-600 uppercase tracking-wider">{label}</label>
            {type === 'textarea' ? (
                <textarea
                    value={value}
                    onChange={e => onChange(k, e.target.value)}
                    placeholder={placeholder}
                    className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm min-h-[100px]"
                />
            ) : type === 'tel' ? (
                <MobileInput
                    label={label}
                    value={value || ''}
                    onChange={val => onChange(k, val)}
                    placeholder={placeholder}
                />
            ) : (
                <input
                    type={type}
                    value={value || ''}
                    onChange={e => onChange(k, e.target.value)}
                    placeholder={placeholder}
                    className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm"
                />
            )}
        </div>
    );
}

const getFileUrl = (path: string | null | undefined) => {
    if (!path) return '';
    if (path.startsWith('http') || path.startsWith('blob:') || path.startsWith('data:')) return path;
    const baseUrl = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1').split('/api/v1')[0];
    return `${baseUrl}/storage/${path}`;
};

function FileUploadField({ settingKey, label, accept, currentUrl, pendingFile, onSelect, disabled }: {
    settingKey: string; label: string; accept: string; currentUrl?: string;
    pendingFile?: File;
    onSelect: (key: string, file: File) => void;
    disabled?: boolean;
}) {
    const previewUrl = pendingFile ? URL.createObjectURL(pendingFile) : getFileUrl(currentUrl);

    return (
        <div className="space-y-2">
            <label className="text-xs font-bold text-slate-600 uppercase tracking-wider">{label}</label>
            {(previewUrl || pendingFile) && (
                <div className="rounded-xl overflow-hidden border border-slate-100 h-24 w-auto flex items-center justify-center bg-slate-50 relative group">
                    {accept.includes('video') ? (
                        <video src={previewUrl} className="h-full" muted autoPlay loop playsInline />
                    ) : (
                        <img src={previewUrl} alt={label} className="h-full object-contain p-2" />
                    )}
                    {pendingFile && (
                        <div className="absolute inset-0 bg-primary/20 backdrop-blur-[1px] flex items-center justify-center">
                            <span className="bg-white px-2 py-1 rounded-lg text-[10px] font-bold text-primary shadow-sm">Pending Save</span>
                        </div>
                    )}
                </div>
            )}
            <label className={`flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed transition-colors text-sm ${disabled ? 'bg-slate-50 border-slate-100 cursor-not-allowed text-slate-400' : 'border-slate-200 hover:border-accent/50 cursor-pointer text-slate-600'}`}>
                <Upload className="w-4 h-4" /> {currentUrl || pendingFile ? 'Replace file' : 'Upload file'}
                {!disabled && (
                    <input type="file" className="hidden" accept={accept} onChange={e => {
                        const file = e.target.files?.[0];
                        if (file) onSelect(settingKey, file);
                    }} />
                )}
            </label>
        </div>
    );
}

const AdminSettingsPage: React.FC = () => {
    const queryClient = useQueryClient();
    const { user } = useAuthStore();
    const { companyName: globalCompanyName, logo: globalLogo } = useSettings();
    const [activeTab, setActiveTab] = useState<TabId>('company');
    const [localSettings, setLocalSettings] = useState<Record<string, string>>({});
    const [pendingFiles, setPendingFiles] = useState<Record<string, File>>({});

    // Profile Edit State
    const { setUser } = useAuthStore();
    const [profileForm, setProfileForm] = useState<any>({});
    const [isEditingProfile, setIsEditingProfile] = useState(false);

    useEffect(() => {
        if (user) {
            setProfileForm({
                name: user.name || '',
                father_name: user.father_name || '',
                dob: user.dob ? user.dob.split('T')[0] : '',
                gender: user.gender || '',
                marital_status: user.marital_status || '',
                blood_group: user.blood_group || '',
                religion: user.religion || '',
                whatsapp_number: user.whatsapp_number || '',
                state: user.state || '',
                district: user.district || '',
                area: user.area || '',
                pincode: user.pincode || '',
                landmark: user.landmark || '',
                permanent_address: user.permanent_address || '',
                current_address: user.current_address || '',
                occupation: user.occupation || '',
                qualification: user.qualification || '',
            });
        }
    }, [user]);

    const updateProfileMutation = useMutation({
        mutationFn: async (data: any) => {
            const res = await api.put('/admin/profile', data);
            return res.data;
        },
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                toast.success('Profile updated successfully');
                setIsEditingProfile(false);
            }
        },
        onError: (err: any) => {
            toast.error(err.response?.data?.message || 'Failed to update profile');
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

    const { data: settingsData, isLoading } = useQuery({
        queryKey: ['admin-settings'],
        queryFn: settingsApi.getSettings
    });

    useEffect(() => {
        if (settingsData?.success && settingsData.data) {
            const flat: Record<string, string> = {};
            Object.values(settingsData.data).flat().forEach(item => {
                flat[item.key] = item.value || '';
            });
            setLocalSettings(flat);
        }
    }, [settingsData]);

    const updateMutation = useMutation({
        mutationFn: settingsApi.updateSettings,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin-settings'] });
            queryClient.invalidateQueries({ queryKey: ['public-settings'] });
            toast.success('Settings saved');
        }
    });

    const uploadFileMutation = useMutation({
        mutationFn: async ({ key, file }: { key: string; file: File }) => {
            const fd = new FormData();
            fd.append('key', key);
            fd.append('file', file);
            const res = await api.post('/admin/settings/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
            return res.data;
        },
        onSuccess: (data, { key }) => {
            if (data?.data?.url) {
                const url: string = data.data.url;
                const relativePath = url.includes('/storage/') ? url.split('/storage/')[1] : url;
                setLocalSettings(prev => ({ ...prev, [key]: relativePath }));
            }
            queryClient.invalidateQueries({ queryKey: ['admin-settings'] });
            queryClient.invalidateQueries({ queryKey: ['public-settings'] });
        }
    });

    const saveSection = async (keys: string[], customData?: Record<string, any>) => {
        for (const key of keys) {
            if (pendingFiles[key]) {
                await uploadFileMutation.mutateAsync({ key, file: pendingFiles[key] });
            }
        }
        setPendingFiles(prev => {
            const updated = { ...prev };
            keys.forEach(k => delete updated[k]);
            return updated;
        });

        const settingsToSave = keys
            .filter(k => localSettings[k] !== undefined && !['company_logo', 'company_logo_2', 'company_signature', 'company_seal', 'hero_video'].includes(k))
            .map(k => ({ key: k, value: localSettings[k] }));

        if (customData) {
            Object.entries(customData).forEach(([k, v]) => {
                settingsToSave.push({ key: k, value: JSON.stringify(v) });
            });
        }

        if (settingsToSave.length > 0) {
            await updateMutation.mutateAsync(settingsToSave);
        }
    };

    const handleInput = (key: string, value: string) => setLocalSettings(p => ({ ...p, [key]: value }));

    const F = (k: string, label: string, type?: string, defaultValue?: string) => (
        <Field k={k} label={label} type={type} value={localSettings[k] || defaultValue || ''} onChange={handleInput} />
    );

    const FU = (settingKey: string, label: string, accept: string, disabled?: boolean) => (
        <FileUploadField
            settingKey={settingKey}
            label={label}
            accept={accept}
            currentUrl={localSettings[settingKey]}
            pendingFile={pendingFiles[settingKey]}
            onSelect={(key, file) => setPendingFiles(prev => ({ ...prev, [key]: file }))}
            disabled={disabled}
        />
    );

    const SectionSave = ({ keys, customData, label = "Save Changes" }: { keys: string[], customData?: Record<string, any>, label?: string }) => (
        <div className="flex justify-end pt-6 border-t border-slate-100">
            <button
                onClick={() => saveSection(keys, customData)}
                disabled={updateMutation.isPending || uploadFileMutation.isPending}
                className="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-xl font-bold hover:bg-primary/90 transition-all shadow-sm disabled:opacity-50"
            >
                {updateMutation.isPending || uploadFileMutation.isPending ? <RefreshCcw className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                {label}
            </button>
        </div>
    );

    if (isLoading) return <div className="flex h-64 items-center justify-center"><RefreshCcw className="w-8 h-8 text-accent animate-spin" /></div>;

    const tabs: { id: TabId; label: string; icon: React.ElementType }[] = [
        { id: 'company', label: 'Company Info', icon: Building2 },
        { id: 'branding', label: 'Branding', icon: Image },
        ...(user?.role === 'super_admin' ? [
            { id: 'portal' as TabId, label: 'Portal', icon: Globe },
            { id: 'achievements' as TabId, label: 'Achievements', icon: Trophy },
            { id: 'feedback' as TabId, label: 'Feedback', icon: MessageSquare },
            { id: 'incentive' as TabId, label: 'Incentive Points', icon: Trophy },
            { id: 'crm_options' as TabId, label: 'CRM Configuration', icon: SettingsIcon },
        ] : []),
        { id: 'icard', label: 'ID Card', icon: CreditCard },
        { id: 'letter', label: 'Joining Letter', icon: FileText },
        { id: 'profile', label: 'My Profile', icon: User },
    ];

    return (
        <div className="max-w-6xl mx-auto space-y-8">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                        <SettingsIcon className="text-accent" /> My Company Settings
                    </h1>
                    <p className="text-slate-600 text-sm">Configure your identity for ID cards, letters, and dashboard branding</p>
                </div>
            </div>

            <div className="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col md:flex-row min-h-[600px]">
                <div className="w-full md:w-64 border-r border-slate-100 bg-slate-50/50 p-4 space-y-1">
                    {tabs.map(tab => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${activeTab === tab.id ? 'bg-white text-accent shadow-sm border border-slate-100' : 'text-slate-600 hover:bg-slate-100'}`}
                        >
                            <tab.icon size={18} />
                            <span>{tab.label}</span>
                        </button>
                    ))}
                </div>

                <div className="flex-1 p-8">
                    {(() => {
                        switch (activeTab) {
                            case 'company':
                                return (
                                    <div className="space-y-6">
                                        <div className="p-4 bg-amber-50 border-l-4 border-amber-400 rounded-lg">
                                            <p className="text-xs text-amber-800 font-medium">
                                                <strong>Tenant Identity:</strong> These details appear on your generated documents (ID Cards, Joining Letters). 
                                                {user?.role === 'super_admin' && " Platform homepage details are managed in the Authority Hub."}
                                            </p>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            {F('company_name', 'Company Name (on ID Cards)')}
                                            {F('company_registration_no', 'Registration Number')}
                                            {F('company_email', 'Support Email')}
                                            {F('company_phone', 'Support Phone')}
                                            {F('company_mobile', 'Support Mobile')}
                                            {F('company_whatsapp', 'WhatsApp Number')}
                                            {F('company_website', 'Website URL')}
                                            <div className="md:col-span-2">
                                                {F('company_address', 'Full Address', 'textarea')}
                                            </div>
                                        </div>
                                        <SectionSave keys={['company_name', 'company_registration_no', 'company_email', 'company_phone', 'company_mobile', 'company_whatsapp', 'company_website', 'company_address']} label="Save Identity" />
                                    </div>
                                );
                            case 'branding':
                                return (
                                    <div className="space-y-8">
                                        {/* ── Read-only: Affiliate Partner (Super Admin) ── */}
                                        <div className="rounded-2xl border-2 border-indigo-100 bg-indigo-50/50 p-6 space-y-4">
                                            <div className="flex items-center gap-2 mb-1">
                                                <Globe className="w-4 h-4 text-indigo-500" />
                                                <span className="text-xs font-black uppercase tracking-widest text-indigo-600">Affiliate Partner (Platform Identity)</span>
                                                <span className="ml-auto text-[9px] bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full font-black uppercase tracking-wider">Read Only · Set by Super Admin</span>
                                            </div>
                                            <div className="flex items-center gap-4">
                                                <div className="w-16 h-16 rounded-xl border-2 border-indigo-100 bg-white flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                                                    {globalLogo ? (
                                                        <img src={globalLogo} alt={globalCompanyName} className="w-full h-full object-contain p-1" />
                                                    ) : (
                                                        <Globe className="w-7 h-7 text-indigo-300" />
                                                    )}
                                                </div>
                                                <div>
                                                    <p className="font-black text-slate-800 text-base">{globalCompanyName}</p>
                                                    <p className="text-xs text-slate-500 mt-0.5">This is the platform's master identity managed by the Super Admin. It appears on official documents as the affiliation authority.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <h3 className="font-bold text-slate-800">Your Company Branding</h3>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            {F('company_slogan', 'Company Slogan')}
                                            {F('authorized_signatory', 'Signatory Name')}
                                            {F('authorized_signatory_title', 'Signatory Title')}
                                            {F('icard_clearance', 'Clearance Level (e.g. Level-V)')}
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            <div className="space-y-1">
                                                {FU('company_logo', 'Institutional Identity (Locked)', 'image/*', user?.role !== 'super_admin')}
                                                <p className="text-[9px] text-indigo-500 font-black uppercase tracking-widest">Master logo set by platform HQ</p>
                                            </div>
                                            <div className="space-y-1">
                                                {FU('company_logo_2', 'My Team Branding (ID Front)', 'image/*')}
                                                <p className="text-[9px] text-slate-400 font-black uppercase tracking-widest">Appears on the front of your ID cards</p>
                                            </div>
                                            <div className="space-y-1">
                                                {FU('company_signature', 'Digital Signature', 'image/*')}
                                                <p className="text-[9px] text-slate-400 font-black uppercase tracking-widest">Appears on ID cards & letters</p>
                                            </div>
                                            <div className="space-y-1">
                                                {FU('company_seal', 'Official Seal', 'image/*')}
                                                <p className="text-[9px] text-slate-400 font-black uppercase tracking-widest">Appears on official letters</p>
                                            </div>
                                        </div>
                                        <SectionSave keys={['company_slogan', 'authorized_signatory', 'authorized_signatory_title', 'icard_clearance', 'company_logo', 'company_logo_2', 'company_signature', 'company_seal']} label="Save Branding" />
                                    </div>
                                );
                            case 'icard':
                                return (
                                    <div className="space-y-8">
                                        <h3 className="font-bold text-slate-800">ID Card Configuration</h3>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            {F('authorized_signatory', 'Signatory Name', 'text', 'Er. Sameer Ahmad')}
                                            {F('authorized_signatory_title', 'Signatory Title', 'text', 'Chief Operations Officer')}
                                            {F('icard_verified_by', 'Verified By', 'text', 'CHIEF OPERATIONS OFFICER')}
                                            {F('company_emergency', 'Emergency Contact', 'text', '9906766655')}
                                            {F('icard_clearance', 'Clearance Level', 'text', 'Level-V (Elite)')}
                                        </div>
                                        <div className="md:col-span-2">
                                            {F('icard_warning_text', 'Warning / Ownership Text', 'textarea', "THIS IDENTITY INSTRUMENT IS ISSUED BY SURYAMITRA SOLAR NETWORK. \nISSUED FOR SECURE ACCESS ONLY. \nIF FOUND, PLEASE RETURN TO A REGIONAL FACILITY OR THE RESIDENCY ROAD HQ.")}
                                        </div>
                                        <SectionSave keys={['authorized_signatory', 'authorized_signatory_title', 'icard_verified_by', 'company_emergency', 'icard_clearance', 'icard_warning_text']} label="Save Card Settings" />
                                    </div>
                                );
                            case 'letter':
                                return (
                                    <div className="space-y-8">
                                        <h3 className="font-bold text-slate-800">Joining Letter Configuration</h3>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            {F('signatory_name', 'Signatory Name (Letter)', 'text', 'Er. Sameer Ahmad')}
                                            {F('signatory_title', 'Signatory Title (Letter)', 'text', 'Chief Operations Officer')}
                                        </div>
                                        <div className="space-y-6">
                                            {F('letter_agent_body', 'Agent Appointment Body', 'textarea', "With reference to your application and subsequent interaction with our team, we are pleased to appoint you as a Business Development Executive (BDE) for our facilitation programme. \n\nYou will be responsible for identifying, registering, and facilitating potential beneficiaries for rooftop solar installation.")}
                                            {F('letter_super_agent_body', 'BDM Appointment Body', 'textarea', "With reference to your application and the evaluation conducted by our senior team, we are pleased to appoint you as a Business Development Manager (BDM) for our facilitation programme. \n\nYou will be responsible for building, managing, and mentoring a team of Business Development Executives.")}
                                            {F('letter_terms', 'Appointment Terms & Conditions (Letter)', 'textarea', "1. This appointment is for facilitation only.\n2. Do NOT collect money from beneficiaries.\n3. Confidentiality must be maintained.\n4. Appointment may be revoked for misconduct.")}
                                            {F('technical_team_terms', 'Technical Team Portal Terms (Survey & Installation)', 'textarea', "I hereby certify that I have personally visited the site, validated the structural and geographical suitability, and guarantee that the geo-tagged selfie submitted corresponds accurately to the property. Any false representation may result in immediate termination of affiliation.")}
                                        </div>
                                        <SectionSave keys={['signatory_name', 'signatory_title', 'letter_agent_body', 'letter_super_agent_body', 'letter_terms', 'technical_team_terms']} label="Save Settings" />
                                    </div>
                                );
                            case 'achievements':
                                return <AchievementManager />;
                            case 'feedback':
                                return <FeedbackManager />;
                            case 'crm_options':
                                return <div className="animate-in fade-in slide-in-from-bottom-2 duration-500"><SuperAdminCrmOptionsPage /></div>;
                            case 'profile':
                                return (
                                    <div className="space-y-12 animate-in fade-in slide-in-from-bottom-2 duration-500">
                                        <div className="flex items-center justify-between gap-4 border-b border-slate-100 pb-6">
                                            <div className="flex items-center gap-4">
                                                <div className="relative group">
                                                    <div className="w-24 h-24 rounded-[2rem] bg-slate-50 border-2 border-slate-100 flex items-center justify-center overflow-hidden">
                                                        {user?.profile_photo_url ? (
                                                            <img src={user.profile_photo_url} alt={user.name} className="w-full h-full object-cover" />
                                                        ) : (
                                                            <User size={32} className="text-slate-200" />
                                                        )}
                                                        <label className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                                            <Camera size={20} className="text-white" />
                                                            <input type="file" className="hidden" accept="image/*" onChange={e => {
                                                                const file = e.target.files?.[0];
                                                                if (file) uploadPhotoMutation.mutate(file);
                                                            }} />
                                                        </label>
                                                    </div>
                                                    {uploadPhotoMutation.isPending && (
                                                        <div className="absolute inset-0 bg-white/60 backdrop-blur-[2px] rounded-[2rem] flex items-center justify-center">
                                                            <RefreshCcw className="w-6 h-6 text-accent animate-spin" />
                                                        </div>
                                                    )}
                                                </div>
                                                <div>
                                                    <h3 className="text-xl font-black text-slate-800 tracking-tight">{user?.name}</h3>
                                                    <p className="text-slate-500 text-xs font-bold uppercase tracking-widest mt-1">{user?.role.replace('_', ' ')} Account</p>
                                                </div>
                                            </div>
                                            {!isEditingProfile ? (
                                                <button onClick={() => setIsEditingProfile(true)} className="px-5 py-2 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 transition-all flex items-center gap-2">
                                                    Edit Profile
                                                </button>
                                            ) : (
                                                <div className="flex gap-2">
                                                    <button onClick={() => setIsEditingProfile(false)} className="px-5 py-2 border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all">
                                                        Cancel
                                                    </button>
                                                    <button 
                                                        onClick={() => updateProfileMutation.mutate(profileForm)} 
                                                        disabled={updateProfileMutation.isPending}
                                                        className="px-5 py-2 bg-emerald-500 text-white rounded-xl font-bold text-sm hover:bg-emerald-600 transition-all flex items-center gap-2 disabled:opacity-50"
                                                    >
                                                        {updateProfileMutation.isPending ? <RefreshCcw className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                                                        Save Profile
                                                    </button>
                                                </div>
                                            )}
                                        </div>

                                        {/* Personal Info */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                            <div className="md:col-span-2">
                                                <h4 className="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-2">
                                                    <User size={14} className="text-accent" /> Personal Details
                                                </h4>
                                            </div>
                                            <ProfileField label="Full Name" value={user?.name} editing={isEditingProfile} formValue={profileForm.name} onChange={(v: string) => setProfileForm({ ...profileForm, name: v })} icon={<User size={14} />} />
                                            <ProfileField label="Father's Name" value={user?.father_name} editing={isEditingProfile} formValue={profileForm.father_name} onChange={(v: string) => setProfileForm({ ...profileForm, father_name: v })} icon={<User size={14} />} placeholder="S/o or D/o Name" />
                                            <ProfileField label="Date of Birth" value={user?.dob} type="date" editing={isEditingProfile} formValue={profileForm.dob} onChange={(v: string) => setProfileForm({ ...profileForm, dob: v })} icon={<Calendar size={14} />} />
                                            <ProfileField label="Gender" value={user?.gender} type="select" options={['male', 'female', 'other']} editing={isEditingProfile} formValue={profileForm.gender} onChange={(v: string) => setProfileForm({ ...profileForm, gender: v })} icon={<User size={14} />} />
                                            <ProfileField label="Marital Status" value={user?.marital_status} type="select" options={['single', 'married', 'divorced', 'widowed']} editing={isEditingProfile} formValue={profileForm.marital_status} onChange={(v: string) => setProfileForm({ ...profileForm, marital_status: v })} icon={<User size={14} />} />
                                            <ProfileField label="Blood Group" value={user?.blood_group} type="select" options={['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']} editing={isEditingProfile} formValue={profileForm.blood_group} onChange={(v: string) => setProfileForm({ ...profileForm, blood_group: v })} icon={<Star size={14} />} />
                                        </div>

                                        {/* Contact & Address */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 pt-6 border-t border-slate-50">
                                            <div className="md:col-span-2">
                                                <h4 className="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-2">
                                                    <MapPin size={14} className="text-accent" /> Contact & Location
                                                </h4>
                                            </div>
                                            <div className="space-y-1.5">
                                                <label className="text-[10px] font-black uppercase tracking-wider text-slate-400 ml-1">Support Phone</label>
                                                <div className="px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-slate-400 text-sm font-bold">{(user as any)?.mobile}</div>
                                            </div>
                                            <ProfileField label="WhatsApp Number" value={user?.whatsapp_number} type="tel" editing={isEditingProfile} formValue={profileForm.whatsapp_number} onChange={(v: string) => setProfileForm({ ...profileForm, whatsapp_number: v })} icon={<Phone size={14} />} />
                                            <ProfileField label="State" value={user?.state} type="select" options={INDIAN_STATES} editing={isEditingProfile} formValue={profileForm.state} onChange={(v: string) => setProfileForm({ ...profileForm, state: v, district: '' })} icon={<MapPin size={14} />} />
                                            <ProfileField label="District" value={user?.district} type="select" options={STATE_DISTRICTS[profileForm.state] || []} editing={isEditingProfile} formValue={profileForm.district} onChange={(v: string) => setProfileForm({ ...profileForm, district: v })} icon={<MapPin size={14} />} disabled={!profileForm.state} />
                                            <ProfileField label="Area / Locality" value={user?.area} editing={isEditingProfile} formValue={profileForm.area} onChange={(v: string) => setProfileForm({ ...profileForm, area: v })} icon={<MapPin size={14} />} />
                                            <ProfileField label="Pincode" value={user?.pincode} editing={isEditingProfile} formValue={profileForm.pincode} onChange={(v: string) => setProfileForm({ ...profileForm, pincode: v })} icon={<MapPin size={14} />} />
                                            <div className="md:col-span-2">
                                                <ProfileField label="Current Address" value={user?.current_address} type="textarea" editing={isEditingProfile} formValue={profileForm.current_address} onChange={(v: string) => setProfileForm({ ...profileForm, current_address: v })} icon={<MapPin size={14} />} />
                                            </div>
                                        </div>

                                        <div className="pt-10 border-t-4 border-slate-100">
                                            <h4 className="flex items-center gap-2 text-xs font-black text-slate-900 uppercase tracking-[0.2em] mb-6">
                                                <Lock size={14} className="text-red-500" /> Account Security
                                            </h4>
                                            <div className="max-w-md">
                                                <ChangePasswordForm />
                                            </div>
                                        </div>
                                    </div>
                                );
                            case 'portal':
                            case 'incentive':
                            default:
                                return (
                                    <div className="p-12 text-center text-slate-400 space-y-4">
                                        <RefreshCcw className="w-12 h-12 mx-auto mb-4 opacity-20" />
                                        <p className="font-bold text-slate-800">Unified Authority Migration</p>
                                        <p className="max-w-md mx-auto text-sm leading-relaxed">This module and its individual settings have been migrated to the unified Authority system for better platform consistency.</p>
                                        {user?.role === 'super_admin' && (
                                            <a href="/super-admin/profile" className="inline-flex items-center gap-2 px-6 py-2 bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100">
                                                Go to Authority Hub <ExternalLink size={14} />
                                            </a>
                                        )}
                                    </div>
                                );
                        }
                    })()}
                </div>
            </div>
        </div>
    );
};

// ====== Sub-components ======

interface ProfileFieldProps {
    label: string;
    value?: string | null;
    icon?: React.ReactNode;
    editing?: boolean;
    formValue?: string | null;
    onChange?: (val: string) => void;
    placeholder?: string;
    type?: 'text' | 'date' | 'select' | 'textarea' | 'number' | 'tel';
    options?: string[];
    disabled?: boolean;
}

const ProfileField: React.FC<ProfileFieldProps> = ({
    label, value, icon, editing, formValue, onChange, placeholder, type = 'text', options = [], disabled = false
}) => {
    if (editing && !disabled) {
        return (
            <div className="space-y-2">
                <div className="flex items-center gap-2 text-slate-400 ml-1">
                    {icon}
                    <label className="text-[10px] font-black uppercase tracking-[0.2em]">{label}</label>
                </div>
                {type === 'textarea' ? (
                    <textarea
                        value={formValue || ''}
                        onChange={e => onChange?.(e.target.value)}
                        placeholder={placeholder}
                        className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-slate-800 font-bold focus:border-emerald-500 outline-none transition-all min-h-[100px]"
                    />
                ) : type === 'tel' ? (
                    <div className="bg-slate-50 border-2 border-slate-100 rounded-2xl p-2 focus-within:border-emerald-500 transition-all">
                        <MobileInput
                            label=""
                            value={formValue || ''}
                            onChange={v => onChange?.(v)}
                            placeholder={placeholder}
                        />
                    </div>
                ) : type === 'select' ? (
                    <select
                        value={formValue || ''}
                        onChange={e => onChange?.(e.target.value)}
                        className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-slate-800 font-bold focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer"
                    >
                        <option value="">Select {label}...</option>
                        {options.map(opt => <option key={opt} value={opt}>{opt.charAt(0).toUpperCase() + opt.slice(1).replace(/_/g, ' ')}</option>)}
                    </select>
                ) : (
                    <input
                        type={type}
                        value={formValue || ''}
                        onChange={e => onChange?.(e.target.value)}
                        placeholder={placeholder}
                        className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-slate-800 font-bold focus:border-emerald-500 outline-none transition-all"
                    />
                )}
            </div>
        );
    }

    return (
        <div className="space-y-2">
            <div className="flex items-center gap-2 text-slate-400 ml-1">
                {icon}
                <label className="text-[10px] font-black uppercase tracking-[0.2em]">{label}</label>
            </div>
            <div className="px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl">
                <p className="font-bold text-slate-800 break-all">{value || '---'}</p>
            </div>
        </div>
    );
};

export default AdminSettingsPage;
