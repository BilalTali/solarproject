import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { 
    Search, LayoutList, MapPin, Tag
} from 'lucide-react';
import toast from 'react-hot-toast';
import api from '@/services/axios';
import { ApiResponse, Lead, PaginatedResponse, User } from '@/types';
import { LeadDocumentsModal } from '@/components/leads/LeadDocumentsModal';

export default function SuperAdminMonitorLeadsPage() {
    const queryClient = useQueryClient();
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');

    // Fetch leads
    const { data: res, isLoading } = useQuery({
        queryKey: ['super-admin', 'monitor-leads', search, status],
        queryFn: async () => {
            const response = await api.get<ApiResponse<PaginatedResponse<Lead>>>('/super-admin/monitor/leads', {
                params: { search, status, per_page: 10 }
            });
            return response.data;
        }
    });

    // Fetch Admins list for assignment
    const { data: adminsRes } = useQuery({
        queryKey: ['super-admin', 'all-admins'],
        queryFn: async () => {
            const response = await api.get<ApiResponse<PaginatedResponse<User>>>('/super-admin/admins', {
                params: { per_page: 100 }
            });
            return response.data;
        }
    });

    const assignMutation = useMutation({
        mutationFn: ({ ulid, admin_id }: { ulid: string, admin_id: number }) => 
            api.put(`/super-admin/monitor/leads/${ulid}/assign-admin`, { admin_id }),
        onSuccess: () => {
            toast.success('Lead assigned to admin successfully!');
            queryClient.invalidateQueries({ queryKey: ['super-admin', 'monitor-leads'] });
        },
        onError: () => toast.error('Failed to assign lead')
    });

    const leads = res?.data?.data || [];
    const admins = adminsRes?.data?.data || [];

    const handleAssign = (ulid: string, admin_id: string) => {
        if (!admin_id) return;
        assignMutation.mutate({ ulid, admin_id: parseInt(admin_id) });
    };

    return (
        <div className="space-y-6 animate-in fade-in duration-500">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 className="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <LayoutList className="w-6 h-6 text-orange-500" />
                        Monitoring: Platform Leads
                    </h2>
                    <p className="text-slate-500 text-sm font-medium">Read-only oversight of the national lead pipeline and status distribution.</p>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div className="md:col-span-8 relative">
                    <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Search by customer name or consumer number..."
                        className="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all font-medium text-slate-700 shadow-sm"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                </div>
                <div className="md:col-span-4">
                   <select 
                     className="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all font-bold text-slate-700 shadow-sm appearance-none"
                     value={status}
                     onChange={(e) => setStatus(e.target.value)}
                   >
                       <option value="">All Statuses</option>
                       <option value="NEW">New</option>
                       <option value="ON_HOLD">On Hold</option>
                       <option value="INVALID">Invalid</option>
                       <option value="DUPLICATE">Duplicate</option>
                       <option value="REJECTED">Rejected</option>
                       <option value="REGISTERED">Registered</option>
                       <option value="SITE_SURVEY">Site Survey</option>
                       <option value="AT_BANK">At Bank</option>
                       <option value="COMPLETED">Completed</option>
                       <option value="PROJECT_COMMISSIONING">Project Commissioning</option>
                       <option value="SUBSIDY_REQUEST">Subsidy Request</option>
                       <option value="SUBSIDY_APPLIED">Subsidy Applied</option>
                       <option value="SUBSIDY_DISBURSED">Subsidy Disbursed</option>
                   </select>
                </div>
            </div>

            <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50/50 border-b border-slate-100">
                                <th className="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Customer / Consumer</th>
                                <th className="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Location</th>
                                <th className="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Assigned Team</th>
                                <th className="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th className="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Docs</th>
                                <th className="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-50">
                            {isLoading ? (
                                Array(5).fill(0).map((_, i) => (
                                    <tr key={i} className="animate-pulse"><td colSpan={6} className="px-6 py-8"><div className="h-4 bg-slate-100 rounded-full w-full" /></td></tr>
                                ))
                            ) : leads.map((lead) => (
                                <tr key={lead.id} className="group hover:bg-slate-50/50 transition-colors">
                                    <td className="px-6 py-5">
                                        <div className="flex flex-col">
                                            <p className="font-bold text-slate-900 leading-tight">{lead.beneficiary_name}</p>
                                            <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{lead.consumer_number || 'No Number'}</p>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex items-center gap-1.5 text-xs font-bold text-slate-600">
                                            <MapPin className="w-3 h-3 text-slate-400" />
                                            {lead.beneficiary_district}, {lead.beneficiary_state}
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="space-y-1">
                                            {lead.assigned_super_agent && (
                                                <div className="flex items-center gap-1.5 text-[10px] font-black text-indigo-600 uppercase">
                                                    <Tag className="w-2.5 h-2.5" />
                                                    BDM: {lead.assigned_super_agent.name}
                                                </div>
                                            )}
                                            {lead.assigned_agent && (
                                                <div className="flex items-center gap-1.5 text-[10px] font-black text-blue-600 uppercase">
                                                    <Tag className="w-2.5 h-2.5" />
                                                    BDE: {lead.assigned_agent.name}
                                                </div>
                                            )}
                                            {lead.assigned_admin_id && lead.assigned_admin ? (
                                                <div className="flex items-center gap-1.5 text-[10px] font-black text-rose-600 uppercase mt-1">
                                                    <Tag className="w-2.5 h-2.5" />
                                                    Admin: {lead.assigned_admin.name}
                                                </div>
                                            ) : !lead.assigned_agent && !lead.assigned_super_agent && (
                                                <div className="mt-1 flex items-center gap-2">
                                                    <select 
                                                        onChange={(e) => handleAssign(lead.ulid, e.target.value)}
                                                        className="text-[10px] uppercase font-bold text-slate-500 bg-slate-50 border border-slate-200 rounded px-2 py-1 max-w-[120px]"
                                                        defaultValue=""
                                                        disabled={assignMutation.isPending}
                                                    >
                                                        <option value="" disabled>Assign Admin...</option>
                                                        {admins.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                                    </select>
                                                </div>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <span className="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-slate-100 text-slate-600 uppercase tracking-widest border border-slate-200">
                                            {lead.status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-5">
                                        <LeadDocumentsModal ulid={lead.ulid} triggerButtonText="Docs" buttonClassName="px-2 py-1 bg-white border border-slate-200 rounded-md text-[10px] font-bold text-slate-600 hover:bg-slate-50 transition-colors inline-flex items-center gap-1 uppercase tracking-wider" />
                                    </td>
                                    <td className="px-6 py-5 text-right font-medium text-slate-400 text-[10px] uppercase">
                                        {new Date(lead.created_at).toLocaleDateString()}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
