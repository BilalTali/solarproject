<?php

namespace App\Services;

use App\Models\{Offer, OfferProgress, OfferRedemption, OfferInstallationLog, SuperAgentAbsorbedPoints, OfferExpiryLog, Lead, User};
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Database\Eloquent\Builder;

class OfferService
{
    /**
     * PROCESS INSTALLATION
     * Called by LeadService when a lead status changes to 'installed'.
     * Credit the installation to all active, eligible offers for this user.
     *
     * @param Lead $lead   The lead that was just marked as installed
     * @param User $agent  The agent (or super agent acting as agent) who owns this lead
     */
    public function processInstallation(Lead $lead, User $agent): void
    {
        // Determine the role context for this user:
        $roleContext = $agent->role;   // 'agent' or 'super_agent'

        // Find all active offers visible to this user's role:
        $activeOffers = Offer::live()
            ->visibleTo($roleContext)
            ->where('offer_type', 'individual')
            ->get();

        DB::transaction(function () use ($lead, $agent, $activeOffers, $roleContext) {
            foreach ($activeOffers as $offer) {

                // Prevent double-counting: check if this lead already credited
                $alreadyCounted = OfferInstallationLog::where('offer_id', $offer->id)
                    ->where('lead_id', $lead->id)
                    ->exists();

                if ($alreadyCounted) continue;

                // Log the installation (immutable)
                OfferInstallationLog::create([
                    'offer_id'     => $offer->id,
                    'user_id'      => $agent->id,
                    'lead_id'      => $lead->id,
                    'installed_at' => now(),
                ]);

                // Update progress — upsert the progress row
                $progress = OfferProgress::lockForUpdate()
                    ->firstOrCreate(
                        ['offer_id' => $offer->id, 'user_id' => $agent->id],
                        [
                            'role_context'             => $roleContext,
                            'total_installations'      => 0,
                            'redeemed_installations'   => 0,
                            'unredeemed_installations' => 0,
                            'redemption_count'         => 0,
                            'can_redeem'               => false,
                            'pending_redemption_count' => 0,
                        ]
                    );

                $progress->increment('total_installations');
                
                if (!$progress->first_installation_at) {
                    $progress->update(['first_installation_at' => now()]);
                }

                $this->recalculate($progress, $offer);

                // Send milestone notifications:
                $this->checkAndNotifyMilestones($progress, $offer, $agent);
            }

            // Also handle COLLECTIVE offers:
            $collectiveOffers = Offer::live()
                ->visibleTo($roleContext)
                ->where('offer_type', 'collective')
                ->where('collective_redeemed', false)
                ->get();

            foreach ($collectiveOffers as $offer) {
                $alreadyCounted = OfferInstallationLog::where('offer_id', $offer->id)
                    ->where('lead_id', $lead->id)
                    ->exists();

                if ($alreadyCounted) continue;

                OfferInstallationLog::create([
                    'offer_id' => $offer->id,
                    'user_id'  => $agent->id,
                    'lead_id'  => $lead->id,
                    'installed_at' => now(),
                ]);

                $offer->increment('current_installations');

                // Check if collective target reached:
                if ($offer->fresh()->current_installations >= $offer->target_installations) {
                    $offer->update([
                        'collective_redeemed'    => true,
                        'collective_redeemed_at' => now(),
                        'status'                 => 'ended', // Auto-end on completion
                    ]);
                    app(NotificationService::class)->notifyCollectiveOfferCompleted($offer);
                }
            }
        });
    }

    /**
     * Recalculate derived fields
     */
    private function recalculate(OfferProgress $progress, Offer $offer): void
    {
        $progress->refresh();   // get latest totals after increment
        $unredeemed = $progress->total_installations - $progress->redeemed_installations;
        $canRedeem  = $unredeemed >= $offer->target_installations;

        $progress->update([
            'unredeemed_installations' => $unredeemed,
            'can_redeem'               => $canRedeem,
            'pending_redemption_count' => $canRedeem ? (int) floor($unredeemed / $offer->target_installations) : 0,
            'last_installation_at'     => now(),
        ]);
    }

    /**
     * Notify agent at milestone points
     */
    private function checkAndNotifyMilestones(OfferProgress $progress, Offer $offer, User $agent): void
    {
        $target = $offer->target_installations;
        if ($target <= 0) return;

        $progress->refresh();
        $unredeemed = $progress->unredeemed_installations;
        $toNext     = $target - ($unredeemed % $target);

        if ($toNext === $target) $toNext = 0; // If they just hit the target exactly

        if (in_array($toNext, [5, 3, 1])) {
            app(NotificationService::class)->notifyOfferMilestone(
                $offer, $agent, $toNext, $progress->pending_redemption_count
            );
        }

        if ($progress->pending_redemption_count > 0 && ($unredeemed % $target) === 0) {
            app(NotificationService::class)->notifyOfferRedeemable(
                $offer, $agent, $progress->pending_redemption_count
            );
        }
    }

