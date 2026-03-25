import React, { useState, useEffect, useRef } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
    Settings as SettingsIcon, Building2, Globe, Save, RefreshCcw,
    AlertCircle, User, Camera, CreditCard, Image, Trophy, MessageSquare,
    Upload, Plus, Trash2, Eye, EyeOff, Reply, ChevronDown, Check,
    Phone, Mail, Calendar, Shield, MapPin, FileText
} from 'lucide-react';
import toast from 'react-hot-toast';
import { settingsApi } from '@/api/settings.api';
import { authApi } from '@/api/auth.api';
import { useAuthStore } from '@/store/authStore';
import { achievementsApi, type AdminAchievement } from '@/api/achievements.api';
import { feedbacksApi, type AdminFeedback } from '@/api/feedbacks.api';
import { STATE_DISTRICTS, INDIAN_STATES } from '@/constants/locationData';
import api from '@/api/axios';
import ChangePasswordForm from '@/components/shared/ChangePasswordForm';
import MobileInput from '@/components/shared/MobileInput';

type TabId = 'company' | 'branding' | 'homepage' | 'achievements' | 'feedback' | 'portal' | 'icard' | 'profile' | 'letter' | 'incentive';

type CalculatorOption = {
    id: string;
    label: string;
    central: number;
    state: number;
    savings: number;
    payback: number;
};

type EligibilityQuestion = {
    id: string;
    text: string;
    expected: 'yes' | 'no';
};

const defaultCalcData: CalculatorOption[] = [
    { id: '1kw', label: '1KW System', central: 30000, state: 0, savings: 600, payback: 60 },
    { id: '2kw', label: '2KW System', central: 60000, state: 0, savings: 1200, payback: 54 },
    { id: '3kw', label: '3KW System', central: 78000, state: 0, savings: 1800, payback: 48 },
    { id: 'above_3kw', label: 'Above 3kW System', central: 78000, state: 0, savings: 2400, payback: 42 },
];

const defaultEligData: EligibilityQuestion[] = [
    { id: 'q1', text: 'Do you own the house where solar panels will be installed?', expected: 'yes' },
    { id: 'q2', text: 'Do you have an active electricity connection in your name?', expected: 'yes' },
    { id: 'q3', text: 'Do you have a valid Aadhaar-linked bank account?', expected: 'yes' },
    { id: 'q4', text: 'Have you NOT availed any solar subsidy before?', expected: 'yes' },
];

const defaultHeroStats: any[] = [
    { icon: 'IndianRupee', value: '₹78,000', label: 'Max Subsidy' },
    { icon: 'Zap', value: '300 Units', label: 'Free / Month' },
    { icon: 'Home', value: '1 Crore+', label: 'Target Homes' },
    { icon: 'BarChart3', value: '25 Years', label: 'Panel Life' },
];

const defaultHowItWorks: any[] = [
    { icon: 'ClipboardList', step: '1', title: 'Submit Query', desc: 'Fill our simple form with your details' },
    { icon: 'PhoneCall', step: '2', title: 'We Call You', desc: 'Our team calls within 24 hours' },
    { icon: 'Building2', step: '3', title: 'Govt Registration', desc: 'We register you on pmsuryaghar.gov.in' },
    { icon: 'Sun', step: '4', title: 'Installation', desc: 'Solar panels installed at your home' },
    { icon: 'Zap', step: '5', title: 'Free Electricity', desc: 'Enjoy up to 300 units free every month!' },
];

const defaultWhyChooseUs: any[] = [
    { icon: 'Sparkles', title: 'Free Guidance', desc: 'No charges from beneficiaries. Our service is completely free for homeowners.' },
    { icon: 'Zap', title: 'Faster Registration', desc: 'We handle all paperwork and portal registration so you don\'t have to.' },
    { icon: 'PhoneCall', title: 'End-to-End Support', desc: 'From application to installation, we stay with you throughout the entire process.' },
];

const AVAILABLE_ICONS = [
    'IndianRupee', 'Zap', 'Home', 'BarChart3', 'ClipboardList', 'PhoneCall', 'Building2', 'Sun', 'Sparkles', 'CheckCircle2', 'Plus', 'Users', 'Trophy', 'Briefcase', 'MessageSquare', 'Camera', 'Shield', 'Mail', 'Calendar', 'MapPin', 'FileText'
];


// ─── Standalone sub-components (must live OUTSIDE the parent to prevent remount on every keystroke) ───

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
    return `${baseUrl} /storage/${path} `;
};

function FileUploadField({ settingKey, label, accept, currentUrl, pendingFile, onSelect }: {
    settingKey: string; label: string; accept: string; currentUrl?: string;
    pendingFile?: File;
    onSelect: (key: string, file: File) => void;
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
            <label className="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-slate-200 hover:border-accent/50 cursor-pointer transition-colors text-sm text-slate-600">
                <Upload className="w-4 h-4" /> {currentUrl || pendingFile ? 'Replace file' : 'Upload file'}
                <input type="file" className="hidden" accept={accept} onChange={e => {
                    const file = e.target.files?.[0];
                    if (file) onSelect(settingKey, file);
                }} />
            </label>
        </div>
    );
}

const InfoBlock = ({ icon, label, value }: { icon: React.ReactNode, label: string, value?: string }) => (
    <div className="space-y-2">
        <div className="flex items-center gap-2 text-slate-500">
            {icon}
            <label className="text-[10px] font-black uppercase tracking-[0.2em]">{label}</label>
        </div>
        <p className="font-bold text-slate-800 text-lg border-b-2 border-slate-50 pb-2">{value}</p>
    </div>
);


