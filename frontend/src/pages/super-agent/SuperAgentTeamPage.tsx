import { useState, useRef } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import {
    Search, Users, Plus, X, UserPlus, Info,
    Image as ImageIcon, CreditCard, Hash, FileText, Upload, MapPin
} from 'lucide-react';
import { superAgentApi } from '@/api/superAgent.api';
import type { User } from '@/types';
import toast from 'react-hot-toast';
import { INDIAN_STATES, STATE_DISTRICTS } from '@/constants/locationData';
import MobileInput from '@/components/shared/MobileInput';

export default function SuperAgentTeamPage() {
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [formData, setFormData] = useState({
        name: '', email: '', mobile: '', whatsapp_number: '', aadhaar_number: '',
        occupation: '', education_level: '', state: '', district: '', area: '',
        father_name: '', dob: '', blood_group: '', religion: '', gender: '', marital_status: '',
        permanent_address: '', current_address: '', pincode: '', landmark: '',
        pan_number: '', voter_id: '', bank_name: '', bank_account_number: '',
        bank_ifsc: '', bank_branch: '', upi_id: '', experience_years: '',
        languages_known: [] as string[], reference_name: '', reference_mobile: '',
    });

    // File states
    const [profilePhoto, setProfilePhoto] = useState<any>({ file: null, name: '', preview: null });
    const [aadhaarDoc, setAadhaarDoc] = useState<any>({ file: null, name: '', preview: null });
    const [panDoc, setPanDoc] = useState<any>({ file: null, name: '', preview: null });
    const [educationCert, setEducationCert] = useState<any>({ file: null, name: '', preview: null });
    const [resume, setResume] = useState<any>({ file: null, name: '', preview: null });
    const [mouSigned, setMouSigned] = useState<any>({ file: null, name: '', preview: null });

    const qc = useQueryClient();

    const { data, isLoading } = useQuery({
        queryKey: ['sa-team', search, statusFilter],
        queryFn: () => superAgentApi.getMyTeam({ search: search || undefined, status: statusFilter || undefined }),
    });

    const createAgentMut = useMutation({
        mutationFn: superAgentApi.createAgent,
        onSuccess: () => {
            toast.success('Business Development Executive added to your team');
            setIsAddModalOpen(false);
            resetForm();
            qc.invalidateQueries({ queryKey: ['sa-team'] });
        },
        onError: (err: any) => {
            const errData = err.response?.data;
            if (errData?.errors) {
                const firstError = Object.values(errData.errors as Record<string, string[]>)[0]?.[0];
                toast.error(firstError || 'Validation failed.');
            } else {
                toast.error(errData?.message || 'Failed to add executive');
            }
        }
    });

    const resetForm = () => {
        setFormData({
            name: '', email: '', mobile: '', whatsapp_number: '', aadhaar_number: '',
            occupation: '', education_level: '', state: '', district: '', area: '',
            father_name: '', dob: '', blood_group: '', religion: '', gender: '', marital_status: '',
            permanent_address: '', current_address: '', pincode: '', landmark: '',
            pan_number: '', voter_id: '', bank_name: '', bank_account_number: '',
            bank_ifsc: '', bank_branch: '', upi_id: '', experience_years: '',
            languages_known: [], reference_name: '', reference_mobile: '',
        });
        setProfilePhoto({ file: null, name: '', preview: null });
        setAadhaarDoc({ file: null, name: '', preview: null });
        setPanDoc({ file: null, name: '', preview: null });
        setEducationCert({ file: null, name: '', preview: null });
        setResume({ file: null, name: '', preview: null });
        setMouSigned({ file: null, name: '', preview: null });
    };

    const agents: User[] = data?.data?.data ?? [];

    const handleAddExecutive = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!profilePhoto.file) { toast.error('Profile photo is required'); return; }
        if (!aadhaarDoc.file) { toast.error('Aadhaar document scan is required'); return; }

        const fd = new FormData();
        Object.entries(formData).forEach(([k, v]) => {
            if (v !== undefined && v !== null && v !== '') {
                if (Array.isArray(v)) {
                    v.forEach(item => fd.append(`${k}[]`, item));
                } else {
                    fd.append(k, String(v));
                }
            }
        });

        if (profilePhoto.file) fd.append('profile_photo', profilePhoto.file);
        if (aadhaarDoc.file) fd.append('aadhaar_document', aadhaarDoc.file);
        if (panDoc.file) fd.append('pan_document', panDoc.file);
        if (educationCert.file) fd.append('education_cert', educationCert.file);
        if (resume.file) fd.append('resume', resume.file);
        if (mouSigned.file) fd.append('mou_signed', mouSigned.file);

        createAgentMut.mutate(fd);
    };

    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-xl font-bold text-slate-800 tracking-tight">Team Management</h1>
                    <p className="text-sm text-slate-500 mt-0.5">Manage Business Development Executives assigned to your supervision</p>
                </div>
                <div className="flex items-center gap-3">
                    <span className="bg-orange-100 text-orange-700 text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full border border-orange-200">
                        {data?.data?.total ?? 0} Executives
                    </span>
                    <button
                        onClick={() => setIsAddModalOpen(true)}
                        className="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-200"
                    >
                        <Plus size={16} /> Add Executive
                    </button>
                </div>
            </div>

            {/* Filters */}
            <div className="flex flex-wrap gap-3">
                <div className="relative">
                    <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Search by name, mobile, agent ID..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 w-72"
                    />
                </div>
                <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            {/* Business Development Executive Grid */}
            {isLoading ? (
                <div className="text-center py-12 text-slate-400">Loading team...</div>
            ) : agents.length === 0 ? (
                <div className="text-center py-16 bg-white rounded-xl border border-dashed border-slate-300">
                    <Users size={40} className="mx-auto text-slate-300 mb-3" />
                    <p className="font-semibold text-slate-500">No agents in your team</p>
                    <p className="text-sm text-slate-400 mt-1">Contact admin to assign agents to your team.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    {agents.map((agent) => (
                        <div key={agent.id} className="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
                            <div className="flex items-start justify-between mb-3">
                                <div>
                                    <p className="font-semibold text-slate-800">{agent.name}</p>
                                    <p className="text-xs text-slate-400 font-mono">{agent.agent_id}</p>
                                </div>
                                <span className={`px-2 py-0.5 rounded text-xs font-medium ${agent.status === 'active' ? 'bg-green-100 text-green-700' :
                                    agent.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-red-100 text-red-700'
                                    }`}>
                                    {agent.status}
                                </span>
                            </div>
                            <div className="space-y-1 text-xs text-slate-500">
                                <p>📱 {agent.mobile}</p>
                                <p>📍 {agent.district}, {agent.state}</p>
                            </div>
                            <Link
                                to={`/super-agent/team/${agent.id}`}
                                className="mt-4 block text-center text-sm font-medium text-orange-600 hover:text-orange-700 py-2 border border-orange-200 rounded-lg hover:bg-orange-50 transition-colors"
                            >
                                View Details
                            </Link>
                        </div>
                    ))}
                </div>
            )}

            {/* Add Executive Modal */}
            {isAddModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-white rounded-3xl w-full max-w-4xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 max-h-[95vh] flex flex-col">
                        <div className="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between shrink-0">
                            <div className="flex items-center gap-3">
                                <div className="p-2.5 bg-slate-900 text-white rounded-2xl shadow-lg shadow-slate-200">
                                    <UserPlus size={20} />
                                </div>
                                <h2 className="text-lg font-bold text-slate-800 tracking-tight">Add New Executive</h2>
                            </div>
                            <button onClick={() => setIsAddModalOpen(false)} className="p-2 hover:bg-slate-200 rounded-xl text-slate-400 transition-colors">
                                <X size={20} />
                            </button>
                        </div>

                        <form onSubmit={handleAddExecutive} className="p-8 overflow-y-auto custom-scrollbar bg-white">
                            {/* Section 1: Basic Info */}
                            <div className="mb-10">
                                <div className="flex items-center gap-2 mb-6 border-b border-slate-100 pb-2">
                                    <div className="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                                        <Info size={16} />
                                    </div>
                                    <h3 className="font-bold text-slate-800 uppercase tracking-wider text-xs">Personal Profile</h3>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                                    <div className="space-y-1.5 sm:col-span-1">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Full Legal Name *</label>
                                        <input required value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} type="text" placeholder="As per Aadhaar" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Father's Name</label>
                                        <input value={formData.father_name} onChange={e => setFormData({ ...formData, father_name: e.target.value })} type="text" placeholder="S/o or D/o name" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Date of Birth</label>
                                        <input value={formData.dob} onChange={e => setFormData({ ...formData, dob: e.target.value })} type="date" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Blood Group</label>
                                        <select value={formData.blood_group} onChange={e => setFormData({ ...formData, blood_group: e.target.value })} className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 outline-none focus:border-slate-300 transition-all font-bold text-sm">
                                            <option value="">Select...</option>
                                            {['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'].map(bg => <option key={bg} value={bg}>{bg}</option>)}
                                        </select>
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Gender</label>
                                        <select value={formData.gender} onChange={e => setFormData({ ...formData, gender: e.target.value })} className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 outline-none focus:border-slate-300 transition-all font-bold text-sm">
                                            <option value="">Select...</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Marital Status</label>
                                        <select value={formData.marital_status} onChange={e => setFormData({ ...formData, marital_status: e.target.value })} className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 outline-none focus:border-slate-300 transition-all font-bold text-sm">
                                            <option value="">Select...</option>
                                            <option value="single">Single</option>
                                            <option value="married">Married</option>
                                            <option value="divorced">Divorced</option>
                                            <option value="widowed">Widowed</option>
                                        </select>
                                    </div>
                                    <MobileInput label="Mobile Number *" value={formData.mobile} onChange={val => setFormData({ ...formData, mobile: val })} required className="col-span-1" />
                                    <MobileInput label="WhatsApp Number *" value={formData.whatsapp_number} onChange={val => setFormData({ ...formData, whatsapp_number: val })} required className="col-span-1" />
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Email (Optional)</label>
                                        <input value={formData.email} onChange={e => setFormData({ ...formData, email: e.target.value })} type="email" placeholder="email@example.com" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                </div>
                            </div>

                            {/* Section 2: Location & Address */}
                            <div className="mb-10">
                                <div className="flex items-center gap-2 mb-6 border-b border-slate-100 pb-2">
                                    <div className="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                        <MapPin size={16} />
                                    </div>
                                    <h3 className="font-bold text-slate-800 uppercase tracking-wider text-xs">Location &amp; Address</h3>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                                    <div className="space-y-1.5 font-bold">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">State *</label>
                                        <select required value={formData.state} onChange={e => setFormData({ ...formData, state: e.target.value, district: '' })} className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 outline-none focus:border-slate-300 transition-all font-bold text-sm">
                                            <option value="">Select State</option>
                                            {INDIAN_STATES.map(s => <option key={s} value={s}>{s}</option>)}
                                        </select>
                                    </div>
                                    <div className="space-y-1.5 font-bold">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">District *</label>
                                        <select required value={formData.district} onChange={e => setFormData({ ...formData, district: e.target.value })} disabled={!formData.state} className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 outline-none focus:border-slate-300 transition-all font-bold text-sm disabled:bg-slate-100 disabled:text-slate-400">
                                            <option value="">Select District</option>
                                            {formData.state && STATE_DISTRICTS[formData.state]?.map(d => (<option key={d} value={d}>{d}</option>))}
                                        </select>
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Area / Village *</label>
                                        <input required value={formData.area} onChange={e => setFormData({ ...formData, area: e.target.value })} type="text" placeholder="Enter Area" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5 md:col-span-2">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Full Current Address</label>
                                        <input value={formData.current_address} onChange={e => setFormData({ ...formData, current_address: e.target.value })} type="text" placeholder="House No, Street, Landmark" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Pincode</label>
                                        <input value={formData.pincode} onChange={e => setFormData({ ...formData, pincode: e.target.value })} type="text" maxLength={6} placeholder="000 000" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                </div>
                            </div>

                            {/* Section 3: Identity & Bank */}
                            <div className="mb-10">
                                <div className="flex items-center gap-2 mb-6 border-b border-slate-100 pb-2">
                                    <div className="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                                        <CreditCard size={16} />
                                    </div>
                                    <h3 className="font-bold text-slate-800 uppercase tracking-wider text-xs">Identity &amp; Banking</h3>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Aadhaar Number *</label>
                                        <input required value={formData.aadhaar_number} onChange={e => setFormData({ ...formData, aadhaar_number: e.target.value })} type="text" pattern="\d{12}" maxLength={12} placeholder="0000 0000 0000" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">PAN Card Number</label>
                                        <input value={formData.pan_number} onChange={e => setFormData({ ...formData, pan_number: e.target.value.toUpperCase() })} type="text" maxLength={10} placeholder="ABCDE1234F" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Voter ID</label>
                                        <input value={formData.voter_id} onChange={e => setFormData({ ...formData, voter_id: e.target.value.toUpperCase() })} type="text" placeholder="XYZ1234567" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Bank Name</label>
                                        <input value={formData.bank_name} onChange={e => setFormData({ ...formData, bank_name: e.target.value })} type="text" placeholder="e.g. SBI, HDFC" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Account Number</label>
                                        <input value={formData.bank_account_number} onChange={e => setFormData({ ...formData, bank_account_number: e.target.value })} type="text" placeholder="000000000000" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">IFSC Code</label>
                                        <input value={formData.bank_ifsc} onChange={e => setFormData({ ...formData, bank_ifsc: e.target.value.toUpperCase() })} type="text" placeholder="SBIN0001234" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                </div>
                            </div>

                            {/* Section 4: Qualification & Experience */}
                            <div className="mb-10">
                                <div className="flex items-center gap-2 mb-6 border-b border-slate-100 pb-2">
                                    <div className="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                                        <FileText size={16} />
                                    </div>
                                    <h3 className="font-bold text-slate-800 uppercase tracking-wider text-xs">Experience &amp; Qualification</h3>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Occupation *</label>
                                        <input required value={formData.occupation} onChange={e => setFormData({ ...formData, occupation: e.target.value })} type="text" placeholder="Current job/title" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Education Level</label>
                                        <select value={formData.education_level} onChange={e => setFormData({ ...formData, education_level: e.target.value })} className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 outline-none focus:border-slate-300 transition-all font-bold text-sm">
                                            <option value="">Select Level</option>
                                            <option value="8th">8th Pass</option>
                                            <option value="10th">10th Pass</option>
                                            <option value="12th">12th Pass</option>
                                            <option value="graduate">Graduate</option>
                                            <option value="post_graduate">Post Graduate</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Exp. Years</label>
                                        <input value={formData.experience_years} onChange={e => setFormData({ ...formData, experience_years: e.target.value })} type="number" placeholder="0" className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-bold focus:border-slate-300 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium text-sm" />
                                    </div>
                                </div>
                            </div>

                            <div className="mb-8">
                                <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1 block mb-3">Required Documents</label>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    <DocUpload label="Profile Photo" required value={profilePhoto} onChange={setProfilePhoto} icon={ImageIcon} accept=".jpg,.jpeg,.png" />
                                    <DocUpload label="Aadhaar Doc" required value={aadhaarDoc} onChange={setAadhaarDoc} icon={CreditCard} accept=".jpg,.jpeg,.png,.pdf" />
                                    <DocUpload label="PAN Card" value={panDoc} onChange={setPanDoc} icon={Hash} accept=".jpg,.jpeg,.png,.pdf" />
                                    <DocUpload label="Education Cert" value={educationCert} onChange={setEducationCert} icon={FileText} accept=".jpg,.jpeg,.png,.pdf" />
                                    <DocUpload label="Resume / CV" value={resume} onChange={setResume} icon={Upload} accept=".pdf,.doc,.docx" />
                                    <DocUpload label="Signed MoU" value={mouSigned} onChange={setMouSigned} icon={FileText} accept=".jpg,.jpeg,.png,.pdf" />
                                </div>
                            </div>

                            <div className="bg-orange-50 border border-orange-100 rounded-2xl p-4 mb-8 flex items-start gap-3">
                                <Info size={18} className="text-orange-500 shrink-0 mt-0.5" />
                                <p className="text-xs text-orange-900 leading-relaxed font-medium">
                                    The executive will be added as <span className="font-bold">Pending</span> until documents are verified by Admin. Their activation password will be <span className="font-bold">Welcome@123</span>.
                                </p>
                            </div>

                            <div className="flex gap-4">
                                <button
                                    type="button"
                                    onClick={() => setIsAddModalOpen(false)}
                                    className="flex-1 px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all active:scale-95"
                                >
                                    Discard
                                </button>
                                <button
                                    type="submit"
                                    disabled={createAgentMut.isPending}
                                    className="flex-2 px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-800 shadow-xl shadow-slate-200 transition-all active:scale-95 disabled:opacity-40"
                                >
                                    {createAgentMut.isPending ? 'Provisioning...' : 'Add Executive'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}

