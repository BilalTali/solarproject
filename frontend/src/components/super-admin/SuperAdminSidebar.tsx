import { Link, useLocation, useNavigate } from 'react-router-dom';
import {
    LayoutDashboard, Users, Shield, FileText, Monitor, LogOut, ShieldAlert, HelpCircle, MessageSquare, IndianRupee, Settings
} from 'lucide-react';
import { useMutation } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { authApi } from '@/services/auth.api';
import { useAuthStore } from '@/hooks/store/authStore';
import { useSettings } from '@/hooks/useSettings';

const SUPER_ADMIN_NAV = [
    { icon: <LayoutDashboard className="w-5 h-5" />, label: 'Dashboard', to: '/super-admin/dashboard' },
    { icon: <ShieldAlert className="w-5 h-5" />, label: 'Manage Admins', to: '/super-admin/admins' },
    { icon: <IndianRupee className="w-5 h-5" />, label: 'Commission Settlements', to: '/super-admin/commissions' },
    { icon: <hr className="border-white/10 my-2" />, label: '', to: '', divider: true },
    { icon: <Monitor className="w-5 h-5" />, label: 'Monitor BDMs (SA)', to: '/super-admin/monitor/super-agents' },
    { icon: <Users className="w-5 h-5" />, label: 'Monitor BDEs (Agent)', to: '/super-admin/monitor/agents' },
    { icon: <Users className="w-5 h-5" />, label: 'Monitor Enumerators', to: '/super-admin/monitor/enumerators' },
    { icon: <FileText className="w-5 h-5" />, label: 'Monitor Leads', to: '/super-admin/monitor/leads' },
    { icon: <HelpCircle className="w-5 h-5" />, label: 'Help Center', to: '/super-admin/help-center' },
    { icon: <MessageSquare className="w-5 h-5" />, label: 'WhatsApp Chatbot', to: '/super-admin/chatbot' },
    { icon: <Shield className="w-5 h-5" />, label: 'Global Reports', to: '/super-admin/reports' },
    { icon: <Settings className="w-5 h-5" />, label: 'Profile & Settings', to: '/super-admin/profile' },
];

export default function SuperAdminSidebar({ onClose }: { onClose?: () => void }) {
    const location = useLocation();
    const navigate = useNavigate();
    const { user, clearAuth } = useAuthStore();
    const { affiliatedWith, logo, masterLogo } = useSettings();
    
    const logoutMutation = useMutation({
        mutationFn: authApi.logout,
        onSuccess: () => {
            clearAuth();
            navigate('/super-admin/login');
            toast.success('Logged out successfully');
        },
    });

    return (
        <aside className="sidebar sidebar-premium w-64 h-full flex flex-col shadow-2xl bg-neutral-900 border-r border-white/5" aria-label="Super Admin Sidebar">
            {/* Logo */}
            <div className="flex items-center gap-2 p-5 border-b border-white/10 bg-black/20">
                <div className="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center overflow-hidden shrink-0 shadow-lg" aria-hidden="true">
                    {masterLogo || logo ? (
                        <img src={masterLogo || logo || ''} alt={affiliatedWith || 'Master identity'} className="w-full h-full object-contain" />
                    ) : (
                        <div className="w-full h-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center">
                            <Shield className="w-5 h-5 text-white" />
                        </div>
                    )}
                </div>
                <div className="flex flex-col min-w-0">
                    <span className="font-display font-black text-white text-[11px] leading-tight uppercase tracking-wider truncate">
                        {affiliatedWith || 'Master Identity Authority'}
                    </span>
                    <div className="flex items-center gap-1.5 mt-1">
                        <div className="w-1 h-1 rounded-full bg-indigo-400 animate-pulse" />
                        <span className="text-[9px] text-white/40 font-black uppercase tracking-[0.2em]">Console Access</span>
                    </div>
                </div>
            </div>

            {/* Profile */}
            <div className="p-4 border-b border-white/5">
                <div className="flex items-center gap-3 bg-white/5 p-3 rounded-2xl border border-white/5">
                    <div className="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center overflow-hidden border border-primary/20">
                        {user?.profile_photo_url ? (
                            <img src={user.profile_photo_url} alt={user.name} className="w-full h-full object-cover" />
                        ) : (
                            <Shield className="w-5 h-5 text-primary" aria-hidden="true" />
                        )}
                    </div>
                    <div className="min-w-0">
                        <p className="text-white font-bold text-sm truncate">{user?.name}</p>
                        <span className="text-white/40 text-[10px] uppercase font-bold tracking-wider">ROOT ACCESS</span>
                    </div>
                </div>
            </div>

            {/* Nav */}
            <nav className="flex-1 p-3 flex flex-col gap-1 overflow-y-auto custom-scrollbar" aria-label="Main Navigation">
                {SUPER_ADMIN_NAV.map((item, idx) => {
                    if (item.divider) return <div key={`div-${idx}`} className="px-3 py-2">{item.icon}</div>;
                    
                    const isActive = location.pathname === item.to || location.pathname.startsWith(item.to + '/');
                    return (
                        <Link
                            key={item.to}
                            to={item.to}
                            onClick={onClose}
                            className={`flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group ${
                                isActive 
                                ? 'bg-primary text-white shadow-lg shadow-primary/20 font-bold' 
                                : 'text-white/60 hover:text-white hover:bg-white/5'
                            }`}
                        >
                            <span className={`${isActive ? 'text-white' : 'text-primary'}`}>{item.icon}</span>
                            <span className="text-sm">{item.label}</span>
                        </Link>
                    );
                })}
            </nav>

            {/* Logout */}
            <div className="p-3 border-t border-white/5 bg-black/10">
                <button
                    onClick={() => logoutMutation.mutate()}
                    className="flex items-center gap-3 px-4 py-3 rounded-xl w-full text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all font-bold"
                >
                    <LogOut className="w-5 h-5" />
                    <span className="text-sm">Logout</span>
                </button>
            </div>
        </aside>
    );
}
