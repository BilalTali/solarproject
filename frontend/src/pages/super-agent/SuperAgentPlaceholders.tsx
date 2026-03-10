import { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
    ArrowLeft, User, Phone, MapPin, Hash, Building2, FileText,
    Calendar, AlertCircle, FileDigit, Download, Image as ImageIcon,
    Camera, Mail, Shield, BadgeCheck, Save
} from 'lucide-react';
import { superAgentApi } from '@/api/superAgent.api';
import { openAuthenticatedFile } from '@/utils/documentUtils';
import { authApi } from '@/api/auth.api';
import { useAuthStore } from '@/store/authStore';
import toast from 'react-hot-toast';
import type { Lead, CommissionPrompt } from '@/types';
import CommissionInlineEntryForAgent from '@/components/super-agent/CommissionInlineEntryForAgent';
import { STATE_DISTRICTS, INDIAN_STATES } from '@/constants/locationData';
import ChangePasswordForm from '@/components/shared/ChangePasswordForm';

const STATUS_BADGE: Record<string, string> = {
    new: 'bg-blue-100 text-blue-700',
    contacted: 'bg-purple-100 text-purple-700',
    documents_collected: 'bg-indigo-100 text-indigo-700',
    registered: 'bg-cyan-100 text-cyan-700',
    site_survey: 'bg-teal-100 text-teal-700',
    installation_pending: 'bg-orange-100 text-orange-700',
    installed: 'bg-green-100 text-green-700',
    subsidy_applied: 'bg-lime-100 text-lime-700',
    completed: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
    on_hold: 'bg-yellow-100 text-yellow-700',
};

const ALL_STATUSES = Object.keys(STATUS_BADGE);

function label(status: string) { return status.replace(/_/g, ' '); }
function fmt(iso: string) { return new Date(iso).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); }