// ====== Sub-components ======

function DocUpload({
    label, required, accept, value, onChange, icon: Icon,
}: {
    label: string; required?: boolean; accept?: string;
    value: any; onChange: (v: any) => void;
    icon: any;
}) {
    const ref = useRef<HTMLInputElement>(null);

    const handleFile = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] ?? null;
        if (!file) return;
        const preview = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
        onChange({ file, name: file.name, preview });
    };

    const clear = () => {
        onChange({ file: null, name: '', preview: null });
        if (ref.current) ref.current.value = '';
    };

    return (
        <div className="flex flex-col gap-1">
            <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">
                {label} {required && <span className="text-danger">*</span>}
            </label>
            <div
                onClick={() => !value.file && ref.current?.click()}
                className={`relative border-2 border-dashed rounded-2xl p-4 text-center cursor-pointer min-h-[100px]
                    flex flex-col items-center justify-center gap-2 transition-all
                    ${value.file
                        ? 'border-green-300 bg-green-50'
                        : 'border-slate-200 bg-slate-50 hover:border-slate-300 hover:bg-slate-100'}`}
            >
                <input ref={ref} type="file" accept={accept} className="hidden" onChange={handleFile} />
                {value.file ? (
                    <>
                        {value.preview
                            ? <img src={value.preview} alt="preview" className="max-h-12 rounded-lg object-contain shadow-sm" />
                            : <FileText className="w-8 h-8 text-green-500" />
                        }
                        <p className="text-[10px] font-bold text-green-700 truncate max-w-full px-2">{value.name}</p>
                        <button
                            type="button"
                            onClick={(e) => { e.stopPropagation(); clear(); }}
                            className="absolute top-2 right-2 w-6 h-6 rounded-xl bg-red-100 flex items-center justify-center text-red-500 hover:bg-red-200 transition-colors"
                        >
                            <X size={12} />
                        </button>
                    </>
                ) : (
                    <>
                        <Icon className="w-6 h-6 text-slate-300" />
                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Upload {label}</p>
                    </>
                )}
            </div>
        </div>
    );
}
