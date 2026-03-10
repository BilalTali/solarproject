import { useNavigate } from 'react-router-dom'
import { Offer, UserOfferProgress, getExcitementState, OfferProgress } from '@/types'

const PARTICLE_COLORS = ['#FFD700', '#FF9500', '#FF6030', '#FFE566', '#FFA500', '#FF4500', '#FFD700']

const STATE_CONFIG = {
    dormant: {
        cardClass: 'bg-white border-primary/10 hover:border-primary/20',
        washClass: 'from-primary/5 to-transparent',
        iconBg: 'bg-primary/10',
        iconEmoji: '🎯',
        trackBg: 'bg-neutral-100',
        fillClass: 'bg-gradient-to-r from-primary to-primary-light',
        shimmerFill: false,
        tickFill: 'bg-primary/40',
        countClass: 'text-neutral-600',
        countColor: 'text-primary',
        tagline: null,
        tagClass: '',
        statusColor: 'text-neutral-500',
        ctaClass: 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200',
        ctaLabel: 'View Offer →',
        daysUrgency: 'normal' as const,
        cardGlow: '',
        confetti: false,
        prizeGlow: false,
    },
    building: {
        cardClass: 'bg-white border-accent/20 hover:border-accent/40',
        washClass: 'from-accent/5 to-transparent',
        iconBg: 'bg-accent/15 animate-[pulse_3s_ease-in-out_infinite]',
        iconEmoji: '🔥',
        trackBg: 'bg-neutral-100',
        fillClass: 'bg-gradient-to-r from-accent to-accent-dark animate-[pulse_3s_ease-in-out_infinite]',
        shimmerFill: false,
        tickFill: 'bg-accent/50',
        countClass: 'text-accent-dark',
        countColor: 'text-accent-dark',
        tagline: "You're building real momentum! 🔥",
        tagClass: 'text-accent-dark/70',
        statusColor: 'text-accent-dark',
        ctaClass: 'bg-accent/10 text-accent-dark border border-accent/20',
        ctaLabel: 'Keep Going →',
        daysUrgency: 'normal' as const,
        cardGlow: '',
        confetti: false,
        prizeGlow: false,
    },
    close: {
        cardClass: 'bg-white border-warning/30 hover:border-warning/50 animate-[closeGlow_3s_ease-in-out_infinite]',
        washClass: 'from-warning/10 to-transparent',
        iconBg: 'bg-warning/20 animate-[pulse_2s_ease-in-out_infinite]',
        iconEmoji: '⚡',
        trackBg: 'bg-neutral-100',
        fillClass: 'bg-gradient-to-r from-warning to-yellow-300 animate-[pulse_2s_ease-in-out_infinite]',
        shimmerFill: true,
        tickFill: 'bg-warning/60',
        countClass: 'text-warning',
        countColor: 'text-warning',
        tagline: "Almost there — don't stop! ⚡",
        tagClass: 'text-warning/80',
        statusColor: 'text-warning',
        ctaClass: 'bg-warning/10 text-warning border border-warning/30',
        ctaLabel: 'So Close! Push Now →',
        daysUrgency: 'urgent' as const,
        cardGlow: '',
        confetti: false,
        prizeGlow: false,
    },
    ready: {
        cardClass: 'bg-gradient-to-br from-yellow-50 to-white border-accent font-bold animate-[readyGlow_2s_ease-in-out_infinite]',
        washClass: 'from-accent/20 to-transparent',
        iconBg: 'bg-accent/30 animate-[pulse_1.4s_ease-in-out_infinite]',
        iconEmoji: '🏆',
        trackBg: 'bg-neutral-200',
        fillClass: 'bg-[length:200%] bg-gradient-to-r from-accent via-yellow-400 to-accent animate-shimmer-fast',
        shimmerFill: true,
        tickFill: 'bg-accent',
        countClass: 'text-accent-dark',
        countColor: 'text-accent-dark',
        tagline: "🎉 YOU'VE EARNED YOUR PRIZE!",
        tagClass: 'text-accent-dark',
        statusColor: 'text-accent-dark',
        ctaClass: 'bg-gradient-to-r from-accent to-accent-dark text-white font-black animate-[pulse_1.6s_ease-in-out_infinite] shadow-lg shadow-accent/30',
        ctaLabel: '🏆 CLAIM YOUR PRIZE',
        daysUrgency: 'critical' as const,
        cardGlow: '',
        confetti: true,
        prizeGlow: true,
    },
} as const

