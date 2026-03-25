import React from 'react';
import { Gift, Calendar, TrendingUp, Users, CheckCircle2, ChevronRight, Info } from 'lucide-react';
import { UserOfferProgress } from '../../types';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

interface OfferCardProps {
    offer: UserOfferProgress;
    onRedeem?: (id: number) => void;
    isRedeeming?: boolean;
    showDetails?: boolean;
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
            "relative flex flex-col h-full bg-white rounded-2xl border transition-all duration-300 overflow-hidden",
            offer.is_featured ? "border-amber-200 shadow-xl shadow-amber-50" : "border-slate-100 shadow-sm hover:shadow-md",
            isEnded && "opacity-75 grayscale-[0.5]"
        )}>
            {/* Featured Badge */}
            {offer.is_featured && !isEnded && (
                <div className="absolute top-0 right-0 bg-amber-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl z-10 flex items-center gap-1 shadow-sm">
                    <TrendingUp size={12} />
                    FEATURED
                </div>
            )}

            {/* Ended Badge */}
            {isEnded && (
                <div className="absolute top-0 right-0 bg-slate-800 text-white text-[10px] font-black px-3 py-1 rounded-bl-xl z-10 flex items-center gap-1 uppercase tracking-widest">
                    Offer Ended
                </div>
            )}

            {/* Header / Image Area */}
            <div className="relative h-44 w-full bg-slate-50 overflow-hidden shrink-0">
                {offer.prize_image_url ? (
                    <img
                        src={offer.prize_image_url}
                        alt={offer.prize_label}
                        className="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                    />
                ) : (
                    <div className="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-white text-indigo-400">
                        <Gift size={48} className="mb-2 opacity-50" />
                        <span className="text-xs font-medium uppercase tracking-wider opacity-60">Offer Prize</span>
                    </div>
                )}

                {/* Type Badge */}
                <div className="absolute bottom-3 left-3 flex flex-wrap gap-2">
                    <span className={twMerge(
                        "px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1 uppercase tracking-tight shadow-sm border",
                        isCollective
                            ? "bg-purple-50 text-purple-700 border-purple-100"
                            : "bg-blue-50 text-blue-700 border-blue-100"
                    )}>
                        {isCollective ? <Users size={12} /> : <TrendingUp size={12} />}
                        {offer.offer_type}
                    </span>

                    <span className={twMerge(
                        "bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1 uppercase tracking-tight shadow-sm border italic",
                        !offer.is_claimable ? "text-red-600 border-red-100 bg-red-50/90" : "text-slate-700 border-slate-100"
                    )}>
                        <Calendar size={12} className={!offer.is_claimable ? "text-red-500" : "text-indigo-500"} />
                        {offer.offer_from} - {offer.offer_to}
                    </span>

                    {offer.is_annual && (
                        <span className="bg-amber-50 text-amber-700 border border-amber-100 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm">
                            Annual
                        </span>
                    )}
                </div>
            </div>

            {/* Content Body */}
            <div className="flex flex-col flex-grow p-5">
                <div className="mb-4">
                    <h3 className="text-lg font-bold text-slate-900 leading-tight mb-1 line-clamp-1">{offer.title}</h3>
                    <p className="text-sm text-slate-500 line-clamp-2 min-h-[40px] leading-relaxed">
                        {isEnded
                            ? `This offer ended on ${offer.offer_to}. Points have been processed.`
                            : (offer.description || `Earn ${offer.target_points} points to win: ${offer.prize_label}`)
                        }
                    </p>
                </div>

                {/* Progress Visualizer */}
                <div className="mt-auto space-y-3">
                    <div className="flex justify-between items-end">
                        <div>
                            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">
                                {isCollective ? "TEAM PROGRESS" : "YOUR PROGRESS"}
                            </p>
                            <p className="text-xl font-black text-slate-900 leading-none">
                                {isEnded ? 0 : (isCollective ? offer.current_points : offer.my_unredeemed_points)}
                                <span className="text-sm text-slate-400 font-medium ml-1">/ {offer.target_points}</span>
                            </p>
                        </div>
                        <div className="text-right">
                            <span className={clsx(
                                "text-sm font-black",
                                isTargetHit && !isEnded ? "text-emerald-600" : "text-indigo-600",
                                isEnded && "text-slate-300"
                            )}>
                                {currentProgress}%
                            </span>
                        </div>
                    </div>

                    {/* Progress Bar */}
                    <div className="relative h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div
                            className={twMerge(
                                "absolute left-0 top-0 h-full transition-all duration-700 rounded-full",
                                isEnded ? "bg-slate-200" : (isTargetHit ? "bg-emerald-500" : "bg-indigo-600")
                            )}
                            style={{ width: `${currentProgress}%` }}
                        />
                    </div>

                    {/* Summary Info / Multi-Redemption Logic */}
                    {!isCollective && !isEnded && (
                        <div className="flex items-center justify-between py-2 border-y border-slate-50">
                            <div className="flex flex-col">
                                <span className="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Redeemed</span>
                                <span className="text-sm font-bold text-slate-800">{offer.my_redemption_count} prizes</span>
                            </div>
                            <div className="w-px h-6 bg-slate-100" />
                            <div className="flex flex-col text-right">
                                <span className="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Available now</span>
                                <span className={twMerge(
                                    "text-sm font-extrabold",
                                    offer.pending_redemption_count > 0 ? "text-amber-600" : "text-slate-400"
                                )}>
                                    {offer.pending_redemption_count}
                                </span>
                            </div>
                        </div>
                    )}
                </div>

                {/* Prize Label Footer */}
                {!isEnded && (
                    <div className="flex items-center gap-3 mt-4 px-3 py-2 bg-slate-50 rounded-xl border border-slate-100/50">
                        <div className="shrink-0 w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm text-amber-500">
                            <Gift size={16} />
                        </div>
                        <div>
                            <p className="text-[9px] font-bold text-slate-400 uppercase leading-none mb-1">Win Prize</p>
                            <p className="text-sm font-bold text-slate-900 leading-none truncate">{offer.prize_label}</p>
                        </div>
                    </div>
                )}

                {/* Redeem Button (Only for Individuals) */}
                {!isCollective && !isEnded && (
                    <div className="mt-5">
                        <button
                            onClick={() => onRedeem?.(offer.id)}
                            disabled={!offer.is_claimable || !offer.can_redeem || isRedeeming}
                            className={twMerge(
                                "w-full py-3.5 px-4 rounded-xl font-black text-sm transition-all flex items-center justify-center gap-2",
                                (offer.can_redeem && offer.is_claimable)
                                    ? "bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-100 active:scale-[0.98]"
                                    : "bg-slate-100 text-slate-400 cursor-not-allowed"
                            )}
                        >
                            {offer.pending_redemption_count > 0 ? (
                                <>
                                    REDEEM NOW
                                    <ChevronRight size={18} />
                                </>
                            ) : (
                                <>
                                    <Info size={16} />
                                    {offer.cycle_needed} MORE POINTS
                                </>
                            )}
                        </button>

                        {!offer.is_claimable && (
                            <p className="mt-2 text-[10px] text-center text-red-500 font-bold uppercase tracking-tight">
                                Claim window expired
                            </p>
                        )}
                    </div>
                )}

                {/* Collective Note */}
                {isCollective && isTargetHit && !isEnded && (
                    <div className="mt-5 p-3 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-2">
                        <CheckCircle2 size={16} className="text-emerald-600 mt-0.5 shrink-0" />
                        <p className="text-xs text-emerald-800 font-medium">
                            Target Hit! Admin will distribute prizes soon.
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
};