const AdminSettingsPage: React.FC = () => {
    const queryClient = useQueryClient();
    const { user, setUser } = useAuthStore();
    const [activeTab, setActiveTab] = useState<TabId>('company');
    const [localSettings, setLocalSettings] = useState<Record<string, string>>({});
    const [pendingFiles, setPendingFiles] = useState<Record<string, File>>({});

    // Achievements state
    const [achForm, setAchForm] = useState({ title: '', description: '', date: '', is_published: true });
    const [achImage, setAchImage] = useState<File | null>(null);
    const [editingAch, setEditingAch] = useState<AdminAchievement | null>(null);
    const achImageRef = useRef<HTMLInputElement>(null);

    // Eligibility state
    const [eligData, setEligData] = useState<EligibilityQuestion[]>(defaultEligData);

    // Feedback state
    const [replyingTo, setReplyingTo] = useState<number | null>(null);
    const [replyText, setReplyText] = useState('');

    // Calculator state
    const [calcData, setCalcData] = useState<CalculatorOption[]>(defaultCalcData);

    // Homepage Cards state
    const [heroStatsData, setHeroStatsData] = useState<any[]>(defaultHeroStats);
    const [howItWorksData, setHowItWorksData] = useState<any[]>(defaultHowItWorks);
    const [whyChooseUsData, setWhyChooseUsData] = useState<any[]>(defaultWhyChooseUs);
    const [capacityPointsData, setCapacityPointsData] = useState<{ id: string, kw: number, points: number }[]>([]);

    // Profile state
    const [profileEditing, setProfileEditing] = useState(false);
    const [profileForm, setProfileForm] = useState({
        name: user?.name ?? '',
        mobile: (user as any)?.mobile ?? '',
        whatsapp_number: user?.whatsapp_number ?? '',
        father_name: (user as any)?.father_name ?? '',
        dob: (user as any)?.dob ? (user as any).dob.split('T')[0] : '',
        blood_group: (user as any)?.blood_group ?? '',
        state: user?.state ?? '',
        district: user?.district ?? '',
        area: user?.area ?? '',
    });

    const { data: settingsData, isLoading } = useQuery({
        queryKey: ['admin-settings'],
        queryFn: settingsApi.getSettings
    });

    const { data: achievements = [] } = useQuery({
        queryKey: ['admin-achievements'],
        queryFn: achievementsApi.list,
    });

    const { data: feedbacks = [] } = useQuery({
        queryKey: ['admin-feedbacks'],
        queryFn: feedbacksApi.list,
    });

    useEffect(() => {
        if (settingsData?.success && settingsData.data) {
            const flat: Record<string, string> = {};
            Object.values(settingsData.data).flat().forEach(item => {
                flat[item.key] = item.value || '';
            });
            setLocalSettings(flat);

            // Init Calculator Data safely
            try {
                if (flat['calculator_values_json']) {
                    const parsed = JSON.parse(flat['calculator_values_json']);
                    setCalcData(Array.isArray(parsed) ? parsed : defaultCalcData);
                }
                if (flat['eligibility_questions_json']) {
                    const parsed = JSON.parse(flat['eligibility_questions_json']);
                    setEligData(Array.isArray(parsed) ? parsed : defaultEligData);
                }
                if (flat['hero_stats_json']) {
                    const parsed = JSON.parse(flat['hero_stats_json']);
                    setHeroStatsData(Array.isArray(parsed) ? parsed : defaultHeroStats);
                }
                if (flat['how_it_works_json']) {
                    const parsed = JSON.parse(flat['how_it_works_json']);
                    setHowItWorksData(Array.isArray(parsed) ? parsed : defaultHowItWorks);
                }
                if (flat['why_choose_us_json']) {
                    const parsed = JSON.parse(flat['why_choose_us_json']);
                    setWhyChooseUsData(Array.isArray(parsed) ? parsed : defaultWhyChooseUs);
                }
                if (flat['capacity_points_json']) {
                    const parsed = JSON.parse(flat['capacity_points_json']);
                    if (Array.isArray(parsed)) {
                        setCapacityPointsData(parsed.map((p, i) => ({ id: `p-${i}`, ...p })));
                    } else if (typeof parsed === 'object') {
                        // Convert dictionary {"3kw": 1} to array [{id, kw: 3, points: 1}]
                        const asArray = Object.entries(parsed).map(([k, v], i) => ({
                            id: `p-${i}`,
                            kw: parseInt(k) || 0,
                            points: Number(v)
                        }));
                        setCapacityPointsData(asArray);
                    }
                }
            } catch (e) {
                if (import.meta.env.DEV) {
                    console.error("Failed to parse dynamic settings", e);
                }
            }
        }
    }, [settingsData]);

    const updateMutation = useMutation({
        mutationFn: settingsApi.updateSettings,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['admin-settings'] });
            queryClient.invalidateQueries({ queryKey: ['public-settings'] });
            toast.success('Settings saved');
        },
        onError: (err: any) => {
            const data = err.response?.data;
            if (data?.errors) {
                const first = Object.values(data.errors)[0] as string[];
                toast.error(first?.[0] || 'Validation error');
            } else {
                toast.error(data?.message || 'Failed to save settings');
            }
        },
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
            // Update localSettings with the returned URL so preview refreshes immediately
            if (data?.data?.url) {
                // Store the relative path portion (strip localhost:8000/storage/)
                const url: string = data.data.url;
                const relativePath = url.includes('/storage/') ? url.split('/storage/')[1] : url;
                setLocalSettings(prev => ({ ...prev, [key]: relativePath }));
            }
            queryClient.invalidateQueries({ queryKey: ['admin-settings'] });
            queryClient.invalidateQueries({ queryKey: ['public-settings'] });
            const label = key.replace(/_/g, ' ');
            toast.success(`✅ ${label} uploaded successfully`);
        },
        onError: (err: any) => {
            const msg = err?.response?.data?.message || 'Upload failed';
            toast.error(`Upload failed: ${msg} `);
        },
    });

    const uploadPhotoMutation = useMutation({
        mutationFn: authApi.uploadProfilePhoto,
        onSuccess: (res) => { if (res.success) { setUser(res.data); toast.success('Profile photo updated'); } },
        onError: () => toast.error('Failed to upload photo'),
    });

    // Achievement mutations
    const createAchMutation = useMutation({
        mutationFn: achievementsApi.create,
        onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['admin-achievements'] }); toast.success('Achievement added'); setAchForm({ title: '', description: '', date: '', is_published: true }); setAchImage(null); },
        onError: () => toast.error('Failed to add achievement'),
    });

    const updateAchMutation = useMutation({
        mutationFn: ({ id, fd }: { id: number; fd: FormData }) => achievementsApi.update(id, fd),
        onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['admin-achievements'] }); toast.success('Achievement updated'); setEditingAch(null); },
        onError: () => toast.error('Failed to update achievement'),
    });

    const deleteAchMutation = useMutation({
        mutationFn: achievementsApi.delete,
        onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['admin-achievements'] }); toast.success('Deleted'); },
    });

    // Feedback mutations
    const replyMutation = useMutation({
        mutationFn: ({ id, reply }: { id: number; reply: string }) => feedbacksApi.reply(id, reply),
        onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['admin-feedbacks'] }); toast.success('Reply sent'); setReplyingTo(null); setReplyText(''); },
    });

    const togglePublishMutation = useMutation({
        mutationFn: feedbacksApi.togglePublish,
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-feedbacks'] }),
    });

    const deleteFeedbackMutation = useMutation({
        mutationFn: feedbacksApi.delete,
        onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['admin-feedbacks'] }); toast.success('Deleted'); },
    });

    const updateProfileMutation = useMutation({
        mutationFn: settingsApi.updateMyProfile,
        onSuccess: (res) => {
            if (res.success) {
                setUser(res.data);
                toast.success('Profile updated');
                setProfileEditing(false);
            }
        },
        onError: (err: any) => {
            const msg = err.response?.data?.message || 'Failed to update profile';
            toast.error(msg);
        }
    });

    // Keys that are managed by the file-upload endpoint — must NEVER be sent to updateBulk
    // because their value (a storage path) is set server-side and is not in localSettings.
    const FILE_SETTING_KEYS = new Set([
        'company_logo',
        'company_favicon',
        'company_logo_2',
        'company_signature',
        'company_seal',
        'hero_video',
    ]);

    const saveSection = async (keys: string[], customData?: Record<string, any>) => {
        try {
            // 1. Upload pending files for this section
            const filesToUpload = Object.entries(pendingFiles).filter(([k]) => keys.includes(k));
            for (const [k, file] of filesToUpload) {
                await uploadFileMutation.mutateAsync({ key: k, file });
            }
            // Clear uploaded files from pending
            setPendingFiles(prev => {
                const updated = { ...prev };
                keys.forEach(k => delete updated[k]);
                return updated;
            });

            // 2. Save text settings — SKIP file keys whose paths are managed by the upload endpoint.
            // Sending them here would overwrite the uploaded path with the empty local-state value.
            const settingsToSave: { key: string; value: string | null }[] = [];
            keys.forEach(k => {
                // Save if (not a file key) OR (is a file key AND has been explicitly cleared to empty string)
                if (localSettings[k] !== undefined) {
                    if (!FILE_SETTING_KEYS.has(k) || localSettings[k] === '') {
                        settingsToSave.push({ key: k, value: localSettings[k] });
                    }
                }
            });

            // Add custom data (JSONs) if provided
            if (customData) {
                Object.entries(customData).forEach(([k, v]) => {
                    settingsToSave.push({ key: k, value: JSON.stringify(v) });
                });
            }

            if (settingsToSave.length > 0) {
                await updateMutation.mutateAsync(settingsToSave);
            } else if (filesToUpload.length === 0) {
                toast.error("Nothing to save");
            }
        } catch (err) {
            if (import.meta.env.DEV) {
                console.error("Save section failed", err);
            }
        }
    };

    const handleCalcChange = (id: string, field: keyof CalculatorOption, val: string) => {
        setCalcData(prev => prev.map(opt => {
            if (opt.id !== id) return opt;
            if (field === 'label' || field === 'id') {
                return { ...opt, [field]: val };
            }
            // Ensure state exists in the object if it was missing from DB
            const base = { ...opt };
            if (base.state === undefined) base.state = 0;
            return { ...base, [field]: val === '' ? 0 : parseInt(val, 10) };
        }));
    };

    const addCalcOption = () => {
        const newId = `system_${Date.now()} `;
        setCalcData(prev => [...prev, { id: newId, label: 'New System', central: 0, state: 0, savings: 0, payback: 0 }]);
    };

    const removeCalcOption = (id: string) => {
        setCalcData(prev => prev.filter(opt => opt.id !== id));
    };

    const handleEligChange = (id: string, field: keyof EligibilityQuestion, val: string) => {
        setEligData(prev => prev.map(q => {
            if (q.id !== id) return q;
            return { ...q, [field]: val };
        }));
    };

    const addEligQuestion = () => {
        const newId = `q_${Date.now()} `;
        setEligData(prev => [...prev, { id: newId, text: 'New Question', expected: 'yes' }]);
    };

    const removeEligQuestion = (id: string) => {
        setEligData(prev => prev.filter(q => q.id !== id));
    };

    const handleInput = (key: string, value: string) => setLocalSettings(p => ({ ...p, [key]: value }));

    const handleAchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!achForm.title) return toast.error('Title is required');
        const fd = new FormData();
        Object.entries(achForm).forEach(([k, v]) => {
            if (k === 'is_published') {
                fd.append(k, v ? '1' : '0');
            } else {
                fd.append(k, String(v));
            }
        });
        if (achImage) fd.append('image', achImage);

        if (editingAch) {
            updateAchMutation.mutate({ id: editingAch.id, fd });
        } else {
            createAchMutation.mutate(fd);
        }
    };

    const handleProfileSave = () => {
        updateProfileMutation.mutate(profileForm);
    };

    const startProfileEdit = () => {
        setProfileForm({
            name: user?.name ?? '',
            mobile: (user as any)?.mobile ?? '',
            whatsapp_number: user?.whatsapp_number ?? '',
            father_name: (user as any)?.father_name ?? '',
            dob: (user as any)?.dob ? (user as any).dob.split('T')[0] : '',
            blood_group: (user as any)?.blood_group ?? '',
            state: user?.state ?? '',
            district: user?.district ?? '',
            area: user?.area ?? '',
        });
        setProfileEditing(true);
    };

    const handleHeroStatChange = (idx: number, field: string, val: string) => {
        setHeroStatsData(prev => prev.map((item, i) => i === idx ? { ...item, [field]: val } : item));
    };
    const addHeroStat = () => setHeroStatsData(prev => [...prev, { icon: 'Sun', value: 'New Stat', label: 'Description' }]);
    const removeHeroStat = (idx: number) => setHeroStatsData(prev => prev.filter((_, i) => i !== idx));

    const handleHowItWorksChange = (idx: number, field: string, val: string) => {
        setHowItWorksData(prev => prev.map((item, i) => i === idx ? { ...item, [field]: val } : item));
    };
    const addHowItWorks = () => setHowItWorksData(prev => [...prev, { icon: 'Zap', step: (prev.length + 1).toString(), title: 'New Step', desc: 'Description' }]);
    const removeHowItWorks = (idx: number) => setHowItWorksData(prev => prev.filter((_, i) => i !== idx));

    const handleWhyChooseUsChange = (idx: number, field: string, val: string) => {
        setWhyChooseUsData(prev => prev.map((item, i) => i === idx ? { ...item, [field]: val } : item));
    };
    const addWhyChooseUs = () => setWhyChooseUsData(prev => [...prev, { icon: 'Sparkles', title: 'New Feature', desc: 'Description' }]);
    const removeWhyChooseUs = (idx: number) => setWhyChooseUsData(prev => prev.filter((_, i) => i !== idx));

    const handleCapacityPointChange = (id: string, field: 'kw' | 'points', val: string) => {
        setCapacityPointsData(prev => prev.map(item => item.id === id ? { ...item, [field]: parseFloat(val) || 0 } : item));
    };
    const addCapacityPoint = () => setCapacityPointsData(prev => [
        ...prev, 
        { 
            id: `p-${Date.now()}`, 
            kw: (prev.length > 0 ? Math.max(...prev.map(p => p.kw)) + 1 : 1), 
            points: 0 
        }
    ]);
    const removeCapacityPoint = (id: string) => setCapacityPointsData(prev => prev.filter(item => item.id !== id));

    /*
    const handleMediaSubmit = (e: React.FormEvent) => { ... };
    const handleDocSubmit = (e: React.FormEvent) => { ... };
    */

    // Thin wrappers that bind localSettings and handleInput — these are NOT component definitions,
    // they are plain functions that return JSX so React does not treat them as new component types.
    const F = (k: string, label: string, type?: string, defaultValue?: string) => (
        <Field k={k} label={label} type={type} value={localSettings[k] || defaultValue || ''} onChange={handleInput} placeholder={defaultValue} />
    );
    const FU = (settingKey: string, label: string, accept: string) => (
        <FileUploadField
            settingKey={settingKey}
            label={label}
            accept={accept}
            currentUrl={localSettings[settingKey] || undefined}
            pendingFile={pendingFiles[settingKey]}
            onSelect={(key, file) => setPendingFiles(prev => ({ ...prev, [key]: file }))}
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
        { id: 'homepage', label: 'Homepage', icon: Globe },
        { id: 'achievements', label: 'Achievements', icon: Trophy },
        { id: 'feedback', label: 'Feedback', icon: MessageSquare },
        { id: 'icard', label: 'ID Card', icon: CreditCard },
        { id: 'letter', label: 'Joining Letter', icon: FileText },
        { id: 'portal', label: 'Portal', icon: Globe },
        { id: 'profile', label: 'My Profile', icon: User },
        { id: 'incentive', label: 'Incentive Points', icon: Trophy },
    ];

    return (
        <div className="max-w-6xl mx-auto space-y-8">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                        <SettingsIcon className="text-accent" /> System Settings
                    </h1>
                    <p className="text-slate-600 text-sm">Configure your homepage, branding, achievements, and more</p>
                </div>
            </div>

            <div className="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col md:flex-row min-h-[600px]">
                {/* Tabs Sidebar */}
                <div className="w-full md:w-56 border-r border-slate-100 bg-slate-50/50 p-4 space-y-1 flex-shrink-0">
                    {tabs.map(tab => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 ${activeTab === tab.id
                                ? 'bg-white text-accent shadow-sm border border-slate-100'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-700'
                                }`}
                        >
                            <tab.icon size={18} className={activeTab === tab.id ? 'text-accent' : 'text-slate-400'} />
                            <span>{tab.label}</span>
                            {tab.id === 'feedback' && feedbacks.filter((f: AdminFeedback) => !f.admin_reply && !f.is_published).length > 0 && (
                                <span className="ml-auto bg-red-500 text-white text-[10px] rounded-full px-1.5 py-0.5 leading-none">
                                    {feedbacks.filter((f: AdminFeedback) => !f.admin_reply).length}
                                </span>
                            )}
                        </button>
                    ))}
                </div>

                {/* Tab Content */}
                <div className="flex-1 p-6 md:p-8 overflow-y-auto">

                    {/* ══ COMPANY INFO & FOOTER ══ */}
                    {activeTab === 'company' && (
                        <div className="space-y-8 animate-in fade-in duration-300">
                            <div>
                                <h3 className="font-bold text-slate-800 mb-4">Company Contact Information</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {F('company_name', 'Company Name', 'text', 'SURYAMITRA SOLAR NETWORK')}
                                    {F('company_registration_no', 'Registration Number', 'text', 'REG/SMS/2026/0892')}
                                    {F('company_affiliated_with', 'Affiliated with', 'text', 'Government of India / MNRE')}
                                    {F('company_email', 'Support Email', 'email', 'info@suryamitra.in')}
                                    {F('company_phone', 'Support Phone', 'text', '+91-9906766655')}
                                    {F('company_mobile', 'Support Mobile', 'tel', '+91 911 911 9111')}
                                    {F('company_whatsapp', 'WhatsApp Number', 'tel', '+91 911 911 9111')}

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Company Website
                                        </label>
                                        <div className="flex rounded-lg border border-gray-200 overflow-hidden
                                                      focus-within:border-orange-400 focus-within:ring-1 focus-within:ring-orange-400/20">
                                            <div className="flex items-center px-3 bg-gray-50 border-r border-gray-200 flex-shrink-0">
                                                <Globe className="w-4 h-4 text-gray-400" />
                                            </div>
                                            <input
                                                type="url"
                                                value={localSettings.company_website ?? ''}
                                                onChange={(e) => handleInput('company_website', e.target.value)}
                                                placeholder="https://suryamitra.in"
                                                className="flex-1 px-3 py-2.5 text-sm outline-none bg-white"
                                            />
                                        </div>
                                        <p className="text-xs text-gray-400 mt-1">
                                            This URL appears on the iCard, joining letter, and all official documents.
                                        </p>
                                    </div>

                                    <div className="md:col-span-2">{F('company_address', 'Full Address', 'textarea', 'Srinagar, Jammu & Kashmir')}</div>
                                </div>
                            </div>

                            <div className="border-t border-slate-100 pt-6">
                                <h3 className="font-bold text-slate-800 mb-4">Footer Section Titles & Links</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {F('footer_section_quick_links', 'Quick Links Section Title', 'text', 'Quick Links')}
                                    {F('footer_section_legal', 'Legal & Support Section Title', 'text', 'Legal & Support')}
                                    {F('footer_link_about', 'About Us Link Label', 'text', 'About Us')}
                                    {F('footer_link_scheme', 'PM Surya Ghar Scheme Link Label', 'text', 'PM Surya Ghar Scheme')}
                                    {F('footer_link_contact', 'Contact Link Label', 'text', 'Contact')}
                                    {F('footer_link_faq', 'FAQ Link Label', 'text', 'FAQ')}
                                    {F('footer_link_privacy', 'Privacy Policy Link Label', 'text', 'Privacy Policy')}
                                    {F('footer_link_terms', 'Terms & Conditions Link Label', 'text', 'Terms & Conditions')}
                                    {F('footer_link_refund', 'Refund Policy Link Label', 'text', 'Refund Policy')}
                                </div>
                            </div>

                            <SectionSave label="Save Company Info" keys={[
                                'company_name', 'company_registration_no', 'company_affiliated_with',
                                'company_email', 'company_mobile', 'company_whatsapp', 'company_website', 'company_address',
                                'footer_about_text', 'footer_copyright', 'footer_disclaimer',
                                'footer_section_quick_links', 'footer_section_legal', 'footer_link_about', 'footer_link_scheme',
                                'footer_link_contact', 'footer_link_faq', 'footer_link_privacy', 'footer_link_terms', 'footer_link_refund'
                            ]} />
                        </div>
                    )}

                    {/* ══ BRANDING ══ */}
                    {activeTab === 'branding' && (
                        <div className="space-y-6 animate-in fade-in duration-300">
                            <h3 className="font-bold text-slate-800">Branding &amp; Identity</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {F('authorized_signatory', 'Authorized Signatory Name', 'text', 'Authorized Signatory')}
                                {F('authorized_signatory_title', 'Authorized Signatory Title', 'text', 'Managing Director')}
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-4">
                                {FU('company_logo', 'Company Logo', 'image/*')}
                                {FU('company_favicon', 'Favicon (.ico, .png, .svg)', '.ico,.png,.svg,image/*')}
                                {FU('company_logo_2', 'Affiliation Logo', 'image/*')}
                                {FU('company_signature', 'Authorized Signature', 'image/*')}
                                {FU('company_seal', 'Official Seal', 'image/*')}
                            </div>
                            <SectionSave label="Save Branding" keys={['company_slogan', 'authorized_signatory', 'authorized_signatory_title', 'company_logo', 'company_favicon', 'company_logo_2', 'company_signature', 'company_seal']} />
                        </div>
                    )}

                    {/* ══ HOMEPAGE ══ */}
                    {activeTab === 'homepage' && (
                        <div className="space-y-10 animate-in fade-in duration-300 pb-8">

                            {/* Hero Section */}
                            <div className="space-y-6">
                                <h3 className="font-bold text-slate-800">Hero Section</h3>
                                {F('hero_headline', 'Main Headline', 'text', 'Get 300 Units FREE Electricity Every Month')}
                                {F('hero_subheadline', 'Sub-Headline', 'textarea', 'Government of India scheme — up to ₹78,000 subsidy for rooftop solar installation. We guide you end-to-end, completely free.')}

                                <div className="pt-2">
                                    {FU('hero_video', 'Hero Background Video (MP4 — will show dark overlay)', 'video/mp4,video/*')}
                                    {localSettings['hero_video'] && (
                                        <button onClick={() => handleInput('hero_video', '')} className="mt-2 text-xs text-red-500 hover:underline">Remove video (use gradient instead)</button>
                                    )}
                                </div>
                                <SectionSave label="Save Hero Section" keys={['hero_headline', 'hero_subheadline', 'hero_video']} />
                            </div>

                            {/* Eligibility Checker */}
                            <div className="space-y-6 pt-4">
                                <h3 className="font-bold text-slate-800">Eligibility Checker Section</h3>
                                {F('eligibility_headline', 'Section Headline', 'text', 'Check Your Eligibility')}
                                {F('eligibility_subheadline', 'Section Sub-headline', 'text', 'Answer 4 quick questions to find out if you qualify')}

                                <div className="mt-8">
                                    <div className="flex items-center justify-between mb-4">
                                        <h4 className="text-sm font-bold text-slate-600 uppercase tracking-wider">Eligibility Questions</h4>
                                        <button onClick={addEligQuestion} className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                            <Plus size={14} /> Add Question
                                        </button>
                                    </div>
                                    <div className="space-y-4 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                        {eligData.map((q, idx) => (
                                            <div key={q.id} className="bg-white p-5 rounded-xl border border-slate-100 shadow-sm relative group animate-in slide-in-from-left-2 duration-200">
                                                <button onClick={() => removeEligQuestion(q.id)} className="absolute top-4 right-4 text-slate-500 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <Trash2 size={16} />
                                                </button>
                                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                                    <div className="md:col-span-3">
                                                        <label className="text-xs font-semibold text-slate-600 uppercase">Question {idx + 1}</label>
                                                        <input type="text" className="input w-full mt-1 border-slate-200" value={q.text} onChange={e => handleEligChange(q.id, 'text', e.target.value)} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-600 uppercase">Required Answer</label>
                                                        <select className="input w-full mt-1 border-slate-200" value={q.expected} onChange={e => handleEligChange(q.id, 'expected', e.target.value as any)}>
                                                            <option value="yes">User must say YES</option>
                                                            <option value="no">User must say NO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div className="space-y-4 p-5 bg-green-50 rounded-2xl border border-green-100">
                                        <h4 className="text-sm font-bold text-green-700 uppercase tracking-wider">Success Message</h4>
                                        {F('eligibility_success_title', 'Success Title', 'text', '✅ You are Eligible for PM Surya Ghar!')}
                                        {F('eligibility_success_desc', 'Success Description', 'textarea', 'Great news! You meet the basic criteria. Please fill the Lead Form below and our team will guide you through the entire free installation process.')}
                                    </div>
                                    <div className="space-y-4 p-5 bg-red-50 rounded-2xl border border-red-100">
                                        <h4 className="text-sm font-bold text-red-700 uppercase tracking-wider">Error Message</h4>
                                        {F('eligibility_error_title', 'Error Title', 'text', '⚠️ You might not be eligible')}
                                        {F('eligibility_error_desc', 'Error Description', 'textarea', 'Based on your answers, you may not qualify for the government subsidy at this moment. However, please contact our support team as there may still be other options available for you.')}
                                    </div>
                                </div>

                                <SectionSave
                                    label="Save Eligibility Checker"
                                    keys={['eligibility_headline', 'eligibility_subheadline', 'eligibility_success_title', 'eligibility_success_desc', 'eligibility_error_title', 'eligibility_error_desc']}
                                    customData={{ eligibility_questions_json: eligData }}
                                />
                            </div>

                            {/* Hero Stats Section */}
                            <div className="space-y-6 border-t border-slate-100 pt-8">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="font-bold text-slate-800">Hero Stats Bar</h3>
                                    <button onClick={addHeroStat} className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                        <Plus size={14} /> Add Stat
                                    </button>
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    {heroStatsData.map((stat, idx) => (
                                        <div key={idx} className="bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative group">
                                            <button onClick={() => removeHeroStat(idx)} className="absolute top-3 right-3 text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Trash2 size={14} />
                                            </button>
                                            <div className="grid grid-cols-1 gap-3">
                                                <div className="flex gap-3">
                                                    <div className="flex-1">
                                                        <label className="text-[10px] font-bold text-slate-500 uppercase">Icon</label>
                                                        <select className="w-full text-xs border-slate-200 mt-1 rounded-lg" value={stat.icon} onChange={e => handleHeroStatChange(idx, 'icon', e.target.value)}>
                                                            {AVAILABLE_ICONS.map(icon => <option key={icon} value={icon}>{icon}</option>)}
                                                        </select>
                                                    </div>
                                                    <div className="flex-1">
                                                        <label className="text-[10px] font-bold text-slate-500 uppercase">Value</label>
                                                        <input type="text" className="w-full text-xs border-slate-200 mt-1 rounded-lg font-bold" value={stat.value} onChange={e => handleHeroStatChange(idx, 'value', e.target.value)} placeholder="e.g. ₹78,000" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label className="text-[10px] font-bold text-slate-500 uppercase">Label</label>
                                                    <input type="text" className="w-full text-xs border-slate-200 mt-1 rounded-lg" value={stat.label} onChange={e => handleHeroStatChange(idx, 'label', e.target.value)} placeholder="Max Subsidy" />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <SectionSave
                                    label="Save Hero Stats"
                                    keys={[]}
                                    customData={{ hero_stats_json: heroStatsData }}
                                />
                            </div>

                            {/* How It Works Section */}
                            <div className="space-y-6 border-t border-slate-100 pt-8">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="font-bold text-slate-800">How It Works (Steps)</h3>
                                    <button onClick={addHowItWorks} className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                        <Plus size={14} /> Add Step
                                    </button>
                                </div>
                                <div className="space-y-4 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    {howItWorksData.map((step, idx) => (
                                        <div key={idx} className="bg-white p-5 rounded-xl border border-slate-100 shadow-sm relative group animate-in slide-in-from-left-2 duration-200">
                                            <button onClick={() => removeHowItWorks(idx)} className="absolute top-4 right-4 text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Trash2 size={16} />
                                            </button>
                                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <div className="md:col-span-1">
                                                    <label className="text-xs font-semibold text-slate-600 uppercase">Icon & Step</label>
                                                    <div className="flex gap-2 mt-1">
                                                        <select className="flex-1 text-xs border-slate-200 rounded-lg" value={step.icon} onChange={e => handleHowItWorksChange(idx, 'icon', e.target.value)}>
                                                            {AVAILABLE_ICONS.map(icon => <option key={icon} value={icon}>{icon}</option>)}
                                                        </select>
                                                        <input type="text" className="w-12 text-xs border-slate-200 rounded-lg text-center font-bold" value={step.step} onChange={e => handleHowItWorksChange(idx, 'step', e.target.value)} />
                                                    </div>
                                                </div>
                                                <div className="md:col-span-1">
                                                    <label className="text-xs font-semibold text-slate-600 uppercase">Title</label>
                                                    <input type="text" className="w-full text-xs border-slate-200 mt-1 rounded-lg font-bold" value={step.title} onChange={e => handleHowItWorksChange(idx, 'title', e.target.value)} />
                                                </div>
                                                <div className="md:col-span-2">
                                                    <label className="text-xs font-semibold text-slate-600 uppercase">Description</label>
                                                    <input type="text" className="w-full text-xs border-slate-200 mt-1 rounded-lg" value={step.desc} onChange={e => handleHowItWorksChange(idx, 'desc', e.target.value)} />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <SectionSave
                                    label="Save Steps"
                                    keys={[]}
                                    customData={{ how_it_works_json: howItWorksData }}
                                />
                            </div>

                            {/* Why Choose Us Section */}
                            <div className="space-y-6 border-t border-slate-100 pt-8">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="font-bold text-slate-800">Why Choose Us (Feature Cards)</h3>
                                    <button onClick={addWhyChooseUs} className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                        <Plus size={14} /> Add Card
                                    </button>
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    {whyChooseUsData.map((item, idx) => (
                                        <div key={idx} className="bg-white p-5 rounded-xl border border-slate-100 shadow-sm relative group">
                                            <button onClick={() => removeWhyChooseUs(idx)} className="absolute top-4 right-4 text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Trash2 size={16} />
                                            </button>
                                            <div className="space-y-4">
                                                <div className="flex gap-4 items-end">
                                                    <div className="flex-1">
                                                        <label className="text-xs font-semibold text-slate-600 uppercase">Icon</label>
                                                        <select className="w-full text-xs border-slate-200 mt-1 rounded-lg" value={item.icon} onChange={e => handleWhyChooseUsChange(idx, 'icon', e.target.value)}>
                                                            {AVAILABLE_ICONS.map(icon => <option key={icon} value={icon}>{icon}</option>)}
                                                        </select>
                                                    </div>
                                                    <div className="flex-[2]">
                                                        <label className="text-xs font-semibold text-slate-600 uppercase">Title</label>
                                                        <input type="text" className="w-full text-xs border-slate-200 mt-1 rounded-lg font-bold" value={item.title} onChange={e => handleWhyChooseUsChange(idx, 'title', e.target.value)} />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label className="text-xs font-semibold text-slate-600 uppercase">Description</label>
                                                    <textarea className="w-full text-xs border-slate-200 mt-1 rounded-lg h-20" value={item.desc} onChange={e => handleWhyChooseUsChange(idx, 'desc', e.target.value)} />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <SectionSave
                                    label="Save Feature Cards"
                                    keys={[]}
                                    customData={{ why_choose_us_json: whyChooseUsData }}
                                />
                            </div>

                            {/* Subsidy Calculator */}
                            <div className="space-y-6 border-t border-slate-100 pt-8">
                                <h3 className="font-bold text-slate-800">Subsidy Calculator Section</h3>
                                {F('calculator_headline', 'Section Headline')}
                                {F('calculator_subheadline', 'Section Sub-headline')}

                                <div className="mt-8">
                                    <div className="flex items-center justify-between mb-4">
                                        <h4 className="text-sm font-bold text-slate-600 uppercase tracking-wider">Calculator Options</h4>
                                        <button onClick={addCalcOption} className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                            <Plus size={14} /> Add Option
                                        </button>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                        {calcData.map(opt => (
                                            <div key={opt.id} className="space-y-4 bg-white p-5 rounded-xl border border-slate-100 shadow-sm relative group">
                                                <button onClick={() => removeCalcOption(opt.id)} className="absolute top-4 right-4 text-slate-500 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity" title="Remove Option">
                                                    <Trash2 size={16} />
                                                </button>

                                                <div>
                                                    <label className="text-xs font-semibold text-slate-600">System Label (shown on button)</label>
                                                    <input type="text" className="input w-full mt-1 border-slate-200 font-bold text-primary" value={opt.label} onChange={e => handleCalcChange(opt.id, 'label', e.target.value)} />
                                                </div>
                                                <div className="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-600">Central Subsidy (₹)</label>
                                                        <input type="number" className="input w-full mt-1 border-slate-200" value={opt.central} onChange={e => handleCalcChange(opt.id, 'central', e.target.value)} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-600">State Subsidy (₹)</label>
                                                        <input type="number" className="input w-full mt-1 border-slate-200" value={opt.state || 0} onChange={e => handleCalcChange(opt.id, 'state', e.target.value)} />
                                                    </div>
                                                    <div className="col-span-2">
                                                        <div className="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center">
                                                            <span className="text-xs font-bold text-slate-600 uppercase">Total Subsidy</span>
                                                            <span className="font-black text-primary">₹{(Number(opt.central) + Number(opt.state || 0)).toLocaleString()}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-600">Monthly Savings (₹)</label>
                                                        <input type="number" className="input w-full mt-1 border-slate-200" value={opt.savings} onChange={e => handleCalcChange(opt.id, 'savings', e.target.value)} />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs font-semibold text-slate-600">Payback (Months)</label>
                                                        <input type="number" className="input w-full mt-1 border-slate-200" value={opt.payback} onChange={e => handleCalcChange(opt.id, 'payback', e.target.value)} />
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                <SectionSave
                                    label="Save Subsidy Calculator"
                                    keys={['calculator_headline', 'calculator_subheadline']}
                                    customData={{ calculator_values_json: calcData }}
                                />
                            </div>

                            {/* Section Labels */}
                            <div className="space-y-6 border-t border-slate-100 pt-8">
                                <h3 className="font-bold text-slate-800">General Section Labels</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {F('label_how_it_works', 'How It Works Title', 'text', 'How It Works')}
                                    {F('label_apply_title', 'Lead Form Title', 'text', 'Apply for Free Solar Electricity')}
                                    {F('label_apply_desc', 'Lead Form Description', 'text', 'Fill the form below and our team will call you within 24 hours')}
                                    {F('label_whatsapp_text', 'WhatsApp Button Text', 'text', 'WhatsApp Us')}
                                    {F('external_pmsuryaghar_label', 'Gov Portal Link Label', 'text', 'pmsuryaghar.gov.in')}
                                    {F('external_pmsuryaghar_url', 'Gov Portal Link URL', 'text', 'https://pmsuryaghar.gov.in')}
                                    {F('label_eligibility_checker', 'Eligibility Link Label', 'text', 'Eligibility Checker')}
                                    {F('label_subsidy_calculator', 'Calculator Link Label', 'text', 'Subsidy Calculator')}
                                    {F('label_become_executive', 'Become Executive Link Label', 'text', 'Become a Biz Dev Executive')}
                                    {F('label_executive_login', 'Executive Login Link Label', 'text', 'Biz Dev Executive Login')}
                                </div>
                                <SectionSave label="Save Section Labels" keys={['label_how_it_works', 'label_apply_title', 'label_apply_desc', 'label_whatsapp_text', 'external_pmsuryaghar_label', 'external_pmsuryaghar_url', 'label_eligibility_checker', 'label_subsidy_calculator', 'label_become_executive', 'label_executive_login']} />
                            </div>

                        </div>
                    )}

                    {/* Achievements section starts here */}

                    {/* ══ ACHIEVEMENTS ══ */}
                    {activeTab === 'achievements' && (
                        <div className="space-y-6 animate-in fade-in duration-300">
                            <div className="flex items-center justify-between">
                                <h3 className="font-bold text-slate-800">Manage Achievements</h3>
                            </div>

                            {/* Form */}
                            <form onSubmit={handleAchSubmit} className="bg-slate-50 rounded-2xl p-5 space-y-4 border border-slate-100">
                                <h4 className="font-semibold text-slate-700 text-sm">{editingAch ? 'Edit Achievement' : 'Add New Achievement'}</h4>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-xs font-bold text-slate-600 uppercase tracking-wider">Title *</label>
                                        <input className="input w-full mt-1" value={achForm.title} onChange={e => setAchForm(p => ({ ...p, title: e.target.value }))} placeholder="e.g. 500 Homes Powered!" />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-slate-600 uppercase tracking-wider">Date</label>
                                        <input type="date" className="input w-full mt-1" value={achForm.date} onChange={e => setAchForm(p => ({ ...p, date: e.target.value }))} />
                                    </div>
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-slate-600 uppercase tracking-wider">Description</label>
                                    <textarea className="input w-full mt-1" rows={2} value={achForm.description} onChange={e => setAchForm(p => ({ ...p, description: e.target.value }))} placeholder="Short description..." />
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-slate-600 uppercase tracking-wider">Image</label>
                                    <input ref={achImageRef} type="file" accept="image/*" className="block mt-1 text-sm text-slate-600" onChange={e => setAchImage(e.target.files?.[0] || null)} />
                                </div>
                                <div className="flex gap-3">
                                    <button type="submit" disabled={createAchMutation.isPending || updateAchMutation.isPending} className="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary/90 transition-all disabled:opacity-50">
                                        {editingAch ? <><Check size={14} /> Save Changes</> : <><Plus size={14} /> Add Achievement</>}
                                    </button>
                                    {editingAch && <button type="button" onClick={() => setEditingAch(null)} className="px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-100 rounded-xl font-medium">Cancel</button>}
                                </div>
                            </form>

                            {/* List */}
                            <div className="space-y-3">
                                {achievements.map((a: AdminAchievement) => (
                                    <div key={a.id} className="flex items-center gap-4 p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                                        {a.image_url ? <img src={a.image_url} alt={a.title} className="w-16 h-16 rounded-xl object-cover" /> : <div className="w-16 h-16 rounded-xl bg-primary/10 flex items-center justify-center"><Trophy size={20} className="text-primary" /></div>}
                                        <div className="flex-1 min-w-0">
                                            <p className="font-semibold text-slate-800 truncate">{a.title}</p>
                                            {a.date && <p className="text-xs text-slate-500">{a.date}</p>}
                                            <span className={`inline - block mt - 1 px - 2 py - 0.5 text - [10px] font - bold rounded - full ${a.is_published ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'} `}>{a.is_published ? 'Published' : 'Draft'}</span>
                                        </div>
                                        <div className="flex gap-2">
                                            <button onClick={() => { setEditingAch(a); setAchForm({ title: a.title, description: a.description || '', date: a.date || '', is_published: a.is_published }); }} className="p-2 rounded-xl hover:bg-slate-100 text-slate-500"><ChevronDown size={16} /></button>
                                            <button onClick={() => { if (confirm('Delete?')) deleteAchMutation.mutate(a.id); }} className="p-2 rounded-xl hover:bg-red-50 text-red-400"><Trash2 size={16} /></button>
                                        </div>
                                    </div>
                                ))}
                                {achievements.length === 0 && <p className="text-center text-slate-600 py-8 text-sm">No achievements yet. Add one above!</p>}
                            </div>
                        </div>
                    )}

                    {/* ══ FEEDBACK ══ */}
                    {activeTab === 'feedback' && (
                        <div className="space-y-4 animate-in fade-in duration-300">
                            <h3 className="font-bold text-slate-800">Customer Feedback</h3>
                            {feedbacks.map((f: AdminFeedback) => (
                                <div key={f.id} className={`p - 5 rounded - 2xl border ${f.is_published ? 'border-green-100 bg-green-50/30' : 'border-slate-100 bg-white'} shadow - sm space - y - 3`}>
                                    <div className="flex items-start justify-between gap-4 flex-wrap">
                                        <div>
                                            <p className="font-semibold text-slate-800">{f.name}</p>
                                            <div className="flex gap-0.5 mt-0.5">{[1, 2, 3, 4, 5].map(i => <span key={i} className={`text - base ${i <= f.rating ? 'text-amber-400' : 'text-slate-200'} `}>★</span>)}</div>
                                            <p className="text-xs text-slate-500 mt-1">{f.created_at.slice(0, 10)} {f.email && `· ${f.email} `} {f.phone && `· ${f.phone} `}</p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <button onClick={() => togglePublishMutation.mutate(f.id)} className={`flex items - center gap - 1.5 px - 3 py - 1.5 rounded - xl text - xs font - bold transition - all ${f.is_published ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'} `}>
                                                {f.is_published ? <><Eye size={12} /> Published</> : <><EyeOff size={12} /> Hidden</>}
                                            </button>
                                            <button onClick={() => { if (confirm('Delete?')) deleteFeedbackMutation.mutate(f.id); }} className="p-2 rounded-xl hover:bg-red-50 text-red-400"><Trash2 size={14} /></button>
                                        </div>
                                    </div>
                                    <p className="text-sm text-slate-700 italic">"{f.message}"</p>
                                    {f.admin_reply && (
                                        <div className="bg-primary/5 border border-primary/10 rounded-xl p-3 text-sm">
                                            <span className="font-bold text-primary text-xs">Your Reply: </span>{f.admin_reply}
                                        </div>
                                    )}
                                    {replyingTo === f.id ? (
                                        <div className="space-y-2">
                                            <textarea className="input w-full" rows={2} placeholder="Write your reply..." value={replyText} onChange={e => setReplyText(e.target.value)} />
                                            <div className="flex gap-2">
                                                <button onClick={() => { if (replyText) replyMutation.mutate({ id: f.id, reply: replyText }); }} disabled={replyMutation.isPending} className="flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold disabled:opacity-50"><Check size={12} /> Send Reply</button>
                                                <button onClick={() => { setReplyingTo(null); setReplyText(''); }} className="px-3 py-2 text-xs text-slate-500 hover:bg-slate-100 rounded-xl">Cancel</button>
                                            </div>
                                        </div>
                                    ) : (
                                        <button onClick={() => { setReplyingTo(f.id); setReplyText(f.admin_reply || ''); }} className="flex items-center gap-1.5 text-xs font-bold text-primary hover:text-primary/70 transition-colors"><Reply size={12} /> {f.admin_reply ? 'Edit Reply' : 'Reply'}</button>
                                    )}
                                </div>
                            ))}
                            {feedbacks.length === 0 && <p className="text-center text-slate-600 py-16 text-sm">No customer feedback yet.</p>}
                        </div>
                    )}

                    <div className="border-t border-slate-100 pt-6">
                        <h3 className="font-bold text-slate-800">Navigation Labels</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {F('nav_home', 'Home Link Label', 'text', 'Home')}
                            {F('nav_rewards', 'Rewards Link Label', 'text', 'Rewards')}
                            {F('nav_portal_login', 'Portal Login Button Label', 'text', 'Portal Login')}
                            {F('nav_cta_electricity', 'Main CTA Button Label', 'text', 'Get Free Electricity')}
                        </div>
                    </div>
                    <SectionSave label="Save Portal Settings" keys={['welcome_message', 'terms_conditions', 'nav_home', 'nav_rewards', 'nav_portal_login', 'nav_cta_electricity']} />

                    {/* ══ ID CARD ══ */}
                    {activeTab === 'icard' && (
                        <div className="space-y-6 animate-in fade-in duration-300">
                            <h3 className="font-bold text-slate-800">Identity Card Settings</h3>

                            {/* Card Back Info */}
                            <div className="pt-2">
                                <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">ID Card Configuration</p>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {F('icard_verified_by', 'Verified By Text', 'text', 'SYSTEM_AUTH_V6')}
                                    {F('icard_clearance', 'Default Clearance Level', 'text', 'Level-V (Elite)')}
                                    {F('company_emergency', 'Emergency Number (shown on back)', 'text', '102')}
                                    <div className="md:col-span-2">
                                        {F('icard_warning_text', 'Backside Warning Message', 'textarea', 'THIS IDENTITY INSTRUMENT IS ISSUED BY SURYAMITRA SOLAR NETWORK. \nISSUED FOR SECURE ACCESS ONLY. \nIF FOUND, PLEASE RETURN TO A REGIONAL FACILITY OR THE RESIDENCY ROAD HQ.')}
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 bg-orange-50 border border-orange-100 rounded-xl flex gap-3">
                                <AlertCircle className="text-orange-500 shrink-0" size={18} />
                                <p className="text-sm text-orange-800">All changes here reflect instantly on every downloaded member ID card — both front and back.</p>
                            </div>
                            <SectionSave label="Save ID Card Settings" keys={[
                                'icard_verified_by', 'icard_clearance', 'icard_warning_text', 'company_emergency'
                            ]} />
                        </div>
                    )}

                    {/* ══ JOINING LETTER ══ */}
                    {activeTab === 'letter' && (
                        <div className="space-y-8 animate-in fade-in duration-300">
                            <div>
                                <h3 className="font-bold text-slate-800 mb-1">Joining Letter Configuration</h3>
                                <p className="text-sm text-slate-500 mb-6">Manage the content and terms of appointment letters for all roles.</p>

                                <div className="space-y-8">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div className="space-y-4">
                                            <div className="p-4 bg-primary/5 rounded-2xl border border-primary/10">
                                                <h4 className="text-xs font-bold text-primary uppercase tracking-wider mb-3">Executive (BDE) Letter Body</h4>
                                                {F('letter_agent_body', 'BDE Letter Content', 'textarea')}
                                                <p className="text-[10px] text-slate-400 mt-2">Placeholders: <code className="text-primary">{'{name}'}</code>, <code className="text-primary">{'{agent_id}'}</code>, <code className="text-primary">{'{joining_date}'}</code>, <code className="text-primary">{'{territory}'}</code></p>
                                            </div>
                                        </div>
                                        <div className="space-y-4">
                                            <div className="p-4 bg-accent/5 rounded-2xl border border-accent/10">
                                                <h4 className="text-xs font-bold text-accent uppercase tracking-wider mb-3">Manager (BDM) Letter Body</h4>
                                                {F('letter_super_agent_body', 'BDM Letter Content', 'textarea')}
                                                <p className="text-[10px] text-slate-400 mt-2">Placeholders: <code className="text-primary">{'{name}'}</code>, <code className="text-primary">{'{super_agent_code}'}</code>, <code className="text-primary">{'{joining_date}'}</code>, <code className="text-primary">{'{territory}'}</code></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="border-t border-slate-100 pt-8">
                                        <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Terms & Conditions (One per line)</h4>
                                        {F('letter_terms', 'Standard Terms', 'textarea')}
                                    </div>

                                    <div className="border-t border-slate-100 pt-8">
                                        <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Authorized Signatory & Footer</h4>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            {F('signatory_name', 'Signatory Name', 'text', 'Authorized Signatory')}
                                            {F('signatory_title', 'Signatory Designation', 'text', 'Manager')}
                                            <div className="md:col-span-2">
                                                {F('letter_footer_note', 'Letter Footer Note', 'textarea')}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 bg-blue-50 border border-blue-100 rounded-xl flex gap-3">
                                <AlertCircle className="text-blue-500 shrink-0" size={18} />
                                <p className="text-sm text-blue-800">HTML is supported in letter bodies. Use <code className="font-bold">{'<strong>'}</code> for bold text.</p>
                            </div>

                            <SectionSave label="Save Letter Settings" keys={[
                                'letter_agent_body', 'letter_super_agent_body', 'letter_terms', 'letter_footer_note',
                                'signatory_name', 'signatory_title'
                            ]} />
                        </div>
                    )}

                    {/* ══ PROFILE ══ */}
                    {activeTab === 'profile' && (
                        <div className="space-y-6 animate-in fade-in duration-300">
                            <div className="flex items-center justify-between">
                                <h3 className="font-bold text-slate-800">My Profile</h3>
                                {!profileEditing ? (
                                    <button
                                        onClick={startProfileEdit}
                                        className="flex items-center gap-2 px-4 py-2 bg-accent text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-accent/90 transition-all shadow-sm"
                                    >
                                        Edit Profile
                                    </button>
                                ) : (
                                    <div className="flex gap-2">
                                        <button
                                            onClick={() => setProfileEditing(false)}
                                            className="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-50 transition-colors"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            onClick={handleProfileSave}
                                            disabled={updateProfileMutation.isPending}
                                            className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-green-700 transition-shadow hover:shadow-lg disabled:opacity-50"
                                        >
                                            <Save size={16} /> {updateProfileMutation.isPending ? 'Saving...' : 'Save Changes'}
                                        </button>
                                    </div>
                                )}
                            </div>

                            <div className="space-y-8 max-w-4xl">
                                <div className="flex items-center gap-6">
                                    <div className="relative group">
                                        <div className="w-24 h-24 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden border-2 border-slate-200 shadow-sm relative z-0">
                                            {user?.profile_photo_url ? <img src={user.profile_photo_url} alt={user.name} className="w-full h-full object-cover" /> : <User size={40} className="text-slate-300" />}
                                        </div>
                                        <label className="absolute -bottom-2 -right-2 p-2 bg-accent text-white rounded-xl shadow-lg cursor-pointer hover:scale-110 transition-transform z-10">
                                            <Camera size={16} />
                                            <input type="file" className="hidden" accept="image/*" onChange={e => { const f = e.target.files?.[0]; if (f) uploadPhotoMutation.mutate(f); }} />
                                        </label>
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-slate-800 text-lg">{user?.name}</h4>
                                        <p className="text-sm font-bold text-slate-400 uppercase tracking-[0.1em]">{user?.role === 'admin' ? 'Administrator' : user?.role}</p>
                                        <p className="text-xs text-slate-400 mt-1 font-medium">{user?.email}</p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                    {!profileEditing ? (
                                        <>
                                            <InfoBlock icon={<User size={14} />} label="Full Name" value={user?.name} />
                                            <InfoBlock icon={<Phone size={14} />} label="Primary Mobile" value={(user as any)?.mobile} />
                                            <InfoBlock icon={<Phone size={14} />} label="WhatsApp Number" value={user?.whatsapp_number || 'Not Provided'} />
                                            <InfoBlock icon={<Mail size={14} />} label="Email Address" value={user?.email || 'Not Provided'} />
                                            <InfoBlock icon={<User size={14} />} label="Father's Name" value={(user as any)?.father_name || 'Not Provided'} />
                                            <InfoBlock icon={<Calendar size={14} />} label="Date of Birth" value={(user as any)?.dob ? new Date((user as any).dob).toLocaleDateString() : 'Not Provided'} />
                                            <InfoBlock icon={<Shield size={14} />} label="Blood Group" value={(user as any)?.blood_group || 'Not Provided'} />
                                            <div className="md:col-span-2">
                                                <InfoBlock icon={<MapPin size={14} />} label="Location" value={[user?.area, user?.district, user?.state].filter(Boolean).join(', ') || 'Not Provided'} />
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <div className="space-y-1.5">
                                                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">Full Name</label>
                                                <input type="text" value={profileForm.name} onChange={e => setProfileForm({ ...profileForm, name: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm" placeholder="Full Name" />
                                            </div>
                                            <MobileInput
                                                label="Mobile Number"
                                                required
                                                value={profileForm.mobile}
                                                onChange={val => setProfileForm({ ...profileForm, mobile: val })}
                                            />

                                            <MobileInput
                                                label="WhatsApp Number"
                                                required
                                                value={profileForm.whatsapp_number}
                                                onChange={val => setProfileForm({ ...profileForm, whatsapp_number: val })}
                                            />

                                            <div className="space-y-1.5">
                                                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">Father's Name</label>
                                                <input type="text" value={profileForm.father_name} onChange={e => setProfileForm({ ...profileForm, father_name: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm" placeholder="S/o or D/o Name" />
                                            </div>

                                            <div className="space-y-1.5">
                                                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">Date of Birth</label>
                                                <input type="date" value={profileForm.dob} onChange={e => setProfileForm({ ...profileForm, dob: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm" />
                                            </div>

                                            <div className="space-y-1.5">
                                                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">Blood Group</label>
                                                <select
                                                    value={profileForm.blood_group}
                                                    onChange={e => setProfileForm({ ...profileForm, blood_group: e.target.value })}
                                                    className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm"
                                                >
                                                    <option value="">Select...</option>
                                                    {['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'].map(bg => <option key={bg} value={bg}>{bg}</option>)}
                                                </select>
                                            </div>

                                            <div className="space-y-1.5">
                                                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">State</label>
                                                <select
                                                    value={profileForm.state}
                                                    onChange={e => setProfileForm({ ...profileForm, state: e.target.value, district: '' })}
                                                    className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm"
                                                >
                                                    <option value="">Select State</option>
                                                    {INDIAN_STATES.map(s => <option key={s} value={s}>{s}</option>)}
                                                </select>
                                            </div>

                                            <div className="space-y-1.5">
                                                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">District</label>
                                                <select
                                                    value={profileForm.district}
                                                    onChange={e => setProfileForm({ ...profileForm, district: e.target.value })}
                                                    disabled={!profileForm.state}
                                                    className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm disabled:opacity-50"
                                                >
                                                    <option value="">Select District</option>
                                                    {(STATE_DISTRICTS[profileForm.state] || []).map(d => <option key={d} value={d}>{d}</option>)}
                                                </select>
                                            </div>

                                            <div className="md:col-span-2 space-y-1.5">
                                                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">Serving Area</label>
                                                <input type="text" value={profileForm.area} onChange={e => setProfileForm({ ...profileForm, area: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent bg-white transition-all text-sm" />
                                            </div>
                                        </>
                                    )}
                                </div>

                                {!profileEditing && (
                                    <>
                                        <ChangePasswordForm />

                                        <div className="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex gap-4 shadow-sm">
                                            <AlertCircle className="text-blue-500 shrink-0 mt-0.5" size={20} />
                                            <div>
                                                <h5 className="font-bold text-blue-900 text-sm mb-1">Account Notice</h5>
                                                <p className="text-sm text-blue-700 leading-relaxed font-medium">Your primary email address is managed by the system. Contact IT support for verified identity changes to your email.</p>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    )}
                    {/* ══ INCENTIVE POINTS ══ */}
                    {activeTab === 'incentive' && (
                        <div className="space-y-8 animate-in fade-in duration-300">
                            <div className="space-y-6">
                                <h3 className="font-bold text-slate-800">Incentive Points Configuration</h3>
                                <p className="text-sm text-slate-500">Configure how many points are awarded to agents based on the system capacity (kW) of completed installations.</p>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {F('offer_grace_period_days', 'Offer Grace Period (Days)', 'number', '7')}
                                </div>

                                <div className="mt-8">
                                    <div className="flex items-center justify-between mb-4">
                                        <h4 className="text-sm font-bold text-slate-600 uppercase tracking-wider">Capacity Point Mapping</h4>
                                        <button onClick={addCapacityPoint} className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                            <Plus size={14} /> Add Mapping
                                        </button>
                                    </div>
                                    <div className="space-y-3 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                        <div className="grid grid-cols-5 gap-4 px-4 mb-2">
                                            <div className="col-span-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">System Capacity (kW)</div>
                                            <div className="col-span-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Points Awarded</div>
                                            <div className="col-span-1"></div>
                                        </div>
                                        {capacityPointsData.sort((a, b) => a.kw - b.kw).map((item) => (
                                            <div key={item.id} className="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex items-center gap-4 group animate-in slide-in-from-left-2 duration-200">
                                                <div className="col-span-2 flex-1 flex items-center gap-2">
                                                    <input 
                                                        type="number" 
                                                        step="1"
                                                        className="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 outline-none" 
                                                        value={item.kw} 
                                                        onChange={e => handleCapacityPointChange(item.id, 'kw', e.target.value)} 
                                                    />
                                                    <span className="text-xs font-bold text-slate-400">kW</span>
                                                </div>
                                                <div className="col-span-2 flex-1 flex items-center gap-2">
                                                    <input 
                                                        type="number" 
                                                        step="0.1"
                                                        className="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm font-bold text-primary focus:ring-2 focus:ring-primary/20 outline-none" 
                                                        value={item.points} 
                                                        onChange={e => handleCapacityPointChange(item.id, 'points', e.target.value)} 
                                                    />
                                                    <span className="text-xs font-bold text-slate-400">Pts</span>
                                                </div>
                                                <button onClick={() => removeCapacityPoint(item.id)} className="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                                    <Trash2 size={16} />
                                                </button>
                                            </div>
                                        ))}
                                        {capacityPointsData.length === 0 && (
                                            <div className="text-center py-8 text-slate-400 text-sm italic">No point mappings defined. Click "Add Mapping" to start.</div>
                                        )}
                                    </div>
                                </div>
                                <div className="p-4 bg-blue-50 border border-blue-100 rounded-xl flex gap-3">
                                    <AlertCircle className="text-blue-500 shrink-0" size={18} />
                                    <div className="text-xs text-blue-800 space-y-1">
                                        <p><strong>3kW Threshold Note:</strong> Per business requirements, systems below 3kW usually earn 0 points. Ensure you map 1kW and 2kW to 0 points if required.</p>
                                        <p>Changes here affect all future point calculations for completed installations.</p>
                                    </div>
                                </div>
                                <SectionSave 
                                    label="Save Incentive Settings" 
                                    keys={['offer_grace_period_days']} 
                                    customData={{ 
                                        capacity_points_json: capacityPointsData.reduce((acc, curr) => ({
                                            ...acc,
                                            [`${curr.kw}kw`]: curr.points
                                        }), {})
                                    }} 
                                />
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default AdminSettingsPage;
