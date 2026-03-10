import { useQuery } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { ChevronRight } from 'lucide-react'
import { OfferExcitementCard } from './OfferExcitementCard'
import { OfferNotificationBanner } from './OfferNotificationBanner'
import { OfferWithProgress, UserOfferProgress, computeUrgencyScore, OfferProgress } from '@/types'
import api from '@/api/axios'

interface Props {
    role: 'agent' | 'super_agent'
    onRedeem?: (offerId: number) => void
}

export const OffersDashboardSection = ({ role, onRedeem }: Props) => {
    const navigate = useNavigate()
    const endpoint = role === 'agent' ? '/agent/offers' : '/super-agent/offers'
    const qKey = role === 'agent' ? ['agent-offers'] : ['sa-offers']

    const { data: offers = [], isLoading } = useQuery<OfferWithProgress[]>({
        queryKey: qKey,
        queryFn: async () => {
            const res = await api.get(endpoint);
            return res.data.data ?? [];
        },
        refetchInterval: 60_000,
        staleTime: 30_000,
    })

    // Live offers only:
    const live = offers.filter(o => o.is_live || o.is_claimable)

    // Progress map:
    const pm: Record<number, UserOfferProgress | OfferProgress> = {}
    live.forEach(o => {
        const p = role === 'agent' ? o.progress : o.own_progress
        if (p) pm[o.id] = p
    })

    // Sort: can_redeem first, then urgency desc:
    const sorted = [...live].sort((a, b) => {
        const pa = pm[a.id], pb = pm[b.id]
        if (pa?.can_redeem && !pb?.can_redeem) return -1
        if (!pa?.can_redeem && pb?.can_redeem) return 1
        return computeUrgencyScore(b, pb) - computeUrgencyScore(a, pa)
    })

    const display = sorted // No longer slicing to show all
    const allRoute = role === 'agent' ? '/agent/offers' : '/super-agent/offers'

    if (isLoading) return (
        <div className="flex gap-4 mb-6 overflow-hidden">
            {[1, 2].map(i => <div key={i} className="h-44 w-full md:w-[calc(50%-8px)] flex-shrink-0 animate-pulse bg-white/5 border border-white/10 rounded-[22px]" />)}
        </div>
    )

    if (!display.length) return null

    return (
        <section className="mb-8">
            <div className="flex items-center justify-between mb-4">
                <h2 className="font-display font-bold text-lg text-dark">
                    Active Offers
                </h2>
                <button
                    onClick={() => navigate(allRoute)}
                    className="flex items-center gap-1 text-sm text-primary hover:underline font-medium"
                >
                    View All {live.length} <ChevronRight className="w-4 h-4" />
                </button>
            </div>

            <OfferNotificationBanner offers={display.slice(0, 3)} progressMap={pm} role={role} />

            <div className="relative group/scroll">
                <div className="
                    flex gap-4 pb-4 px-1
                    overflow-x-auto scrollbar-hide snap-x snap-mandatory
                ">
                    {display.map(offer => (
                        <div key={offer.id} className="w-[85%] sm:w-[calc(50%-8px)] flex-shrink-0 snap-start">
                            <OfferExcitementCard
                                offer={offer}
                                progress={role === 'agent' ? offer.progress : offer.own_progress}
                                role={role}
                                onRedeem={onRedeem}
                                compact={display.length >= 3}
                            />
                        </div>
                    ))}
                </div>
            </div>
        </section>
    )
}
