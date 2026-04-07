import { useNavigate } from 'react-router-dom';
import { Zap, Trophy, Target, Gift, Award, TrendingUp, ChevronRight, CheckCircle2 } from 'lucide-react';
import { Offer, UserOfferProgress, getExcitementState, OfferProgress } from '@/types';
import { twMerge } from 'tailwind-merge';

const PARTICLE_COLORS = ['#FFD700', '#FF9500', '#FF6030', '#FFE566', '#FFA500', '#FF4500', '#FFD700'];

const STATE_CONFIG = {
    dormant: {
        cardClass: 'bg-white border-slate-100 shadow-xl shadow-slate-200/40 hover:border-indigo-100',
        washClass: 'from-indigo-50/50 to-transparent',
        iconBg: 'bg-indigo-50 text-indigo-500',
        icon: <Target size={18} strokeWidth={2.5} />,
        trackBg: 'bg-slate-100 shadow-inner',
        fillClass: 'bg-gradient-to-r from-indigo-600 to-indigo-400',
        shimmerFill: true,
        tickFill: 'bg-indigo-200',
        countClass: 'text-slate-500',
        countColor: 'text-indigo-600',
        tagline: null,
        tagClass: '',
        statusColor: 'text-slate-400',
        ctaClass: 'bg-slate-900 text-white shadow-lg shadow-slate-200 hover:bg-slate-800',
        ctaLabel: 'View Offer',
        daysUrgency: 'normal' as const,
        cardGlow: '',
        confetti: false,
        prizeGlow: false,
    },
    building: {
        cardClass: 'bg-white border-amber-100 shadow-2xl shadow-amber-100/30 hover:border-amber-200',
        washClass: 'from-amber-50/50 to-transparent',
        iconBg: 'bg-amber-50 text-amber-500 animate-pulse',
        icon: <TrendingUp size={18} strokeWidth={2.5} />,
        trackBg: 'bg-slate-100 shadow-inner',
        fillClass: 'bg-[length:200%] bg-gradient-to-r from-amber-500 to-orange-400 animate-shimmer-fast',
        shimmerFill: true,
        tickFill: 'bg-amber-300',
        countClass: 'text-amber-700',
        countColor: 'text-amber-600',
        tagline: "Building momentum! 🔥",
        tagClass: 'text-amber-600',
        statusColor: 'text-amber-600',
        ctaClass: 'bg-amber-500 text-white shadow-xl shadow-amber-200 hover:bg-amber-600',
        ctaLabel: 'Keep Going',
        daysUrgency: 'normal' as const,
        cardGlow: '',
        confetti: false,
        prizeGlow: true,
    },
    close: {
        cardClass: 'bg-gradient-to-br from-indigo-50/30 to-white border-indigo-200 shadow-2xl shadow-indigo-200/40 animate-pulse-slow',
        washClass: 'from-indigo-100/40 to-transparent',
        iconBg: 'bg-indigo-600 text-white shadow-lg shadow-indigo-200',
        icon: <Zap size={18} strokeWidth={2.5} />,
        trackBg: 'bg-indigo-50 shadow-inner',
        fillClass: 'bg-[length:200%] bg-gradient-to-r from-indigo-600 via-purple-500 to-indigo-600 animate-shimmer-fast',
        shimmerFill: true,
        tickFill: 'bg-indigo-300',
        countClass: 'text-indigo-700 font-black',
        countColor: 'text-indigo-600',
        tagline: "Almost there! ⚡",
        tagClass: 'text-indigo-600/80',
        statusColor: 'text-indigo-700',
        ctaClass: 'bg-slate-900 text-white shadow-2xl shadow-indigo-200 hover:scale-[1.02]',
        ctaLabel: 'Final Push Now',
        daysUrgency: 'urgent' as const,
        cardGlow: 'shadow-indigo-500/20',
        confetti: false,
        prizeGlow: true,
    },
    ready: {
        cardClass: 'bg-gradient-to-br from-emerald-50 to-white border-emerald-300 shadow-2xl shadow-emerald-200/50 animate-bounce-slow',
        washClass: 'from-emerald-100/40 to-transparent',
        iconBg: 'bg-emerald-500 text-white shadow-xl shadow-emerald-200',
        icon: <Trophy size={18} strokeWidth={2.5} />,
        trackBg: 'bg-emerald-100 shadow-inner border border-emerald-200',
        fillClass: 'bg-[length:200%] bg-gradient-to-r from-emerald-600 via-green-400 to-emerald-600 animate-shimmer-fast',
        shimmerFill: true,
        tickFill: 'bg-emerald-700',
        countClass: 'text-emerald-800 font-black',
        countColor: 'text-emerald-700',
        tagline: "YOU'VE WON! 🏆",
        tagClass: 'text-emerald-700',
        statusColor: 'text-emerald-700',
        ctaClass: 'bg-emerald-600 text-white font-black shadow-2xl shadow-emerald-200 hover:bg-emerald-700 scale-[1.05] animate-pulse',
        ctaLabel: 'CLAIM YOUR PRIZE',
        daysUrgency: 'critical' as const,
        cardGlow: 'shadow-emerald-500/30',
        confetti: true,
        prizeGlow: true,
    },
} as const;

