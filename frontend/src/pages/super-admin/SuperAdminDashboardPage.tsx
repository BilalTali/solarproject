import { useQuery } from '@tanstack/react-query';
import { 
    Users, Shield, Star, FileText, DollarSign, Activity, 
    TrendingUp, ArrowUpRight, MonitorCheck
} from 'lucide-react';
import api from '@/api/axios';
import { ApiResponse } from '@/types';

interface DashboardStats {
    total_admins: number;
    total_super_agents: number;
    total_agents: number;
    total_enumerators: number;
    total_leads: number;
    total_commissions: number;
}

export default function SuperAdminDashboardPage() {
    const { data: statsRes, isLoading } = useQuery({
        queryKey: ['super-admin', 'stats'],
        queryFn: async () => {
            const res = await api.get<ApiResponse<DashboardStats>>('/super-admin/dashboard/stats');
            return res.data;
        }
    });

    const stats = statsRes?.data;

    const cards = [
        { label: 'Total Admins', value: stats?.total_admins, icon: <Shield className="w-6 h-6" />, color: 'bg-blue-500', trend: '+2 this month' },
        { label: 'Total BDMs (SA)', value: stats?.total_super_agents, icon: <Star className="w-6 h-6" />, color: 'bg-indigo-500', trend: '+5 this month' },
        { label: 'Total BDEs (Agent)', value: stats?.total_agents, icon: <Users className="w-6 h-6" />, color: 'bg-primary', trend: '+12 this month' },
        { label: 'Total Enumerators', value: stats?.total_enumerators, icon: <Users className="w-6 h-6" />, color: 'bg-emerald-500', trend: '+24 this month' },
        { label: 'Total Leads', value: stats?.total_leads, icon: <FileText className="w-6 h-6" />, color: 'bg-orange-500', trend: '+150 this week' },
        { label: 'Global Commissions', value: `₹${stats?.total_commissions?.toLocaleString()}`, icon: <DollarSign className="w-6 h-6" />, color: 'bg-rose-500', trend: 'Global Payout' },
    ];

    if (isLoading) {
        return (
            <div className="flex items-center justify-center min-h-[400px]">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
        );
    }

    return (
        <div className="space-y-8 animate-in fade-in duration-500">
            {/* Header */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 className="text-3xl font-black text-slate-900 tracking-tight">System Oversight</h2>
                    <p className="text-slate-500 mt-1 font-medium">Real-time platform-wide activity and role distribution.</p>
                </div>
                <div className="flex items-center gap-3">
                    <button className="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-all shadow-sm">
                        <Activity className="w-4 h-4 text-primary" />
                        System Logs
                    </button>
                    <button className="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary-dark transition-all shadow-lg shadow-primary/20">
                        <TrendingUp className="w-4 h-4" />
                        Generate Report
                    </button>
                </div>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {cards.map((card, i) => (
                    <div key={i} className="group bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div className="flex items-start justify-between">
                            <div className={`p-4 rounded-2xl ${card.color} text-white shadow-lg`}>
                                {card.icon}
                            </div>
                            <div className="flex items-center gap-1 text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                                <span className="text-[10px] font-black uppercase">{card.trend}</span>
                                <ArrowUpRight className="w-3 h-3" />
                            </div>
                        </div>
                        <div className="mt-6">
                            <h3 className="text-slate-500 font-bold text-sm uppercase tracking-wider">{card.label}</h3>
                            <p className="text-4xl font-black text-slate-900 mt-1">{card.value ?? 0}</p>
                        </div>
                        <div className="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                            <span className="text-xs text-slate-400 font-medium">View detailed monitor</span>
                            <MonitorCheck className="w-4 h-4 text-primary" />
                        </div>
                    </div>
                ))}
            </div>

            {/* Bottom Section */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div className="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h3 className="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <Shield className="w-5 h-5 text-primary" />
                        Administrative Health
                    </h3>
                    <div className="space-y-4">
                        <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-2 h-2 rounded-full bg-emerald-500" />
                                <span className="text-sm font-bold text-slate-700">All Admins Online</span>
                            </div>
                            <span className="text-xs font-medium text-slate-400">100% Stability</span>
                        </div>
                        <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-2 h-2 rounded-full bg-blue-500" />
                                <span className="text-sm font-bold text-slate-700">Backup System Status</span>
                            </div>
                            <span className="text-xs font-medium text-slate-400">Verified 2h ago</span>
                        </div>
                    </div>
                </div>

                <div className="bg-gradient-to-br from-primary to-primary-dark p-8 rounded-3xl shadow-xl shadow-primary/20 text-white relative overflow-hidden">
                    <div className="absolute top-0 right-0 p-8 opacity-10">
                        <Shield className="w-32 h-32" />
                    </div>
                    <div className="relative z-10">
                        <h3 className="text-2xl font-black mb-2">Role Management</h3>
                        <p className="text-white/80 font-medium mb-6 max-w-xs">You have full permission to CRUD administrators and monitor all platform activity.</p>
                        <button className="px-6 py-3 bg-white text-primary rounded-xl font-bold hover:bg-slate-50 transition-all shadow-lg active:scale-95">
                            Manage Admin Access
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
