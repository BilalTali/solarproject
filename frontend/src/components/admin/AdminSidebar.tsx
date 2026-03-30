import { Link, useLocation, useNavigate } from 'react-router-dom';
import {
    Sun, LayoutDashboard, List, Users, DollarSign, BarChart3,
    Settings, LogOut, Shield, Star, Award, FileText, Gift, Inbox, Wallet
} from 'lucide-react';
import { useMutation } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import { authApi } from '@/services/auth.api';
import { useAuthStore } from '@/hooks/store/authStore';
import { DownloadIdCardButton } from '@/components/shared/DownloadIdCardButton';
import { useSettings } from '@/hooks/useSettings';

const ADMIN_NAV = [
    { icon: <LayoutDashboard className="w-5 h-5" />, label: 'Dashboard', to: '/admin/dashboard' },
    { icon: <List className="w-5 h-5" />, label: 'Leads', to: '/admin/leads' },
    { icon: <Gift className="w-5 h-5" />, label: 'Incentive Offers', to: '/admin/offers' },
    { icon: <Award className="w-5 h-5" />, label: 'Prize Redemptions', to: '/admin/redemptions' },
    { icon: <Inbox className="w-5 h-5" />, label: 'Absorbed Points', to: '/admin/absorptions' },
    { icon: <Wallet className="w-5 h-5" />, label: 'Withdrawal Requests', to: '/admin/withdrawals' },
    { icon: <Users className="w-5 h-5" />, label: 'Business Development Executives', to: '/admin/agents' },
    { icon: <Users className="w-5 h-5" />, label: 'Enumerators', to: '/admin/enumerators' },
    { icon: <Star className="w-5 h-5" />, label: 'Business Development Managers', to: '/admin/super-agents' },
    { icon: <Shield className="w-5 h-5" />, label: 'Operators', to: '/admin/operators' },
    { icon: <Award className="w-5 h-5" />, label: 'Reward Winners', to: '/admin/media' },
    { icon: <FileText className="w-5 h-5" />, label: 'Agent Documents', to: '/admin/documents' },
    { icon: <DollarSign className="w-5 h-5" />, label: 'Commissions', to: '/admin/commissions' },
    { icon: <BarChart3 className="w-5 h-5" />, label: 'Reports', to: '/admin/reports' },
    { icon: <Settings className="w-5 h-5" />, label: 'Settings', to: '/admin/settings' },
];

const OPERATOR_NAV = [
    { icon: <List className="w-5 h-5" />, label: 'Leads', to: '/admin/leads' },
];

export default function AdminSidebar({ onClose }: { onClose?: () => void }) {
    const location = useLocation();
    const navigate = useNavigate();
    const { user, clearAuth } = useAuthStore();
    const { companyName } = useSettings();

    const isOperator = user?.role === 'operator';
    const NAV = isOperator ? OPERATOR_NAV : ADMIN_NAV;

    const logoutMutation = useMutation({
        mutationFn: authApi.logout,
        onSettled: () => {
            clearAuth();
            navigate('/admin/login');
            toast.success('Logged out successfully');
        },
    });

    return (
        <aside className="sidebar w-64 h-full flex flex-col shadow-2xl" aria-label="Admin Sidebar">
            {/* Logo */}
            <div className="flex items-center gap-2 p-5 border-b border-white/10">
                <div className="w-9 h-9 rounded-full bg-gradient-to-br from-accent to-accent-dark flex items-center justify-center" aria-hidden="true">
                    <Sun className="w-5 h-5 text-white" />
                </div>
                <span className="font-display font-bold text-white">{companyName}</span>
            </div>

            {/* Admin Profile */}
            <div className="p-4 border-b border-white/10">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center overflow-hidden border-2 border-accent/20">
                        {user?.profile_photo_url ? (
                            <img src={user.profile_photo_url} alt={`Profile photo of ${user.name}`} className="w-full h-full object-cover" />
                        ) : (
                            <Shield className="w-5 h-5 text-accent" aria-hidden="true" />
                        )}
                    </div>
                    <div className="min-w-0">
                        <p className="text-white font-semibold text-sm truncate">{user?.name}</p>
                        <span className="text-accent text-xs font-medium capitalize">{isOperator ? 'Operator' : 'Admin'}</span>
                    </div>
                </div>
            </div>

            {/* Nav */}
            <nav className="flex-1 p-3 flex flex-col gap-1 overflow-y-auto" aria-label="Main Navigation">
                {NAV.map((item) => {
                    const isActive = location.pathname.startsWith(item.to);
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

                {!isOperator && (
                    <div className="px-3 mt-2">
                        <DownloadIdCardButton className="w-full justify-start !shadow-none !bg-white/5 hover:!bg-white/10 !border !border-white/10" />
                    </div>
                )}
            </nav>

            {/* Logout */}
            <div className="p-3 border-t border-white/10">
                <button
                    onClick={() => logoutMutation.mutate()}
                    className="sidebar-item w-full text-danger hover:text-danger hover:bg-danger/10"
                    aria-label="Logout from Admin Portal"
                >
                    <LogOut className="w-5 h-5" aria-hidden="true" />
                    <span className="text-sm">Logout</span>
                </button>
            </div>
        </aside>
    );
}