    /**
     * REDEEM OFFER
     * Called when agent/SA clicks "Redeem"
     */
    public function redeemOffer(int $offerId, User $user): OfferRedemption
    {
        return DB::transaction(function () use ($offerId, $user) {

            // Lock the offer for reading
            $offer = Offer::lockForUpdate()->findOrFail($offerId);

            // Rule: offer must be live OR within grace period
            if (!$offer->is_claimable) {
                throw new \Exception('This offer has expired and the grace period has passed.');
            }

            // Lock the progress row for update (prevent race conditions)
            $progress = OfferProgress::where('offer_id', $offerId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // ELIGIBILITY GATE — only eligible users can redeem
            if (!$progress->can_redeem) {
                throw new \Exception(
                    "You need {$offer->target_installations} installations to redeem. " .
                    "You currently have {$progress->unredeemed_installations} unredeemed."
                );
            }

            // For collective offers, only one redemption allowed
            if ($offer->offer_type === 'collective' && $offer->collective_redeemed) {
                throw new \Exception('This collective offer prize has already been redeemed by the team.');
            }

            // Determine next redemption number for this user+offer
            $redemptionNumber = $progress->redemption_count + 1;

            // Create the redemption record
            $redemption = OfferRedemption::create([
                'offer_id'          => $offer->id,
                'user_id'           => $user->id,
                'redemption_number' => $redemptionNumber,
                'installations_used'=> $offer->target_installations,
                'status'            => 'pending',
                'claimed_at'        => now(),
            ]);

            // ATOMIC DEDUCTION — increment redeemed, decrement unredeemed
            $progress->redeemed_installations += $offer->target_installations;
            $progress->redemption_count        = $redemptionNumber;
            $progress->last_redeemed_at        = now();
            $progress->save();

            // Recalculate new state
            $this->recalculate($progress, $offer);

            // Notify admin of new redemption claim
            app(NotificationService::class)->notifyAdminOfferRedemptionClaimed($offer, $user, $redemption);

            return $redemption;
        });
    }

    /**
     * Process expiry for a single offer.
     * Called by the OfferExpiryJob after (offer_to + grace_period_days) passes.
     * Absorbs partial points to super agents, zeroes expired progress for UI.
     */
    public function processOfferExpiry(Offer $offer): array
    {
        if ($offer->absorption_processed_at) {
            return ['skipped' => true, 'reason' => 'Already processed'];
        }

        $stats = [
            'agents_processed'          => 0,
            'agents_absorbed'           => 0,
            'agents_grace_period_expired'=> 0,
            'total_points_absorbed'     => 0,
            'total_points_discarded'    => 0,
            'agent_breakdown'           => [],
        ];

        // Get ALL progress rows for this offer
        $progressRows = OfferProgress::where('offer_id', $offer->id)
            ->with(['user.superAgent'])
            ->get();

        DB::transaction(function () use ($offer, $progressRows, &$stats) {

            foreach ($progressRows as $progress) {
                $stats['agents_processed']++;
                $agent = $progress->user;
                $unredeemed = $progress->unredeemed_installations;

                // ── CASE B (per prompt): Agent fell short (unredeemed > 0, but < target) ──────────
                if (!$progress->can_redeem && $unredeemed > 0) {
                    $this->absorbPoints($offer, $agent, $unredeemed, 'agent_fell_short', $stats);
                }

                // ── CASE A (per prompt): Agent was eligible but grace period has now passed ────────
                elseif ($progress->can_redeem) {
                    $this->absorbPoints($offer, $agent, $unredeemed, 'grace_period_expired', $stats);
                }

                // ── CASE C: Agent has zero unredeemed (perfect redemption or no installs) ──
                else {
                    $stats['agent_breakdown'][] = [
                        'agent_id' => $agent->id,
                        'outcome'  => 'nothing_to_absorb',
                        'unredeemed' => 0,
                    ];
                }

                // Zero out for UI regardless of outcome
                $progress->update(['offer_ended_zeroed_at' => now()]);
            }

            // Mark offer as absorption-processed
            $offer->update(['absorption_processed_at' => now()]);

            // Log the expiry run
            OfferExpiryLog::create([
                'offer_id'                    => $offer->id,
                'agents_processed'            => $stats['agents_processed'],
                'agents_absorbed'             => $stats['agents_absorbed'],
                'agents_grace_period_expired' => $stats['agents_grace_period_expired'],
                'total_points_absorbed'       => $stats['total_points_absorbed'],
                'total_points_discarded'      => $stats['total_points_discarded'],
                'agent_breakdown'             => $stats['agent_breakdown'],
                'processed_at'               => now(),
            ]);

            // Notify all affected super agents
            $affectedSAs = SuperAgentAbsorbedPoints::where('offer_id', $offer->id)
                ->where('status', 'unclaimed')
                ->where('absorbed_at', '>=', now()->subMinutes(1))
                ->distinct('super_agent_id')
                ->pluck('super_agent_id');

            foreach ($affectedSAs as $saId) {
                app(NotificationService::class)->notifySuperAgentNewAbsorption($saId, $offer);
            }
        });

        return $stats;
    }

    /**
     * Helper: create an absorption record safely.
     */
    private function absorbPoints(
        Offer $offer,
        User $agent,
        int $points,
        string $reason,
        array &$stats
    ): void {
        if (!$agent->super_agent_id) {
            // Agent has no Super Agent — points are simply discarded
            $stats['total_points_discarded'] += $points;
            return;
        }

        if ($points <= 0) return;

        // Prevent duplicate absorptions (unique constraint)
        $alreadyAbsorbed = SuperAgentAbsorbedPoints::where('offer_id', $offer->id)
            ->where('source_agent_id', $agent->id)
            ->exists();

        if ($alreadyAbsorbed) return;

        SuperAgentAbsorbedPoints::create([
            'super_agent_id'             => $agent->super_agent_id,
            'source_agent_id'            => $agent->id,
            'offer_id'                   => $offer->id,
            'absorbed_installations'     => $points,
            'agent_total_installations'  => OfferProgress::where('offer_id', $offer->id)
                                                ->where('user_id', $agent->id)
                                                ->value('total_installations') ?? 0,
            'offer_target'               => $offer->target_installations,
            'absorption_reason'          => $reason,
            'absorbed_at'                => now(),
            'status'                     => 'unclaimed',
        ]);

        $stats['total_points_absorbed'] += $points;
        $stats['agents_absorbed']++;
    }

    /**
     * ADMIN APPROVES / MARKS DELIVERED
     */
    public function approveRedemption(OfferRedemption $redemption, User $admin, ?string $notes = null): OfferRedemption
    {
        $redemption->update([
            'status'      => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'notes'       => $notes,
        ]);

        app(NotificationService::class)->notifyAgentRedemptionApproved($redemption->offer, $redemption->user, $redemption);

        return $redemption->fresh();
    }

    public function markDelivered(OfferRedemption $redemption, User $admin, ?string $notes = null): OfferRedemption
    {
        $redemption->update([
            'status'       => 'delivered',
            'delivered_at' => now(),
            'notes'        => $notes ?? $redemption->notes,
        ]);

        app(NotificationService::class)->notifyAgentPrizeDelivered($redemption->offer, $redemption->user);

        return $redemption->fresh();
    }

    /**
     * GET OFFERS FOR USER DASHBOARD
     */
    public function getOffersForUser(User $user): array
    {
        $offers = Offer::live()
            ->visibleTo($user->role)
            ->with([
                'progress' => fn($q) => $q->where('user_id', $user->id),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderBy('offer_to')
            ->get();

        return $offers->map(function (Offer $offer) use ($user) {
            $p = $offer->progress->first();

            $myTotal      = $p?->total_installations      ?? 0;
            $myRedeemed   = $p?->redeemed_installations   ?? 0;
            $myUnredeemed = $p?->unredeemed_installations ?? 0;
            $myCount      = $p?->redemption_count         ?? 0;
            $canRedeem    = $p?->can_redeem                ?? false;
            $pendingCount = $p?->pending_redemption_count  ?? 0;
            $target       = $offer->target_installations;

            // Progress within CURRENT redemption cycle
            $cycleInstalls  = $target > 0 ? ($myUnredeemed % $target) : 0;
            $cycleNeeded    = $target - $cycleInstalls;

            return [
                'id'                     => $offer->id,
                'title'                  => $offer->title,
                'description'            => $offer->description,
                'prize_label'            => $offer->prize_label,
                'prize_amount'           => $offer->prize_amount,
                'prize_image_url'        => $offer->prize_image_url,
                'offer_from'             => $offer->offer_from?->format('d M Y'),
                'offer_to'               => $offer->offer_to?->format('d M Y'),
                'offer_type'             => $offer->offer_type,
                'visible_to'             => $offer->visible_to,
                'target_installations'   => $target,
                'is_featured'            => $offer->is_featured,
                'is_annual'              => $offer->is_annual,
                'is_claimable'           => $offer->is_claimable,
                'days_remaining'         => $offer->days_remaining,
                'offer_ended_zeroed_at'  => $p?->offer_ended_zeroed_at ? $p->offer_ended_zeroed_at->format('d M Y') : null,

                // Collective-only fields
                'current_installations'  => $offer->current_installations,
                'collective_remaining'   => $offer->collective_remaining,
                'collective_redeemed'    => $offer->collective_redeemed,

                // Individual — user's personal data
                'my_total_installations'     => $myTotal,
                'my_redeemed_installations'  => $myRedeemed,
                'my_unredeemed_installations'=> $myUnredeemed,
                'my_redemption_count'        => $myCount,
                'can_redeem'                 => $canRedeem,
                'pending_redemption_count'   => $pendingCount,

                // Current cycle progress (toward NEXT redemption)
                'cycle_installations'        => $cycleInstalls,
                'cycle_needed'               => $cycleNeeded,
                'cycle_percentage'           => $target > 0 ? round(($cycleInstalls / $target) * 100) : 0,
            ];
        })->toArray();
    }
}
