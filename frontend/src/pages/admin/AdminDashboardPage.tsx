import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import api from '@/api/axios';
import type { AdminDashboardStats } from '@/types';
import LoadingSpinner from '@/components/shared/LoadingSpinner';
import LeadStatusBadge from '@/components/shared/LeadStatusBadge';
import { formatCurrency, formatDate, STATUS_LABELS } from '@/utils/formatters';
import { ArrowRight, Users, TrendingUp, CheckCircle2, Clock, IndianRupee } from 'lucide-react';
import { DownloadIdCardButton } from '@/components/shared/DownloadIdCardButton';
import DownloadJoiningLetterButton from '@/components/shared/DownloadJoiningLetterButton';
import { useAuthStore } from '@/store/authStore';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, Cell } from 'recharts';

const PIPELINE_COLORS: Record<string, string> = {
    new: '#94A3B8',
    registered: '#818CF8',
    installed: '#34D399',
    completed: '#4ADE80',
    rejected: '#F87171',
    on_hold: '#FCD34D',
};

export default function AdminDashboardPage() {
    const { user } = useAuthStore();
    const { data, isLoading } = useQuery({
        queryKey: ['admin-dashboard'],
        queryFn: async () => {
            const res = await api.get<{ success: boolean; data: AdminDashboardStats }>('/admin/dashboard/stats');
            return res.data.data;
        },
    });

    if (isLoading) return <LoadingSpinner text="Loading admin dashboard..." />;

    const kpis = data?.kpis;
    const stats = [
        { icon: <TrendingUp className="w-6 h-6" />, label: 'Total Leads', value: kpis?.total_leads ?? 0, color: 'border-primary text-primary', sub: `${kpis?.new_leads_today ?? 0} today` },
        { icon: <CheckCircle2 className="w-6 h-6" />, label: 'Installations', value: kpis?.total_installations ?? 0, color: 'border-success text-success', sub: `${kpis?.installations_this_month ?? 0} this month` },
        { icon: <Users className="w-6 h-6" />, label: 'Active Business Development Executives', value: kpis?.active_agents ?? 0, color: 'border-accent text-accent', sub: `${kpis?.pending_agents ?? 0} pending` },
        { icon: <IndianRupee className="w-6 h-6" />, label: 'Commission Paid', value: formatCurrency(kpis?.total_commission_paid ?? 0), color: 'border-warning text-warning', sub: `${formatCurrency(kpis?.pending_commission ?? 0)} pending` },
    ];

    // Pipeline chart data
    const pipelineData = data?.pipeline
        ? Object.entries(data.pipeline)
            .filter(([, count]) => Number(count) > 0)
            .map(([status, count]) => ({
                status: STATUS_LABELS[status as keyof typeof STATUS_LABELS] || status,
                rawStatus: status,
                count: Number(count),
            }))
        : [];

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="font-display font-bold text-2xl text-dark">Admin Dashboard</h1>
                    <p className="text-neutral-600 text-sm mt-1">Overview of all portal activity</p>
                </div>
                <div className="flex gap-3">
                    <DownloadIdCardButton />
                    <DownloadJoiningLetterButton user={user!} variant="button" />
                    <Link to="/admin/leads" className="btn-primary text-sm py-2 px-4">View All Leads</Link>
                </div>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                {stats.map((stat) => (
                    <div key={stat.label} className={`stat-card ${stat.color}`}>
                        <div>
                            <p className="text-xs text-neutral-600 font-medium mb-1">{stat.label}</p>
                            <p className="font-display font-bold text-2xl text-dark">{stat.value}</p>
                            <p className="text-xs text-neutral-600 mt-1">{stat.sub}</p>
                        </div>
                        <div className={`p-2 rounded-xl bg-current/10 ${stat.color}`}>
                            {stat.icon}
                        </div>
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
                {/* Pipeline Chart */}
                <div className="xl:col-span-2 card">
                    <h2 className="font-display font-bold text-lg text-dark mb-4">Lead Pipeline Distribution</h2>
                    {pipelineData.length > 0 ? (
                        <ResponsiveContainer width="100%" height={200}>
                            <BarChart data={pipelineData} margin={{ top: 0, right: 0, left: -20, bottom: 0 }}>
                                <XAxis dataKey="status" tick={{ fontSize: 10 }} />
                                <YAxis tick={{ fontSize: 10 }} />
                                <Tooltip formatter={(v: unknown) => [(v as number), 'Leads']} />
                                <Bar dataKey="count" radius={[4, 4, 0, 0]}>
                                    {pipelineData.map((entry) => (
                                        <Cell key={entry.rawStatus} fill={PIPELINE_COLORS[entry.rawStatus] || '#6B7280'} />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    ) : (
                        <p className="text-sm text-neutral-600 text-center py-12">No lead data yet.</p>
                    )}
                </div>

                {/* Pending Business Development Executive Approvals */}
                <div className="card">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="font-display font-bold text-lg text-dark">Pending Business Development Executives</h2>
                        <Link to="/admin/agents?status=pending" className="text-xs text-primary hover:underline flex items-center gap-1">
                            View All <ArrowRight className="w-3 h-3" />
                        </Link>
                    </div>
                    {data?.pending_approvals?.length ? (
                        <div className="flex flex-col gap-3">
                            {data.pending_approvals.map((agent) => (
                                <div key={agent.id} className="flex items-center gap-3 p-3 bg-neutral-50 rounded-xl">
                                    <div className="w-9 h-9 rounded-full bg-warning/10 flex items-center justify-center text-warning font-bold text-sm shrink-0">
                                        {agent.name.charAt(0)}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium text-dark text-sm truncate">{agent.name}</p>
                                        <p className="text-neutral-600 text-xs">{agent.mobile}</p>
                                    </div>
                                    <Link to={`/admin/agents/${agent.id}`} className="text-xs text-primary hover:underline shrink-0 flex items-center gap-1">
                                        <Clock className="w-3 h-3" /> Review
                                    </Link>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-neutral-600 text-center py-8">No pending approvals.</p>
                    )}
                </div>
            </div>

            {/* Recent Leads */}
            <div className="card">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="font-display font-bold text-lg text-dark">Recent Leads</h2>
                    <Link to="/admin/leads" className="text-sm text-primary hover:underline flex items-center gap-1">
                        View All <ArrowRight className="w-4 h-4" />
                    </Link>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="text-left text-neutral-600 border-b border-gray-100">
                                <th className="pb-3 font-semibold">Lead ULID</th>
                                <th className="pb-3 font-semibold">Beneficiary</th>
                                <th className="pb-3 font-semibold">District, State</th>
                                <th className="pb-3 font-semibold">Assigned Business Development Executive</th>
                                <th className="pb-3 font-semibold">Status</th>
                                <th className="pb-3 font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data?.recent_leads?.length ? data.recent_leads.map((lead, i) => (
                                <tr key={lead.id} className={`border-b border-gray-50 ${i % 2 === 1 ? 'bg-neutral-50' : ''}`}>
                                    <td className="py-3">
                                        <Link to={`/admin/leads/${lead.ulid}`} className="font-mono text-xs text-primary hover:underline">{lead.ulid}</Link>
                                    </td>
                                    <td className="py-3">
                                        <p className="font-medium text-dark">{lead.beneficiary_name}</p>
                                        <p className="text-neutral-600 text-xs">{lead.beneficiary_mobile}</p>
                                    </td>
                                    <td className="py-3 text-neutral-600 text-xs">{lead.beneficiary_district}, {lead.beneficiary_state}</td>
                                    <td className="py-3">
                                        {lead.assigned_agent
                                            ? <span className="text-xs font-mono text-dark">{lead.assigned_agent.agent_id || lead.assigned_agent.name}</span>
                                            : <span className="text-xs text-neutral-600 italic">Unassigned</span>}
                                    </td>
                                    <td className="py-3"><LeadStatusBadge status={lead.status} /></td>
                                    <td className="py-3 text-neutral-600 text-xs whitespace-nowrap">{formatDate(lead.created_at)}</td>
                                </tr>
                            )) : (
                                <tr><td colSpan={6} className="text-center py-10 text-neutral-600 text-sm">No leads yet.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