interface OfferExcitementCardProps {
    offer: Offer
    progress: OfferProgress | UserOfferProgress | null | undefined
    role: 'agent' | 'super_agent'
    onRedeem?: (offerId: number) => void
    compact?: boolean
}

export const OfferExcitementCard = ({
    offer, progress, role, onRedeem, compact = false,
}: OfferExcitementCardProps) => {
    const navigate = useNavigate()
    const target = offer.target_installations
    const state = getExcitementState(progress, target)
    const cfg = STATE_CONFIG[state]

    // Handle cross-over from UserOfferProgress to the card logic
    const unredeemed = progress
        ? (progress as UserOfferProgress).my_unredeemed_installations ?? (progress as OfferProgress).unredeemed_installations
        : 0

    // Cycle progress — reset per redemption:
    const cycleInstalls = progress
        ? (progress.can_redeem ? target : unredeemed % target)
        : 0
    const pct = target > 0 ? Math.min(100, Math.round((cycleInstalls / target) * 100)) : 0
    const needed = Math.max(0, target - cycleInstalls)
    const redeemCount = (progress as UserOfferProgress)?.my_redemption_count ?? (progress as OfferProgress)?.redemption_count ?? 0

    // Tick marks (cap at 12 for visual clarity):
    const showTicks = target <= 12
    const ticks = showTicks
        ? Array.from({ length: target }, (_, i) => i < cycleInstalls)
        : []

    const handleCTA = (e: React.MouseEvent) => {
        e.stopPropagation()
        if (state === 'ready' && onRedeem) {
            onRedeem(offer.id)
        } else {
            navigate(role === 'agent' ? '/agent/offers' : '/super-agent/offers')
        }
    }

    return (
        <div
            className={`
        relative overflow-hidden rounded-[22px] border cursor-pointer
        transition-transform duration-200 hover:-translate-y-[3px]
        ${compact ? 'p-4' : 'p-5'}
        ${cfg.cardClass}
      `}
            onClick={handleCTA}
        >
            {/* Header wash */}
            <div className={`absolute top-0 left-0 right-0 h-[90px] pointer-events-none
                       rounded-t-[22px] bg-gradient-to-b ${cfg.washClass}`} />

            {/* Shimmer overlay (all except dormant) */}
            {state !== 'dormant' && (
                <div className="absolute inset-0 pointer-events-none overflow-hidden rounded-[22px]">
                    <div className="absolute inset-y-0 -left-[70%] w-[50%] pointer-events-none
                          bg-gradient-to-r from-transparent via-white/[0.05] to-transparent
                          animate-shimmer-sweep" />
                </div>
            )}

            {/* Confetti (READY only) */}
            {cfg.confetti && PARTICLE_COLORS.map((color, i) => (
                <div
                    key={i}
                    className="absolute w-[5px] h-[5px] rounded-full pointer-events-none
                     opacity-0 animate-confetti"
                    style={{
                        left: `${8 + i * 13}%`,
                        top: '22%',
                        backgroundColor: color,
                        animationDelay: `${i * 0.35}s`,
                    }}
                />
            ))}

            {/* ─── Content ─────────────────────────────── */}
            <div className="relative z-10">

                {/* Top row */}
                <div className="flex items-start justify-between gap-2.5 mb-2">
                    <div className="flex items-center gap-2.5 flex-1 min-w-0">
                        <div className={`w-[38px] h-[38px] rounded-xl flex-shrink-0
                             flex items-center justify-center
                             shadow-sm
                             ${state === 'ready' ? 'text-lg' : 'text-base'} ${cfg.iconBg}`}>
                            {cfg.iconEmoji}
                        </div>
                        <div className="flex-1 min-w-0">
                            <div className={`
                text-[13.5px] font-extrabold leading-tight truncate
                ${state === 'ready' ? 'text-accent-dark' : 'text-dark'}
              `}>
                                {offer.title}
                            </div>
                            {cfg.tagline && (
                                <div className={`text-[10.5px] font-bold mt-0.5 ${cfg.tagClass}`}>
                                    {cfg.tagline}
                                </div>
                            )}
                        </div>
                    </div>
                    {offer.days_remaining <= 30 && (
                        <div className={`
              flex-shrink-0 flex items-center gap-1 px-3 py-1.5
              rounded-xl text-[11px] font-black uppercase tracking-wider
              shadow-sm
              ${cfg.daysUrgency === 'critical'
                                ? 'bg-red-600 text-white shadow-red-500/30 animate-pulse'
                                : cfg.daysUrgency === 'urgent'
                                    ? 'bg-orange-100 text-orange-700 border border-orange-200'
                                    : 'bg-blue-50 text-blue-700 border border-blue-100'}
            `}>
                            <span className="opacity-70">Ends in</span>
                            <span className="font-mono">{offer.days_remaining <= 0 ? 'Grace' : `${offer.days_remaining}d`}</span>
                        </div>
                    )}
                </div>

                {/* Prize row */}
                <div className={`
          flex items-center gap-3 rounded-[14px] p-2.5 my-3
          border
          ${state === 'ready'
                        ? 'bg-accent/5 border-accent/20'
                        : state === 'close'
                            ? 'bg-warning/5 border-warning/10'
                            : 'bg-neutral-50 border-neutral-100'}
        `}>
                    <div className={`
            w-[46px] h-[46px] rounded-xl flex-shrink-0
            flex items-center justify-center text-[24px]
            bg-white shadow-sm border
            ${cfg.prizeGlow
                            ? 'border-accent/30 animate-[prizeGlow_2s_ease-in-out_infinite]'
                            : 'border-neutral-100'}
          `}>
                        {offer.prize_image_url
                            ? <img src={offer.prize_image_url} alt=""
                                className="w-full h-full object-cover rounded-xl" />
                            : '🎁'}
                    </div>
                    <div>
                        <div className="text-[9px] font-extrabold tracking-[2.5px] uppercase text-neutral-400 mb-0.5">
                            {state === 'ready' ? 'Your Prize' : 'Prize'}
                        </div>
                        <div className={`
              text-[13px] font-bold leading-tight
              ${state === 'ready' ? 'text-accent-dark' : 'text-dark'}
            `}>
                            {offer.prize_label}
                        </div>
                        {offer.prize_amount && (
                            <div className={`text-[10.5px] mt-0.5
                ${state === 'ready' ? 'text-accent/60' : 'text-neutral-500'}`}>
                                Worth ₹{Number(offer.prize_amount).toLocaleString('en-IN')}
                            </div>
                        )}
                    </div>
                </div>

                {/* Progress */}
                <div className="flex justify-between items-center mb-1.5">
                    <span className="text-[10px] text-neutral-400 font-semibold italic">Progress this cycle</span>
                    <span className={`text-[11.5px] font-extrabold tabular-nums ${cfg.countClass}`}>
                        {state === 'ready' ? `${target} / ${target} ✓` : `${cycleInstalls} / ${target}`}
                    </span>
                </div>

                {/* Progress bar */}
                <div className={`h-2 rounded-full overflow-hidden relative ${cfg.trackBg}`}>
                    <div className={`h-full rounded-full relative overflow-hidden ${cfg.fillClass}`}
                        style={{ width: `${pct}%` }}>
                        {cfg.shimmerFill && (
                            <div className="absolute inset-0
                              bg-gradient-to-r from-transparent via-white/[0.28] to-transparent
                              animate-shimmer-fast" />
                        )}
                    </div>
                </div>

                {/* Tick marks */}
                {showTicks && (
                    <div className="flex justify-between px-px mt-1.5">
                        {ticks.map((filled, i) => (
                            <div key={i}
                                className={`w-0.5 h-1 rounded-sm
                     ${filled ? cfg.tickFill : 'bg-white/[0.11]'}`} />
                        ))}
                    </div>
                )}

                {/* Status row */}
                <div className="flex items-center justify-between mt-2.5 mb-3.5">
                    <div className={`text-[11.5px] font-semibold ${cfg.statusColor}`}>
                        {state === 'ready' ? (
                            <span className="flex items-center gap-1.5">
                                <span className="w-[5px] h-[5px] rounded-full bg-accent animate-pulse inline-block" />
                                Ready to redeem now!
                            </span>
                        ) : needed > 0 ? (
                            <span className="text-neutral-500">
                                <strong className={cfg.countColor}>{needed} more install{needed !== 1 ? 's' : ''}</strong>
                                {' '}to go
                            </span>
                        ) : null}
                    </div>
                    {redeemCount > 0 && (
                        <span className="text-[10px] px-2 py-0.5 rounded-xl bg-neutral-100 text-neutral-400 border border-neutral-200">
                            🏅 Won {redeemCount}×
                        </span>
                    )}
                </div>

                {/* CTA */}
                <button
                    onClick={handleCTA}
                    className={`
            w-full py-3 px-4 rounded-[13px] text-[12px] font-extrabold
            flex items-center justify-center gap-1.5 transition-all
            active:scale-[0.98] ${cfg.ctaClass}
          `}
                >
                    {cfg.ctaLabel}
                </button>

            </div>
        </div>
    )
}
