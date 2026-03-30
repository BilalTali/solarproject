import { useEffect, useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { superAgentApi } from '@/services/superAgent.api';
import { X, ExternalLink } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function CommissionNotificationPopup() {
    const queryClient = useQueryClient();
    const [activeNotif, setActiveNotif] = useState<any>(null);

    const { data } = useQuery({
        queryKey: ['sa-notifications-polling'],
        queryFn: () => superAgentApi.getNotifications(),
        refetchInterval: 15000,
    });

    const markReadMutation = useMutation({
        mutationFn: (id: number) => superAgentApi.markNotificationRead(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['sa-notifications-polling'] });
        }
    });

    useEffect(() => {
        if (data?.data && Array.isArray(data.data)) {
            // Find the oldest unread 'commission_entered' notification
            const unreadCommissions = data.data.filter((n: any) => !n.read_at && n.type === 'commission_entered');
            if (unreadCommissions.length > 0) {
                // Show the most recent one if we don't already have one showing
                if (!activeNotif || !unreadCommissions.find((n: any) => n.id === activeNotif.id)) {
                    setActiveNotif(unreadCommissions[0]);
                }
            } else {
                setActiveNotif(null);
            }
        }
    }, [data, activeNotif]);

    if (!activeNotif) return null;

    const handleDismiss = () => {
        markReadMutation.mutate(activeNotif.id);
        setActiveNotif(null);
    };

    return (
        <div className="fixed bottom-6 right-6 z-50 animate-in slide-in-from-bottom-5 fade-in duration-300">
            <div className="bg-white border-l-4 border-orange-500 shadow-xl rounded-lg p-5 w-80 max-w-full relative overflow-hidden">
                {/* Decorative background circle */}
                <div className="absolute -right-6 -top-6 w-24 h-24 bg-orange-50 rounded-full opacity-50 pointer-events-none" />

                <button
                    onClick={handleDismiss}
                    className="absolute top-3 right-3 text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1 rounded-md transition-colors"
                >
                    <X size={16} />
                </button>

                <div className="flex items-start gap-3">
                    <div className="bg-orange-100 p-2 rounded-full flex-shrink-0 mt-1">
                        <span className="text-xl leading-none block">💸</span>
                    </div>
                    <div>
                        <h4 className="font-bold text-slate-800 pr-5">{activeNotif.title}</h4>
                        <p className="text-sm text-slate-600 mt-1 leading-relaxed">
                            {activeNotif.message}
                        </p>

                        <div className="mt-4 flex items-center justify-between">
                            <span className="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded">
                                New Earnings
                            </span>
                            <Link
                                to="/super-agent/commissions"
                                onClick={handleDismiss}
                                className="text-xs font-semibold flex items-center gap-1 text-slate-600 hover:text-orange-600 transition-colors"
                            >
                                View Details <ExternalLink size={12} />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