interface OfferExcitementCardProps {
    offer: Offer;
    progress: OfferProgress | UserOfferProgress | null | undefined;
    role: 'agent' | 'super_agent';
    onRedeem?: (offerId: number) => void;
    compact?: boolean;
}

export const OfferExcitementCard = ({
    offer, progress, role, onRedeem, compact = false,
}: OfferExcitementCardProps) => {
    const navigate = useNavigate();
    const target = offer.target_points;
    const state = getExcitementState(progress, target);
    const cfg = STATE_CONFIG[state];

    // Handle cross-over from UserOfferProgress to the card logic
    const unredeemed = progress
        ? (progress as UserOfferProgress).my_unredeemed_points ?? (progress as OfferProgress).unredeemed_points
        : 0;

    // Cycle progress — reset per redemption:
    const cyclePoints = progress
        ? (progress.can_redeem ? target : unredeemed % target)
        : 0;
    const pct = target > 0 ? Math.min(100, Math.round((cyclePoints / target) * 100)) : 0;
    const needed = Math.max(0, target - cyclePoints);
    const redeemCount = (progress as UserOfferProgress)?.my_redemption_count ?? (progress as OfferProgress)?.redemption_count ?? 0;

    // Tick marks (cap at 12 for visual clarity):
    const showTicks = target <= 12;
    const ticks = showTicks
        ? Array.from({ length: target }, (_, i) => i < cyclePoints)
        : [];

    const handleCTA = (e: React.MouseEvent) => {
        e.stopPropagation();
        if (state === 'ready' && onRedeem) {
            onRedeem(offer.id);
        } else {
            navigate(role === 'agent' ? '/agent/offers' : '/super-agent/offers');
        }
    };

    return (
        <div
            className={twMerge(
                "relative overflow-hidden rounded-[2.5rem] border-2 cursor-pointer transition-all duration-300 group",
                compact ? 'p-4' : 'p-6',
                cfg.cardClass,
                cfg.cardGlow
            )}
            onClick={handleCTA}
        >
            {/* Background elements */}
            <div className={twMerge("absolute top-0 inset-x-0 h-24 bg-gradient-to-b opacity-40 pointer-events-none", cfg.washClass)} />
            
            {/* Shimmer sweep */}
            {state !== 'dormant' && (
                <div className="absolute inset-0 pointer-events-none overflow-hidden rounded-[2.5rem]">
                    <div className="absolute inset-y-0 -left-[100%] w-[50%] pointer-events-none bg-gradient-to-r from-transparent via-white/10 to-transparent animate-[shimmer-sweep_3s_infinite]" />
                </div>
            )}

            {/* Confetti */}
            {cfg.confetti && PARTICLE_COLORS.map((color, i) => (
                <div
                    key={i}
                    className="absolute w-1 h-1 rounded-full pointer-events-none opacity-0 animate-[confetti_3s_infinite]"
                    style={{
                        left: `${10 + i * 12}%`,
                        top: '15%',
                        backgroundColor: color,
                        animationDelay: `${i * 0.2}s`,
                    }}
                />
            ))}

            <div className="relative z-10 flex flex-col h-full">
                {/* Header */}
                <div className="flex items-start justify-between gap-3 mb-4">
                    <div className="flex items-center gap-3 min-w-0">
                        <div className={twMerge(
                            "w-10 h-10 rounded-2xl flex-shrink-0 flex items-center justify-center shadow-md transition-transform duration-300 group-hover:scale-110",
                            cfg.iconBg
                        )}>
                            {cfg.icon}
                        </div>
                        <div className="min-w-0">
                            <h4 className={twMerge(
                                "text-sm font-black tracking-tight truncate leading-none mb-1",
                                state === 'ready' ? 'text-emerald-900' : 'text-slate-900'
                            )}>
                                {offer.title}
                            </h4>
                            {cfg.tagline && (
                                <p className={twMerge("text-[10px] font-bold leading-none tracking-tight", cfg.tagClass)}>
                                    {cfg.tagline}
                                </p>
                            )}
                        </div>
                    </div>

                    {offer.days_remaining <= 30 && (
                        <div className={twMerge(
                            "flex-shrink-0 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm",
                            cfg.daysUrgency === 'critical' ? 'bg-red-600 text-white animate-pulse' :
                            cfg.daysUrgency === 'urgent' ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-500'
                        )}>
                            {offer.days_remaining <= 0 ? 'GRACE' : `${offer.days_remaining}D LEFT`}
                        </div>
                    )}
                </div>

                {/* Prize Card */}
                <div className={twMerge(
                    "flex items-center gap-3 rounded-2xl p-3 mb-5 border-2 transition-all duration-300",
                    state === 'ready' ? 'bg-emerald-500/10 border-emerald-200 shadow-lg shadow-emerald-500/5' : 
                    state === 'close' ? 'bg-indigo-50 border-indigo-100 shadow-lg shadow-indigo-500/5' : 
                    'bg-slate-50 border-slate-100'
                )}>
                    <div className={twMerge(
                        "w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center p-0.5 bg-white border-2 overflow-hidden shadow-md",
                        cfg.prizeGlow ? "border-amber-400 animate-pulse" : "border-slate-100"
                    )}>
                        {offer.prize_image_url ? (
                            <img src={offer.prize_image_url} alt="" className="w-full h-full object-cover rounded-lg" />
                        ) : (
                            <Gift className="text-slate-300" size={24} />
                        )}
                    </div>
                    <div className="min-w-0">
                        <p className="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1.5 italic">PRIZE</p>
                        <p className={twMerge(
                            "text-[13px] font-black leading-none truncate",
                            state === 'ready' ? 'text-emerald-950' : 'text-slate-900'
                        )}>
                            {offer.prize_label}
                        </p>
                    </div>
                </div>

                {/* Progress Tracking */}
                <div className="space-y-3 mt-auto">
                    <div className="flex justify-between items-end">
                        <p className="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Progress Tracker</p>
                        <p className={twMerge("text-xs font-black tabular-nums tracking-tight", cfg.countClass)}>
                            {state === 'ready' ? `READY ✓` : `${cyclePoints} / ${target} PTS`}
                        </p>
                    </div>

                    {/* Premium Progress Bar */}
                    <div className={twMerge("h-3.5 w-full rounded-full overflow-hidden relative border", cfg.trackBg)}>
                        <div 
                            className={twMerge("h-full rounded-full relative transition-all duration-1000 ease-out shadow-md", cfg.fillClass)} 
                            style={{ width: `${pct}%` }}
                        >
                            {cfg.shimmerFill && (
                                <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer-fast" style={{ backgroundSize: '200% 100%' }} />
                            )}
                        </div>
                    </div>

                    {/* Ticks */}
                    {showTicks && (
                        <div className="flex justify-between px-1">
                            {ticks.map((filled, i) => (
                                <div key={i} className={twMerge("w-0.5 h-1 rounded-full", filled ? cfg.tickFill : "bg-slate-200")} />
                            ))}
                        </div>
                    )}

                    {/* Status Text & Redeemed Count */}
                    <div className="flex items-center justify-between min-h-[16px]">
                        <p className={twMerge("text-[10px] font-bold leading-none italic", cfg.statusColor)}>
                            {state === 'ready' ? (
                                <span className="flex items-center gap-1.5 text-emerald-600 font-extrabold uppercase tracking-tighter">
                                    <CheckCircle2 size={10} strokeWidth={3} /> Redeemable Now!
                                </span>
                            ) : needed > 0 ? (
                                <span>Need <strong className={cfg.countColor}>{needed}</strong> more to win</span>
                            ) : null}
                        </p>
                        {redeemCount > 0 && (
                            <div className="flex items-center gap-1 bg-amber-50 text-amber-700 text-[8px] font-black px-1.5 py-0.5 rounded-md border border-amber-100 tracking-tighter">
                                <Award size={8} strokeWidth={3} /> WON {redeemCount}×
                            </div>
                        )}
                    </div>

                    {/* Action Button */}
                    <button
                        onClick={handleCTA}
                        className={twMerge(
                            "w-full py-4 px-4 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-2 mt-2 active:scale-95 group",
                            cfg.ctaClass
                        )}
                    >
                        {cfg.ctaLabel}
                        <ChevronRight size={14} strokeWidth={4} className="transition-transform group-hover:translate-x-0.5" />
                    </button>
                </div>
            </div>
        </div>
    );
};

