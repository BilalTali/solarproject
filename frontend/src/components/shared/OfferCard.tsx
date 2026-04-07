import { Gift, Calendar, TrendingUp, Users, CheckCircle2, ChevronRight, Info, Award, Zap } from 'lucide-react';
import { UserOfferProgress } from '../../types';
import { twMerge } from 'tailwind-merge';

interface OfferCardProps {
    offer: UserOfferProgress;
    onRedeem?: (id: number) => void;
    isRedeeming?: boolean;
}

export const OfferCard: React.FC<OfferCardProps> = ({
    offer,
    onRedeem,
    isRedeeming = false,
}) => {
    const isCollective = offer.offer_type === 'collective';
    const isEnded = !!offer.offer_ended_zeroed_at;
    const isTargetHit = isCollective ? (offer.current_points || 0) >= offer.target_points : offer.can_redeem;

    // Progress calculation
    const currentProgress = isEnded
        ? 0
        : (isCollective
            ? Math.min(100, Math.round(((offer.current_points || 0) / offer.target_points) * 100))
            : (offer.pending_redemption_count > 0 ? 100 : offer.cycle_percentage));

    return (
        <div className={twMerge(
            "group relative flex flex-col h-full rounded-[2.5rem] transition-all duration-500 overflow-hidden border-2",
            offer.is_featured && !isEnded 
                ? "bg-gradient-to-br from-amber-50/50 to-white border-amber-200/50 shadow-2xl shadow-amber-100/50 scale-[1.02] z-10" 
                : "bg-white border-slate-100 shadow-xl shadow-slate-200/40 hover:shadow-2xl hover:shadow-indigo-100/50 hover:-translate-y-1",
            isEnded && "opacity-80 grayscale-[0.3] border-slate-200"
        )}>
            {/* Background Accent Gradients */}
            <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl pointer-events-none" />
            <div className="absolute bottom-0 left-0 w-24 h-24 bg-amber-500/5 rounded-full translate-y-1/2 -translate-x-1/2 blur-2xl pointer-events-none" />

            {/* Top Status Bar */}
            <div className="px-6 pt-5 pb-2 flex items-center justify-between relative z-10">
                <div className="flex gap-2">
                    <span className={twMerge(
                        "px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 shadow-sm border",
                        isCollective
                            ? "bg-purple-100 text-purple-700 border-purple-200"
                            : "bg-indigo-100 text-indigo-700 border-indigo-200"
                    )}>
                        {isCollective ? <Users size={12} strokeWidth={3} /> : <Zap size={12} strokeWidth={3} />}
                        {offer.offer_type}
                    </span>
                    {offer.is_featured && !isEnded && (
                        <span className="bg-amber-500 text-white text-[10px] font-black px-3 py-1 rounded-full flex items-center gap-1 shadow-md shadow-amber-200 animate-pulse">
                            <TrendingUp size={12} strokeWidth={3} />
                            FEATURED
                        </span>
                    )}
                </div>
                {isEnded ? (
                    <span className="text-[10px] font-black text-slate-400 uppercase tracking-tighter">EXPIRED</span>
                ) : (
                    <div className="flex items-center gap-1.5 bg-slate-50 border border-slate-100 px-3 py-1 rounded-full shadow-inner">
                        <Calendar size={12} className="text-indigo-500" />
                        <span className="text-[10px] font-black text-slate-600 tracking-tighter">{offer.offer_to}</span>
                    </div>
                )}
            </div>

            {/* Visual Container */}
            <div className="px-6 py-4 relative z-10 shrink-0">
                <div className="relative aspect-[16/10] w-full rounded-[2rem] overflow-hidden bg-slate-900 shadow-2xl shadow-slate-900/20 group-hover:shadow-indigo-500/20 transition-all duration-500">
                    {offer.prize_image_url ? (
                        <img
                            src={offer.prize_image_url}
                            alt={offer.prize_label}
                            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        />
                    ) : (
                        <div className="w-full h-full flex flex-col items-center justify-center bg-gradient-to-tr from-slate-800 to-indigo-900 text-white/20 p-8">
                            <Gift size={64} strokeWidth={1} />
                            <Award className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 opacity-5" />
                        </div>
                    )}
                    
                    {/* Glassmorphic Overlay for Title */}
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-60" />
                    
                    {/* Prize Label Badge */}
                    <div className="absolute bottom-4 left-4 right-4 backdrop-blur-md bg-white/10 border border-white/20 px-4 py-3 rounded-2xl flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center shadow-lg shadow-amber-400/20 text-slate-900">
                            <Gift size={16} strokeWidth={2.5} />
                        </div>
                        <div className="min-w-0">
                            <p className="text-[9px] font-black text-amber-300 uppercase leading-none mb-1 tracking-widest">REWARD</p>
                            <p className="text-sm font-black text-white leading-none truncate">{offer.prize_label}</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Content Body */}
            <div className="px-8 pb-8 flex flex-col flex-grow relative z-10">
                <div className="mb-6">
                    <h3 className="text-xl font-black text-slate-900 leading-none mb-2 tracking-tight group-hover:text-indigo-600 transition-colors">
                        {offer.title}
                    </h3>
                    <p className="text-sm font-medium text-slate-500 line-clamp-2 min-h-[40px] leading-relaxed opacity-80 italic">
                        {offer.description || `Hit the target of ${offer.target_points} to unlock this exclusive reward.`}
                    </p>
                </div>

                {/* Progress Visualizer */}
                <div className="mt-auto space-y-4">
                    <div className="flex justify-between items-end bg-slate-50/50 p-4 rounded-2xl border border-slate-100 shadow-inner">
                        <div>
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 leading-none">
                                {isCollective ? "NETWORK TOTAL" : "MILESTONE TRACKER"}
                            </p>
                            <div className="flex items-baseline gap-1">
                                <span className="text-2xl font-black text-slate-900 leading-none tabular-nums">
                                    {isEnded ? 0 : (isCollective ? offer.current_points : offer.my_unredeemed_points)}
                                </span>
                                <span className="text-xs font-black text-slate-400 uppercase tracking-tighter">/ {offer.target_points} PTS</span>
                            </div>
                        </div>
                        <div className={twMerge(
                            "px-3 py-1 rounded-lg text-lg font-black tabular-nums shadow-sm border",
                            isTargetHit && !isEnded ? "bg-emerald-500 text-white border-emerald-400" : "bg-indigo-600 text-white border-indigo-500"
                        )}>
                            {currentProgress}%
                        </div>
                    </div>

                    {/* Glowing Progress Bar */}
                    <div className="relative h-4 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                        <div
                            className={twMerge(
                                "absolute left-0 top-0 h-full transition-all duration-1000 ease-out rounded-full shadow-lg",
                                isEnded ? "bg-slate-300" : (isTargetHit ? "bg-emerald-500" : "bg-gradient-to-r from-indigo-600 to-indigo-400")
                            )}
                            style={{ width: `${currentProgress}%` }}
                        >
                            <div className="absolute inset-0 bg-[linear-gradient(90deg,transparent_0%,rgba(255,255,255,0.3)_50%,transparent_100%)] animate-shimmer-fast" style={{ backgroundSize: '200% 100%' }} />
                        </div>
                    </div>

                    {/* Multi-Redemption Badge (Only for Individuals) */}
                    {!isCollective && !isEnded && offer.my_redemption_count > 0 && (
                        <div className="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-widest pt-1 px-1">
                            <CheckCircle2 size={12} strokeWidth={3} />
                            Already won {offer.my_redemption_count} times
                        </div>
                    )}
                </div>

                {/* Redeem Button (Only for Individuals) */}
                {!isCollective && !isEnded && (
                    <div className="mt-8">
                        <button
                            onClick={() => onRedeem?.(offer.id)}
                            disabled={!offer.is_claimable || !offer.can_redeem || isRedeeming}
                            className={twMerge(
                                "w-full py-5 px-6 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95",
                                (offer.can_redeem && offer.is_claimable)
                                    ? "bg-slate-900 text-white ring-4 ring-slate-900/10 hover:bg-slate-800 hover:ring-indigo-600/20 hover:shadow-2xl shadow-slate-900/20"
                                    : "bg-slate-50 text-slate-400 border border-slate-100 cursor-not-allowed opacity-60"
                            )}
                        >
                            {isRedeeming ? (
                                <div className="animate-spin w-4 h-4 border-2 border-indigo-200 border-t-indigo-600 rounded-full" />
                            ) : (
                                <>
                                    {offer.pending_redemption_count > 0 ? (
                                        <>
                                            CLAIM REWARD NOW
                                            <div className="w-6 h-6 rounded-lg bg-indigo-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                                <ChevronRight size={14} strokeWidth={4} />
                                            </div>
                                        </>
                                    ) : (
                                        <>
                                            <Info size={16} strokeWidth={3} />
                                            STILL NEED {offer.cycle_needed} POINTS
                                        </>
                                    )}
                                </>
                            )}
                        </button>

                        {!offer.is_claimable && (
                            <p className="mt-3 text-[10px] text-center text-red-500 font-black uppercase tracking-widest opacity-60">
                                Registration window closed
                            </p>
                        )}
                    </div>
                )}

                {/* Collective Note */}
                {isCollective && isTargetHit && !isEnded && (
                    <div className="mt-8 p-5 rounded-3xl bg-emerald-50 border-2 border-emerald-100 flex items-center gap-4 animate-in zoom-in duration-500">
                        <div className="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-200 shrink-0">
                            <CheckCircle2 size={24} strokeWidth={2.5} />
                        </div>
                        <p className="text-xs text-emerald-900 font-black uppercase tracking-tight leading-tight">
                            Target Hit! <br/> <span className="opacity-60">Redemptions Active</span>
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
};
