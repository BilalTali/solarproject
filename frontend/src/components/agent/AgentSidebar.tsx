import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Sun, LayoutDashboard, List, PlusCircle, DollarSign, Bell, User, Users, LogOut, BadgeCheck, FileText, Gift, Wallet } from 'lucide-react';
import { useMutation } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { authApi } from '@/services/auth.api';
import { useAuthStore } from '@/hooks/store/authStore';
import { DownloadIdCardButton } from '@/components/shared/DownloadIdCardButton';
import { useSettings } from '@/hooks/useSettings';

const NAV = [
    { icon: <LayoutDashboard className="w-5 h-5" />, label: 'Dashboard', to: '/agent/dashboard' },
    { icon: <Users className="w-5 h-5" />, label: 'Enumerators', to: '/agent/enumerators' },
    { icon: <List className="w-5 h-5" />, label: 'My Leads', to: '/agent/leads' },
    { icon: <Gift className="w-5 h-5" />, label: 'Milestone Prizes', to: '/agent/offers' },
    { icon: <Wallet className="w-5 h-5" />, label: 'Withdraw Cash', to: '/agent/withdrawals' },
    { icon: <PlusCircle className="w-5 h-5" />, label: 'Submit Lead', to: '/agent/leads/new' },
    { icon: <FileText className="w-5 h-5" />, label: 'Resource Library', to: '/agent/documents' },
    { icon: <DollarSign className="w-5 h-5" />, label: 'My Earnings', to: '/agent/commissions' },
    { icon: <Bell className="w-5 h-5" />, label: 'Notifications', to: '/agent/notifications' },
    { icon: <User className="w-5 h-5" />, label: 'Profile', to: '/agent/profile' },
];

export default function AgentSidebar({ onClose }: { onClose?: () => void }) {
    const location = useLocation();
    const navigate = useNavigate();
    const { user, clearAuth } = useAuthStore();
    const { companyName } = useSettings();

    const logoutMutation = useMutation({
        mutationFn: authApi.logout,
        onSettled: () => {
            clearAuth();
            navigate('/agent/login');
            toast.success('Logged out successfully');
        },
    });

    return (
        <aside className="sidebar w-64 h-full flex flex-col shadow-2xl" aria-label="Agent Sidebar">
            {/* Logo */}
            <div className="flex items-center gap-2 p-5 border-b border-white/10">
                <div className="w-9 h-9 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center" aria-hidden="true">
                    <Sun className="w-5 h-5 text-white" />
                </div>
                <span className="font-display font-bold text-white">{companyName}</span>
            </div>

            {/* Business Development Executive Profile */}
            <div className="p-4 border-b border-white/10">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-white font-bold text-sm overflow-hidden border-2 border-white/20">
                        {user?.profile_photo_url ? (
                            <img src={user.profile_photo_url} alt={`Profile photo of ${user.name}`} className="w-full h-full object-cover" />
                        ) : (
                            <span aria-label={user?.name?.charAt(0).toUpperCase()}>{user?.name?.charAt(0).toUpperCase()}</span>
                        )}
                    </div>
                    <div className="min-w-0">
                        <p className="text-white font-semibold text-sm truncate">{user?.name}</p>
                        {user?.agent_id && (
                            <div className="flex items-center gap-1">
                                <BadgeCheck className="w-3 h-3 text-accent" aria-hidden="true" />
                                <span className="text-accent text-xs font-mono" aria-label={`Executive ID: ${user.agent_id}`}>{user.agent_id}</span>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Nav */}
            <nav className="flex-1 p-3 flex flex-col gap-1 overflow-y-auto" aria-label="Main Navigation">
                {NAV.map((item) => {
                    const isActive = location.pathname === item.to;
                    return (
                        <Link
                            key={item.to}
                            to={item.to}
                            onClick={onClose}
                            className={`sidebar-item group ${isActive ? 'sidebar-item-active' : ''}`}
                            aria-current={isActive ? 'page' : undefined}
                        >
                            <span aria-hidden="true">{item.icon}</span>
                            <span className="text-sm">{item.label}</span>
                        </Link>
                    );
                })}

                <div className="px-3 mt-2">
                    <DownloadIdCardButton className="w-full justify-start !shadow-none !bg-white/5 hover:!bg-white/10 !border !border-white/10" />
                </div>
            </nav>

            {/* Logout */}
            <div className="p-3 border-t border-white/10">
                <button
                    onClick={() => logoutMutation.mutate()}
                    className="sidebar-item w-full text-danger hover:text-danger hover:bg-danger/10"
                    aria-label="Logout from Agent Portal"
                >
                    <LogOut className="w-5 h-5" aria-hidden="true" />
                    <span className="text-sm">Logout</span>
                </button>
            </div>
        </aside>
    );
}