export function SuperAgentLeadDetailPage() {
    const { ulid } = useParams<{ ulid: string }>();
    const qc = useQueryClient();

    // Status update states
    const [newStatus, setNewStatus] = useState('');
    const [statusNote, setStatusNote] = useState('');

    const { data, isLoading } = useQuery({
        queryKey: ['sa-lead-detail', ulid],
        queryFn: () => superAgentApi.getLeadDetail(ulid!),
        enabled: !!ulid,
    });

    // team agents for assignment
    const { data: teamData } = useQuery({
        queryKey: ['sa-my-team'],
        queryFn: () => superAgentApi.getMyTeam({ per_page: 200 }),
    });

    const lead: Lead | undefined = data?.data;
    const teamAgents = (teamData?.data?.data ?? []) as any[];

    const [activeCommissionPrompt, setActiveCommissionPrompt] = useState<CommissionPrompt | null>(null);
    const [assignAgentId, setAssignAgentId] = useState<number | string>('');

    const statusMut = useMutation({
        mutationFn: ({ ulid, status, notes }: { ulid: string; status: string; notes?: string }) =>
            superAgentApi.updateLeadStatus(ulid, { status, notes }),
        onSuccess: (res: any) => {
            qc.invalidateQueries({ queryKey: ['sa-lead-detail', ulid] });
            qc.invalidateQueries({ queryKey: ['sa-leads'] });
            toast.success('Lead status updated');
            setNewStatus('');
            setStatusNote('');

            if (res.data?.commission_prompt?.should_prompt) {
                setActiveCommissionPrompt(res.data.commission_prompt);
            } else {
                setActiveCommissionPrompt(null);
            }
        },
        onError: () => toast.error('Failed to update status.'),
    });

    const assignMut = useMutation({
        mutationFn: ({ ulid, agent_id }: { ulid: string; agent_id: number }) =>
            superAgentApi.assignLead(ulid, agent_id),
        onSuccess: () => {
            qc.invalidateQueries({ queryKey: ['sa-lead-detail', ulid] });
            qc.invalidateQueries({ queryKey: ['sa-leads'] });
            toast.success('Lead assigned to agent');
            setAssignAgentId('');
        },
        onError: () => toast.error('Failed to assign lead.'),
    });

    if (isLoading) {
        return <div className="p-8 text-center text-slate-500">Loading lead details...</div>;
    }

    if (!lead) {
        return <div className="p-8 text-center text-red-500">Lead not found.</div>;
    }

    // Initialize status dropdown if not set
    if (!newStatus && lead.status) {
        setNewStatus(lead.status);
    }

    const docs = lead.documents || [];
    const getDoc = (type: string) => docs.find(d => d.document_type === type);

    const docTypes = [
        { key: 'aadhaar', label: 'Aadhaar Card' },
        { key: 'electricity_bill', label: 'Electricity Bill' },
        { key: 'other', label: 'PAN Card' },
        { key: 'photo', label: 'Beneficiary Photo' },
        { key: 'solar_roof_photo', label: 'Solar Roof Photo' },
        { key: 'bank_passbook', label: 'Bank Passbook' }
    ];

    return (
        <div className="space-y-6 max-w-5xl mx-auto pb-12">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <Link to="/super-agent/leads" className="p-2 hover:bg-slate-100 rounded-lg text-slate-500 transition-colors">
                        <ArrowLeft size={20} />
                    </Link>
                    <div>
                        <h1 className="text-xl font-bold text-slate-800 flex items-center gap-3">
                            {lead.beneficiary_name}
                            <span className={`px-2 py-0.5 rounded text-xs font-medium capitalize ${STATUS_BADGE[lead.status] ?? 'bg-slate-100 text-slate-600'}`}>
                                {label(lead.status)}
                            </span>
                        </h1>
                        <p className="text-sm text-slate-500 mt-0.5 font-mono">{lead.ulid}</p>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {/* ── Left Column: Details ── */}
                <div className="lg:col-span-2 space-y-6">

                    {/* Beneficiary Info */}
                    <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h2 className="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Beneficiary Details</h2>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="flex gap-3">
                                <User size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">Full Name</p>
                                    <p className="font-medium text-slate-800">{lead.beneficiary_name}</p>
                                </div>
                            </div>
                            <div className="flex gap-3">
                                <Phone size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">Mobile Number</p>
                                    <p className="font-medium text-slate-800">{lead.beneficiary_mobile}</p>
                                </div>
                            </div>
                            {lead.beneficiary_email && (
                                <div className="flex gap-3 sm:col-span-2">
                                    <AlertCircle size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                    <div>
                                        <p className="text-xs text-slate-600">Email Address</p>
                                        <p className="font-medium text-slate-800">{lead.beneficiary_email}</p>
                                    </div>
                                </div>
                            )}
                            <div className="flex gap-3 sm:col-span-2">
                                <MapPin size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">Full Address</p>
                                    <p className="font-medium text-slate-800 uppercase">
                                        {[lead.beneficiary_address, lead.beneficiary_district, lead.beneficiary_state, lead.beneficiary_pincode].filter(Boolean).join(', ')}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Technical Info */}
                    <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h2 className="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Technical & System Requirements</h2>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="flex gap-3">
                                <Building2 size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">DISCOM</p>
                                    <p className="font-medium text-slate-800">{lead.discom_name || '—'}</p>
                                </div>
                            </div>
                            <div className="flex gap-3">
                                <Hash size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">Consumer Number</p>
                                    <p className="font-medium text-slate-800">{lead.consumer_number || '—'}</p>
                                </div>
                            </div>
                            <div className="flex gap-3">
                                <FileDigit size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">Monthly Bill (₹)</p>
                                    <p className="font-medium text-slate-800">{lead.monthly_bill_amount ? `₹${lead.monthly_bill_amount}` : '—'}</p>
                                </div>
                            </div>
                            <div className="flex gap-3">
                                <MapPin size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">Roof Size</p>
                                    <p className="font-medium text-slate-800 capitalize">{lead.roof_size?.replace(/_/g, ' ') || '—'}</p>
                                </div>
                            </div>
                            <div className="flex gap-3 sm:col-span-2">
                                <AlertCircle size={16} className="text-slate-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-xs text-slate-600">System Capacity</p>
                                    <p className="font-medium text-slate-800 capitalize">
                                        {lead.system_capacity?.replace(/_/g, ' ') || '—'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Query Message */}
                    {lead.query_message && (
                        <div className="bg-orange-50 rounded-xl border border-orange-100 p-6">
                            <h2 className="text-sm font-bold text-orange-800 uppercase tracking-wider mb-2">Query / Message</h2>
                            <p className="text-sm text-orange-900 leading-relaxed bg-white/50 p-4 rounded-lg border border-orange-200/50">
                                {lead.query_message}
                            </p>
                        </div>
                    )}

                    {/* Documents */}
                    <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h2 className="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <FileText size={16} /> Attached Documents
                        </h2>
                        {docs.length === 0 ? (
                            <p className="text-sm text-slate-500 italic">No documents uploaded for this lead.</p>
                        ) : (
                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                {docTypes.map(dtype => {
                                    const doc = getDoc(dtype.key);
                                    if (!doc) return null;

                                    return (
                                        <div key={dtype.key} className="border border-slate-200 rounded-xl p-3 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                                            <div className="w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                                                <ImageIcon size={20} />
                                            </div>
                                            <div>
                                                <p className="text-xs font-semibold text-slate-800">{dtype.label}</p>
                                            </div>
                                            <button
                                                onClick={() => openAuthenticatedFile(doc.download_url, doc.original_filename)}
                                                className="mt-1 text-xs px-3 py-1 bg-white border border-slate-200 rounded shadow-sm hover:border-orange-500 hover:text-orange-600 flex items-center gap-1 transition-colors"
                                            >
                                                <Download size={12} /> View
                                            </button>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                </div>

                {/* ── Right Column: Actions & Log ── */}
                <div className="space-y-6">

                    {/* Status Update Card / Commission Entry */}
                    {(activeCommissionPrompt || (lead.status === 'completed' && lead.assigned_agent)) ? (
                        <div className="bg-orange-50 rounded-xl border border-orange-200 shadow-sm p-0 mb-6 overflow-hidden">
                            <CommissionInlineEntryForAgent
                                leadUlid={lead.ulid}
                                leadStatus={lead.status}
                                commissionPrompt={activeCommissionPrompt || undefined}
                                existingCommission={lead.formatted_commissions?.agent_commission || null}
                                agentName={activeCommissionPrompt?.payee_name || lead.assigned_agent?.name || 'Business Development Executive'}
                                agentCode={activeCommissionPrompt?.payee_code || lead.assigned_agent?.agent_id || ''}
                                onSaved={() => {
                                    setActiveCommissionPrompt(null);
                                    qc.invalidateQueries({ queryKey: ['sa-lead-detail', ulid] });
                                }}
                                onSkip={() => setActiveCommissionPrompt(null)}
                            />
                        </div>
                    ) : (
                        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                            <h2 className="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Update Status</h2>
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                                    <select
                                        value={newStatus}
                                        onChange={e => setNewStatus(e.target.value)}
                                        className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 capitalize"
                                    >
                                        {ALL_STATUSES.map(s => (
                                            <option key={s} value={s}>{label(s)}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-slate-600 mb-1">Notes (Optional)</label>
                                    <textarea
                                        value={statusNote}
                                        onChange={e => setStatusNote(e.target.value)}
                                        placeholder="Add context to this status change..."
                                        className="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none min-h-[80px]"
                                    />
                                </div>
                                <button
                                    disabled={newStatus === lead.status || statusMut.isPending}
                                    onClick={() => statusMut.mutate({ ulid: lead.ulid, status: newStatus, notes: statusNote || undefined })}
                                    className="w-full py-2.5 bg-orange-600 text-white rounded-lg text-sm font-bold hover:bg-orange-700 disabled:opacity-40 transition-colors"
                                >
                                    {statusMut.isPending ? 'Saving...' : 'Update Status'}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Business Development Executive Information & Assignment */}
                    <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h2 className="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Business Development Executive Assignment</h2>

                        {lead.submitted_by_agent && (
                            <div className="mb-4">
                                <p className="text-xs text-slate-600">Submitted By</p>
                                <p className="font-medium text-slate-800 text-sm">{lead.submitted_by_agent.name}</p>
                                <p className="text-xs text-slate-600">{lead.submitted_by_agent.mobile}</p>
                            </div>
                        )}

                        <div className="space-y-4">
                            <div>
                                <p className="text-xs text-slate-600 mb-1">Assigned To</p>
                                {lead.assigned_agent ? (
                                    <div className="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <p className="font-medium text-slate-800 text-sm">{lead.assigned_agent.name}</p>
                                        <p className="text-xs text-slate-600">{lead.assigned_agent.mobile}</p>
                                        <p className="text-[10px] text-slate-500 mt-1 uppercase tracking-tight">Business Development Executive ID: {lead.assigned_agent.agent_id || 'N/A'}</p>
                                    </div>
                                ) : (
                                    <div className="p-3 bg-orange-50/50 rounded-lg border border-orange-100 border-dashed">
                                        <p className="text-sm text-orange-600/70 italic">Not yet assigned to an agent</p>
                                    </div>
                                )}
                            </div>

                            {/* Re-assignment or initial assignment */}
                            <div className="pt-2 border-t border-slate-50">
                                <label className="block text-xs font-semibold text-slate-600 mb-2">
                                    {lead.assigned_agent ? 'Change Assignment' : 'Assign to Team Member'}
                                </label>
                                <div className="flex gap-2">
                                    <select
                                        value={assignAgentId}
                                        onChange={e => setAssignAgentId(e.target.value)}
                                        className="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    >
                                        <option value="">Select agent...</option>
                                        {teamAgents.map(a => (
                                            <option key={a.id} value={a.id}>{a.name} ({a.agent_id || 'no id'})</option>
                                        ))}
                                    </select>
                                    <button
                                        disabled={!assignAgentId || assignMut.isPending}
                                        onClick={() => assignMut.mutate({ ulid: lead.ulid, agent_id: Number(assignAgentId) })}
                                        className="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-900 disabled:opacity-40 transition-colors"
                                    >
                                        {assignMut.isPending ? '...' : lead.assigned_agent ? 'Reassign' : 'Assign'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Activity Log */}
                    {(lead.status_logs?.length ?? 0) > 0 && (
                        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                            <h2 className="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Status History</h2>
                            <ol className="relative border-l border-slate-200 space-y-5 ml-2">
                                {lead.status_logs!.map(log => (
                                    <li key={log.id} className="ml-4">
                                        <span className="absolute -left-1.5 w-3 h-3 rounded-full bg-slate-300 border-2 border-white" />
                                        <div className="flex flex-col gap-1">
                                            <div className="flex items-center gap-1.5 flex-wrap">
                                                <span className={`px-1.5 py-0.5 rounded text-[10px] uppercase tracking-wider font-semibold ${STATUS_BADGE[log.to_status] ?? 'bg-slate-100 text-slate-600'}`}>
                                                    {label(log.to_status)}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-1.5 text-xs text-slate-600">
                                                <Calendar size={12} />
                                                <span>{fmt(log.created_at)}</span>
                                            </div>
                                            {log.notes && (
                                                <p className="text-xs text-slate-600 italic bg-slate-50 p-2 rounded mt-1 border border-slate-100"> "{log.notes}"</p>
                                            )}
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    )}

                </div>
            </div>
        </div>
    );
}

export function SuperAgentNotificationsPage() {
    return (
        <div className="py-8 px-4">
            <h1 className="text-xl font-bold text-slate-800 mb-4">Notifications</h1>
            <div className="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">
                Your notifications will appear here.
            </div>
        </div>
    );
}

export function SuperAgentProfilePage() {
    const { user, setUser } = useAuthStore();
    const [editing, setEditing] = useState(false);
    const [editForm, setEditForm] = useState({
        whatsapp_number: user?.whatsapp_number ?? '',
        father_name: (user as any)?.father_name ?? '',
        dob: (user as any)?.dob ? (user as any).dob.split('T')[0] : '',
        blood_group: (user as any)?.blood_group ?? '',
        state: user?.state ?? '',
        district: user?.district ?? '',
        area: user?.area ?? '',
    });

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
                toast.success('Profile updated');
                setEditing(false);
            }
        },
        onError: (err: any) => {
            const msg = err.response?.data?.message || 'Failed to update profile';
            toast.error(msg);
        }
    });

    const startEdit = () => {
        setEditForm({
            whatsapp_number: user?.whatsapp_number ?? '',
            father_name: (user as any)?.father_name ?? '',
            dob: (user as any)?.dob ? (user as any).dob.split('T')[0] : '',
            blood_group: (user as any)?.blood_group ?? '',
            state: user?.state ?? '',
            district: user?.district ?? '',
            area: user?.area ?? '',
        });
        setEditing(true);
    };

    const handleSave = () => {
        updateProfileMutation.mutate(editForm);
    };

    return (
        <div className="max-w-4xl mx-auto py-8 px-4">
            <div className="flex items-center justify-between mb-8">
                <div>
                    <h1 className="text-2xl font-bold text-slate-800 tracking-tight">Manager Profile</h1>
                    <p className="text-slate-500 text-sm">View and manage your personal details and branding</p>
                </div>
                {!editing ? (
                    <button
                        onClick={startEdit}
                        className="flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-shadow hover:shadow-lg active:scale-95 transition-all"
                    >
                        Edit Profile
                    </button>
                ) : (
                    <div className="flex gap-2">
                        <button
                            onClick={() => setEditing(false)}
                            className="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            onClick={handleSave}
                            disabled={updateProfileMutation.isPending}
                            className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-green-700 transition-shadow hover:shadow-lg disabled:opacity-50"
                        >
                            <Save size={16} /> {updateProfileMutation.isPending ? 'Saving...' : 'Save Changes'}
                        </button>
                    </div>
                )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                {/* Left: Avatar Column */}
                <div className="md:col-span-1">
                    <div className="bg-white rounded-3xl border border-slate-200 p-8 flex flex-col items-center relative overflow-hidden shadow-sm">
                        <div className="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-orange-50 to-transparent" />

                        <div className="relative z-10">
                            <div className="w-32 h-32 rounded-[2rem] bg-slate-50 flex items-center justify-center overflow-hidden border-4 border-white shadow-xl relative group">
                                {user?.profile_photo_url ? (
                                    <img src={user.profile_photo_url} alt={user.name} className="w-full h-full object-cover transition-transform group-hover:scale-110" />
                                ) : (
                                    <div className="w-full h-full bg-gradient-to-br from-orange-100 to-orange-50 flex items-center justify-center">
                                        <User size={60} className="text-orange-300" />
                                    </div>
                                )}
                                <div className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Camera size={24} className="text-white" />
                                </div>
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

                        <div className="mt-6 text-center z-10">
                            <h3 className="text-xl font-bold text-slate-800">{user?.name}</h3>
                            <div className="flex items-center justify-center gap-1.5 mt-1">
                                <BadgeCheck size={14} className="text-orange-500" />
                                <span className="text-sm font-mono font-bold text-orange-600 uppercase tracking-wider">{user?.super_agent_code}</span>
                            </div>
                        </div>

                        <div className="mt-8 w-full space-y-3 z-10">
                            <div className="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <span className="text-xs font-bold text-slate-500 uppercase tracking-widest">Status</span>
                                <span className="px-2 py-1 bg-green-100 text-green-700 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                    {user?.status}
                                </span>
                            </div>
                            <div className="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <span className="text-xs font-bold text-slate-500 uppercase tracking-widest">Role</span>
                                <span className="text-xs font-bold text-slate-700 capitalize">
                                    {user?.role?.replace('_', ' ')}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right: Info Column */}
                <div className="md:col-span-2 space-y-6">
                    <div className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                        <div className="px-8 py-5 border-b border-slate-100 bg-slate-50/30">
                            <h3 className="font-bold text-slate-800 text-sm uppercase tracking-widest">
                                {editing ? 'Edit Personal Information' : 'Manager Identity'}
                            </h3>
                        </div>
                        <div className="p-8">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-8">
                                {!editing ? (
                                    <>
                                        <InfoBlock icon={<User size={14} />} label="Full Name" value={user?.name} />
                                        <InfoBlock icon={<Phone size={14} />} label="Primary Mobile" value={user?.mobile} />
                                        <InfoBlock icon={<Phone size={14} />} label="WhatsApp Number" value={user?.whatsapp_number || 'Not Provided'} />
                                        <InfoBlock icon={<Mail size={14} />} label="Email Address" value={user?.email || 'Not Provided'} />
                                        <InfoBlock icon={<User size={14} />} label="Father's Name" value={(user as any)?.father_name || 'Not Provided'} />
                                        <InfoBlock icon={<Calendar size={14} />} label="Date of Birth" value={(user as any)?.dob ? new Date((user as any).dob).toLocaleDateString() : 'Not Provided'} />
                                        <InfoBlock icon={<Shield size={14} />} label="Blood Group" value={(user as any)?.blood_group || 'Not Provided'} />
                                        <div className="sm:col-span-2">
                                            <InfoBlock icon={<MapPin size={14} />} label="Primary Location" value={[user?.area, user?.district, user?.state].filter(Boolean).join(', ') || 'Not Provided'} />
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <EditBlock label="WhatsApp Number" value={editForm.whatsapp_number} onChange={v => setEditForm({ ...editForm, whatsapp_number: v })} placeholder="10-digit mobile" />
                                        <EditBlock label="Father's Name" value={editForm.father_name} onChange={v => setEditForm({ ...editForm, father_name: v })} placeholder="S/o or D/o Name" />
                                        <EditBlock label="Date of Birth" value={editForm.dob} type="date" onChange={v => setEditForm({ ...editForm, dob: v })} />
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Blood Group</label>
                                            <select
                                                value={editForm.blood_group}
                                                onChange={e => setEditForm({ ...editForm, blood_group: e.target.value })}
                                                className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-3 text-slate-800 font-bold focus:border-orange-500 outline-none transition-colors shadow-inner"
                                            >
                                                <option value="">Select...</option>
                                                {['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'].map(bg => <option key={bg} value={bg}>{bg}</option>)}
                                            </select>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">State</label>
                                            <select
                                                value={editForm.state}
                                                onChange={e => setEditForm({ ...editForm, state: e.target.value, district: '' })}
                                                className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-3 text-slate-800 font-bold focus:border-orange-500 outline-none transition-colors shadow-inner"
                                            >
                                                <option value="">Select State</option>
                                                {INDIAN_STATES.map(s => <option key={s} value={s}>{s}</option>)}
                                            </select>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">District</label>
                                            <select
                                                value={editForm.district}
                                                onChange={e => setEditForm({ ...editForm, district: e.target.value })}
                                                disabled={!editForm.state}
                                                className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-3 text-slate-800 font-bold focus:border-orange-500 outline-none transition-colors shadow-inner disabled:opacity-50"
                                            >
                                                <option value="">Select District</option>
                                                {(STATE_DISTRICTS[editForm.state] || []).map(d => <option key={d} value={d}>{d}</option>)}
                                            </select>
                                        </div>
                                        <div className="sm:col-span-2">
                                            <EditBlock label="Serving Area" value={editForm.area} onChange={v => setEditForm({ ...editForm, area: v })} />
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>

                    {!editing && (
                        <>
                            <ChangePasswordForm />

                            <div className="bg-gradient-to-r from-orange-400 to-orange-600 rounded-3xl p-8 text-white shadow-xl shadow-orange-100 relative overflow-hidden">
                                <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl" />
                                <div className="flex items-start gap-4 relative z-10">
                                    <div className="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                                        <AlertCircle className="text-white" />
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-lg mb-2">Notice for Profile Updates</h4>
                                        <p className="text-white/80 text-sm leading-relaxed mb-6">
                                            Major identity changes (Name, Email, Mobile) require verification. To change these, please submit a request to the Admin portal for security auditing.
                                        </p>
                                        <button className="flex items-center gap-2 px-6 py-3 bg-white text-orange-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-lg transition-all active:scale-95">
                                            Help Center
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>
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

const EditBlock = ({ label, value, onChange, placeholder, type = "text" }: { label: string, value: string, onChange: (v: string) => void, placeholder?: string, type?: string }) => (
    <div className="space-y-2">
        <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">{label}</label>
        <input
            type={type}
            value={value}
            onChange={e => onChange(e.target.value)}
            placeholder={placeholder}
            className="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-3 text-slate-800 font-bold focus:border-orange-500 outline-none transition-colors shadow-inner"
        />
    </div>
);
