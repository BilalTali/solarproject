<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Offer;
use App\Models\OfferExpiryLog;
use App\Models\OfferInstallationLog;
use App\Models\OfferProgress;
use App\Models\OfferRedemption;
use App\Models\SuperAgentAbsorbedPoints;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class OfferService
{
    /**
     * PROCESS POINTS (Formerly Installation)
     * Called by LeadService when a lead status changes to 'INSTALLED' or beyond.
     * Credit the points to all active, eligible offers.
     */
    public function processPoints(Lead $lead, User $agent): void
    {
        $activeOffers = Offer::live()->get();
        if ($activeOffers->isEmpty()) return;

        $points = $this->getPointsForCapacity($lead->system_capacity);

        // If this capacity earns 0 points (1kW or 2kW), skip offer processing entirely
        if ($points <= 0) return;

        /** @var \App\Models\Offer $offer */
        foreach ($activeOffers as $offer) {
            // Idempotency guard — never double-count the same lead for the same offer
            if (OfferInstallationLog::where('offer_id', $offer->id)
                                    ->where('lead_id', $lead->id)
                                    ->exists()) {
                continue;
            }

            DB::transaction(function () use ($offer, $agent, $lead, $points) {
                // Log the point allocation
                OfferInstallationLog::create([

                    'offer_id'       => $offer->id,
                    'lead_id'        => $lead->id,
                    'user_id'        => $agent->id,
                    'installed_at'   => now(),
                ]);

                // ── Award points to BDE ──────────────────────────────────────────
                // Check if offer is visible to agents
                $agentVisible = in_array($offer->visible_to, ['agents', 'both']);
                if ($agentVisible) {
                    $progress = OfferProgress::firstOrCreate(
                        ['user_id' => $agent->id, 'offer_id' => $offer->id],
                        [
                            'role_context'            => 'agent',
                            'total_points'            => 0,
                            'redeemed_points'         => 0,
                            'redemption_count'        => 0,
                            'pending_redemption_count'=> 0,
                        ]
                    );

                    $newTotal   = (float)$progress->total_points + $points;
                    $effectiveTotal = $agent->role === 'enumerator' ? max(0, $newTotal - 10) : $newTotal;
                    $unredeemed = $effectiveTotal - (float)$progress->redeemed_points;
                    $target     = (float)$offer->target_points;
                    $claimableNow  = $target > 0 ? (int)floor($unredeemed / $target) : 0;
                    $claimableBefore = $progress->pending_redemption_count;

                    $progress->update([
                        'total_points'            => $newTotal,
                        'unredeemed_points'       => $unredeemed,
                        'pending_redemption_count'=> $claimableNow,
                        'can_redeem'              => $unredeemed >= $target,
                        'last_installation_at'    => now(),
                    ]);

                    if (!$progress->first_installation_at) {
                        $progress->update(['first_installation_at' => now()]);
                    }

                    // Notify agent if they just crossed a redemption threshold
                    if ($claimableNow > $claimableBefore) {
                        app(NotificationService::class)->notifyOfferRedeemable($offer, $agent, $claimableNow);
                    }
                }

                // ── Award points to BDM (if offer visible to super agents) ───────
                $targetSAId = (int) $agent->super_agent_id;

                // SPECIAL CASE: If the "agent" themselves is a Super Agent (e.g. they created an enumerator)
                // they should also receive the SA-level points for this installation.
                if (!$targetSAId && $agent->isSuperAgent()) {
                    $targetSAId = (int) $agent->id;
                }

                if ($targetSAId) {
                    // Award points to Super Agent immediately if visible to them
                    $saVisible = in_array($offer->visible_to, ['super_agents', 'both']);
                    if ($saVisible) {
                        $saProgress = OfferProgress::firstOrCreate(
                            ['user_id' => $targetSAId, 'offer_id' => $offer->id],
                            [
                                'role_context'            => 'super_agent',
                                'total_points'            => 0,
                                'redeemed_points'         => 0,
                                'redemption_count'        => 0,
                                'pending_redemption_count'=> 0,
                            ]
                        );
                        $saNew = (float)$saProgress->total_points + $points;
                        $saTarget = (float)$offer->target_points;
                        $saUnredeemed = $saNew - (float)$saProgress->redeemed_points;
                        $saClaimable  = $saTarget > 0 ? (int)floor($saUnredeemed / $saTarget) : 0;
                        
                        $saProgress->update([
                            'total_points'            => $saNew,
                            'unredeemed_points'       => $saUnredeemed,
                            'pending_redemption_count'=> $saClaimable,
                            'can_redeem'              => $saUnredeemed >= $saTarget,
                            'last_installation_at'    => now(),
                        ]);

                        if (!$saProgress->first_installation_at) {
                            $saProgress->update(['first_installation_at' => now()]);
                        }
                    }
                }

                // ── Handle Collective Offers ───────────────────────────────────
                if ($offer->offer_type === 'collective' && !$offer->collective_redeemed) {
                    $newCollective = (float)$offer->current_points + $points;
                    $offer->update(['current_points' => $newCollective]);

                    if ($newCollective >= $offer->target_points) {
                        $offer->update([
                            'collective_redeemed' => true,
                            'collective_redeemed_at' => now(),
                            'status' => 'ended'
                        ]);
                        app(NotificationService::class)->notifyCollectiveOfferCompleted($offer);
                    }
                }
            });
        }
    }

    /**
     * Recalculate derived fields
     */
    private function recalculate(OfferProgress $progress, Offer $offer): void
    {
        $progress->refresh();   // get latest totals after increment
        $agent = $progress->user;
        $effectiveTotal = $agent && $agent->role === 'enumerator' ? max(0, (float)$progress->total_points - 10) : (float)$progress->total_points;
        $unredeemed = $effectiveTotal - (float)$progress->redeemed_points;
        $canRedeem = $unredeemed >= $offer->target_points;

        $progress->update([
            'unredeemed_points' => $unredeemed,
            'can_redeem' => $canRedeem,
            'pending_redemption_count' => $canRedeem ? (int) floor($unredeemed / $offer->target_points) : 0,
            'last_installation_at' => now(),
        ]);
    }

    private function getPointsForCapacity(?string $capacity): float
    {
        $capacityRaw = strtolower(str_replace(' ', '', $capacity ?? ''));
        if (empty($capacityRaw)) {
            return 0;
        }

        $pointsMap = json_decode(Setting::getValue('capacity_points_json', '{}'), true);

        // 1. Try exact match (e.g., "3kw", "above_10kw", "3.3kw")
        if (isset($pointsMap[$capacityRaw])) {
            return (float) $pointsMap[$capacityRaw];
        }

        // 2. Try matching with "kw" suffix if missing (e.g., "3" -> "3kw")
        if (is_numeric($capacityRaw) && isset($pointsMap[$capacityRaw . 'kw'])) {
            return (float) $pointsMap[$capacityRaw . 'kw'];
        }

        return 0;
    }

    /**
     * Notify agent at milestone points
     */
    private function checkAndNotifyMilestones(OfferProgress $progress, Offer $offer, User $agent): void
    {
        $target = $offer->target_points;
        if ($target <= 0) {
            return;
        }

        $progress->refresh();
        $unredeemed = (float)$progress->unredeemed_points;
        $toNext = $target - ($unredeemed % $target);

        if ($toNext === $target) {
            $toNext = 0;
        } // If they just hit the target exactly

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
            $offer = Offer::findOrFail($offerId);

            if (!$offer->is_claimable) {
                throw new \Exception('This offer has expired and is no longer claimable.');
            }

            $progress = OfferProgress::where('user_id', $user->id)
                ->where('offer_id', $offerId)
                ->lockForUpdate()   // CRITICAL: prevents race condition on double-tap
                ->firstOrFail();

            $effectiveTotal = $user->role === 'enumerator' ? max(0, (float)$progress->total_points - 10) : (float)$progress->total_points;
            $unredeemed = $effectiveTotal - (float)$progress->redeemed_points;
            $target     = (float)$offer->target_points;

            if ($unredeemed < $target) {
                throw new \InvalidArgumentException(
                    "You need {$target} points to redeem this offer. " .
                    "You currently have " . number_format($unredeemed, 1) . " unredeemed points."
                );
            }

            // Deduct exactly one target from unredeemed (surplus carries forward):
            $newRedeemed       = (float)$progress->redeemed_points + $target;
            $newUnredeemed     = $unredeemed - $target;  // the surplus after this redemption
            $newPending        = $target > 0 ? (int)floor($newUnredeemed / $target) : 0;
            $redemptionNumber  = $progress->redemption_count + 1;

            $progress->update([
                'redeemed_points'         => $newRedeemed,
                'unredeemed_points'       => $newUnredeemed,
                'redemption_count'        => $redemptionNumber,
                'pending_redemption_count'=> $newPending,
                'can_redeem'              => $newUnredeemed >= $target,
                'last_redeemed_at'        => now(),
            ]);

            $redemption = OfferRedemption::create([
                'offer_id'           => $offerId,
                'user_id'            => $user->id,
                'redemption_number'  => $redemptionNumber,
                'points_used' => $target,

                'status'             => 'pending',
                'claimed_at'         => now(),
            ]);

            // Notify admin
            app(NotificationService::class)->notifyAdminOfferRedemptionClaimed($offer, $user, $redemption);

            // ── SHARED POOL SYNC: If an Agent redeems, deduct from their Super Agent's override balance ──
            if ($user->role === 'agent' && $user->super_agent_id) {
                $this->syncRedemptionToSuperAgent($offer, $user, $target);
            }

            return $redemption;
        });
    }

    /**
     * Synchronize a redemption from an agent to their Super Agent
     * This ensures that points claimed by an agent are deducted from the SA's "potential" balance.
     */
    private function syncRedemptionToSuperAgent(Offer $offer, User $agent, float $points): void
    {
        $saProgress = OfferProgress::where('user_id', $agent->super_agent_id)
            ->where('offer_id', $offer->id)
            ->lockForUpdate()
            ->first();

        if ($saProgress) {
            // We increment the SA's redeemed_points to reduce their unredeemed total.
            // This treats the override as "used" once the underlying agent claims it.
            $newRedeemed = (float)$saProgress->redeemed_points + $points;
            
            // Limit to total_points to avoid negative unredeemed
            if ($newRedeemed > (float)$saProgress->total_points) {
                $newRedeemed = (float)$saProgress->total_points;
            }

            $saProgress->update([
                'redeemed_points' => $newRedeemed,
            ]);
            
            $saProgress->recalculate();
        }
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
            'agents_processed' => 0,
            'agents_absorbed' => 0,
            'agents_grace_period_expired' => 0,
            'total_points_absorbed' => 0,
            'total_points_discarded' => 0,
            'agent_breakdown' => [],
        ];

        // Get ALL progress rows for this offer
        $progressRows = OfferProgress::query()->where(fn ($q) => $q->where('offer_id', $offer->id))
            ->with(['user.superAgent'])
            ->get();

        DB::transaction(function () use ($offer, $progressRows, &$stats) {

            /** @var \App\Models\OfferProgress $progress */
            foreach ($progressRows as $progress) {
                $stats['agents_processed']++;
                $agent = $progress->user;
                $unredeemed = (float)$progress->unredeemed_points;
                $target = (float)$offer->target_points;

                // ── CASE B: Agent fell short (unredeemed > 0, but < target) ──────────
                if ($unredeemed > 0 && $unredeemed < $target) {
                    $this->absorbPoints($offer, $agent, $unredeemed, 'agent_fell_short', $stats);
                }

                // ── CASE A: Agent was eligible but grace period has now passed ────────
                elseif ($unredeemed >= $target) {
                    $this->absorbPoints($offer, $agent, $unredeemed, 'grace_period_expired', $stats);
                }

                // ── CASE C: Agent has zero unredeemed (perfect redemption or no installs) ──
                else {
                    $stats['agent_breakdown'][] = [
                        'agent_id' => $agent->id,
                        'outcome' => 'nothing_to_absorb',
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
                'offer_id' => $offer->id,
                'agents_processed' => $stats['agents_processed'],
                'agents_absorbed' => $stats['agents_absorbed'],
                'agents_grace_period_expired' => $stats['agents_grace_period_expired'],
                'total_points_absorbed' => $stats['total_points_absorbed'],
                'total_points_discarded' => $stats['total_points_discarded'],
                'agent_breakdown' => $stats['agent_breakdown'],
                'processed_at' => now(),
            ]);

            // Notify all affected super agents
            $affectedSAs = SuperAgentAbsorbedPoints::query()
                ->where(fn($q) => $q->where('offer_id', $offer->id))
                ->where(fn($q) => $q->where('status', 'unclaimed'))
                ->where(fn($q) => $q->where('absorbed_at', '>=', now()->subMinutes(1)))
                ->distinct('super_agent_id')
                ->pluck('super_agent_id');

            foreach ($affectedSAs as $saId) {
                app(NotificationService::class)->notifySuperAgentNewAbsorption((int)$saId, $offer);
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
        if ($points <= 0) {
            return;
        }

        // Prevent duplicate absorptions (unique constraint)
        $alreadyAbsorbed = SuperAgentAbsorbedPoints::query()->where(fn ($q) => $q->where('offer_id', $offer->id))
            ->where(fn ($q) => $q->where('source_agent_id', $agent->id))
            ->exists();

        if ($alreadyAbsorbed) {
            return;
        }

        SuperAgentAbsorbedPoints::create([
            'super_agent_id' => $agent->super_agent_id, // May be null for orphan agents
            'source_agent_id' => $agent->id,
            'offer_id' => $offer->id,
            'absorbed_points' => $points,
            'agent_total_points' => OfferProgress::query()->where(fn ($q) => $q->where('offer_id', $offer->id))
                ->where(fn ($q) => $q->where('user_id', $agent->id))
                ->value('total_points') ?? 0,
            'offer_target' => $offer->target_points,
            'absorption_reason' => $reason,
            'absorbed_at' => now(),
            'status' => 'unclaimed',
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
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        app(NotificationService::class)->notifyAgentRedemptionApproved($redemption->offer, $redemption->user, $redemption);

        return $redemption->fresh();
    }

    public function approveRedemptionByAdmin(OfferRedemption $redemption, User $admin, ?string $notes = null): OfferRedemption
    {
        $redemption->update([
            'status' => 'admin_approved',
            'admin_approved_by' => $admin->id,
            'admin_approved_at' => now(),
            'notes' => $notes,
        ]);

        // Keep existing notification or add a new one if needed
        app(NotificationService::class)->notifyAgentRedemptionApproved($redemption->offer, $redemption->user, $redemption);

        return $redemption->fresh();
    }

    public function markDelivered(OfferRedemption $redemption, User $admin, ?string $notes = null): OfferRedemption
    {
        $redemption->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'notes' => $notes ?? $redemption->notes,
        ]);

        app(NotificationService::class)->notifyAgentPrizeDelivered($redemption->offer, $redemption->user);

        return $redemption->fresh();
    }

    /**
     * CANCEL REDEMPTION
     * Reverts points back to the user's progress.
     */
    public function cancelRedemption(OfferRedemption $redemption, User $admin, ?string $notes = null): OfferRedemption
    {
        return DB::transaction(function () use ($redemption, $admin, $notes) {
            if ($redemption->status !== 'pending' && $redemption->status !== 'approved') {
                throw new \Exception('Only pending or approved redemptions can be cancelled.');
            }

            $pointsToRevert = (float) $redemption->points_used;
            $user = $redemption->user;
            $offer = $redemption->offer;

            // Find progress
            $progress = OfferProgress::where('user_id', $user->id)
                ->where('offer_id', $offer->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Revert points
            $newRedeemed = max(0, (float)$progress->redeemed_points - $pointsToRevert);
            $newUnredeemed = (float)$progress->unredeemed_points + $pointsToRevert;
            
            // We only decrement redemption_count if it was already incremented during redemption
            $newCount = max(0, $progress->redemption_count - 1);
            
            $target = (float)$offer->target_points;
            $newPendingCount = $target > 0 ? (int)floor($newUnredeemed / $target) : 0;

            $progress->update([
                'redeemed_points' => $newRedeemed,
                'unredeemed_points' => $newUnredeemed,
                'redemption_count' => $newCount,
                'pending_redemption_count' => $newPendingCount,
                'can_redeem' => $newUnredeemed >= $target,
            ]);

            $redemption->update([
                'status' => 'cancelled',
                'notes' => $notes ?? "Cancelled by admin: " . $admin->name,
                // We don't set approved_by/approved_at for cancellation
            ]);

            // Notify user
            // app(NotificationService::class)->notifyAgentRedemptionCancelled($offer, $user, $redemption);

            return $redemption->fresh();
        });
    }

    /**
     * GET OFFERS FOR USER DASHBOARD
     */
    public function getOffersForUser(User $user): array
    {
        $offers = Offer::live()
            ->visibleTo($user->role)
            ->with(['progress' => function ($q) use ($user) {
                /** @var \Illuminate\Database\Eloquent\Relations\HasMany $q */
                $q->where(fn ($q2) => $q2->where('user_id', $user->id));
            }])
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderBy('offer_to')
            ->get();

        return $offers->map(function (Offer $offer) use ($user) {
            $p = $offer->progress->first();

            $myTotal = (float)($p?->total_points ?? 0);
            $effectiveTotal = $user->role === 'enumerator' ? max(0, $myTotal - 10) : $myTotal;
            
            $myRedeemed = (float)($p?->redeemed_points ?? 0);
            $myUnredeemed = max(0, $effectiveTotal - $myRedeemed);
            $myCount = $p?->redemption_count ?? 0;
            $canRedeem = $p?->can_redeem ?? false;
            $pendingCount = $p?->pending_redemption_count ?? 0;
            $target = max(0.1, (float)$offer->target_points);

            // Progress within CURRENT redemption cycle
            $cycleInstalls = ($target > 0) ? ($myUnredeemed % $target) : 0;
            $cycleNeeded = max(0, $target - $cycleInstalls);

            // Helper to safe-format dates
            $safeFormat = fn($date) => ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface) 
                ? $date->format('d M Y') 
                : (is_string($date) ? date('d M Y', strtotime($date)) : null);

            $personalData = [
                // Personal data (Individual)
                'my_total_points' => $myTotal,
                'my_redeemed_points' => $myRedeemed,
                'my_unredeemed_points' => $myUnredeemed,
                'my_redemption_count' => $myCount,
                'can_redeem' => $canRedeem,
                'pending_redemption_count' => $pendingCount,

                // Current cycle progress (toward NEXT redemption)
                'cycle_points' => $cycleInstalls, 
                'cycle_needed' => $cycleNeeded,
                'cycle_percentage' => $target > 0 ? round(($cycleInstalls / $target) * 100) : 0,
                
                // Also include basic fields inside for completeness
                'id' => $offer->id,
                'title' => $offer->title,
                'description' => $offer->description,
                'prize_label' => $offer->prize_label,
                'prize_amount' => $offer->prize_amount,
                'prize_image_url' => $offer->prize_image_url,
                'offer_from' => $safeFormat($offer->offer_from),
                'offer_to' => $safeFormat($offer->offer_to),
                'offer_type' => $offer->offer_type,
                'target_points' => $target,
                'is_featured' => $offer->is_featured,
                'is_annual' => $offer->is_annual,
                'is_claimable' => $offer->is_claimable,
                'days_remaining' => $offer->days_remaining,
                'offer_ended_zeroed_at' => $safeFormat($p?->offer_ended_zeroed_at),
            ];

            return [
                'id' => $offer->id,
                'title' => $offer->title,
                'description' => $offer->description,
                'prize_label' => $offer->prize_label,
                'prize_amount' => $offer->prize_amount,
                'prize_image_url' => $offer->prize_image_url,
                'offer_from' => $safeFormat($offer->offer_from),
                'offer_to' => $safeFormat($offer->offer_to),
                'offer_type' => $offer->offer_type,
                'visible_to' => $offer->visible_to,
                'target_points' => $target,
                'is_featured' => $offer->is_featured,
                'is_annual' => $offer->is_annual,
                'is_claimable' => $offer->is_claimable,
                'days_remaining' => $offer->days_remaining,

                // Relationship data nested as per frontend Expectation (OfferWithProgress)
                'progress' => $user->role === 'agent' ? $personalData : null,
                'own_progress' => $user->role === 'super_agent' ? $personalData : null,

                // Collective-only fields (Top level)
                'current_points' => $offer->current_points,
                'collective_remaining' => $offer->collective_remaining,
                'collective_redeemed' => $offer->collective_redeemed,
            ];
        })->toArray();
    }

    /**
     * Get team performance summary for all live individual offers
     */
    public function getTeamOfferPerformance(User $superAgent): array
    {
        $agents = User::query()->agents()
            ->where(fn ($q) => $q->where('super_agent_id', $superAgent->id))
            ->get();
        $agentIds = $agents->pluck('id');

        $activeOffers = Offer::live()
            ->visibleTo('super_agent')
            ->individual()
            ->get();

        return $activeOffers->map(function ($offer) use ($agents, $agentIds) {
            $progressData = OfferProgress::query()
                ->where(fn ($q) => $q->where('offer_id', $offer->id))
                ->whereIn('user_id', $agentIds)
                ->get();

            $teamTotal = (float) $progressData->sum('total_points');
            $teamRedeemed = (float) $progressData->sum('redeemed_points');
            $teamRedemptionCount = (int) $progressData->sum('redemption_count');

            $agentStats = $agents->map(function ($agent) use ($offer, $progressData) {
                $p = $progressData->firstWhere('user_id', $agent->id);
                $totalPoints = (float) ($p?->total_points ?? 0);
                $unredeemed = (float) ($p?->unredeemed_points ?? 0);
                $target = (float) $offer->target_points;
                
                $cyclePoints = $target > 0 ? ($unredeemed % $target) : 0;
                $cycleNeeded = max(0, $target - $cyclePoints);

                return [
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name,
                    'agent_code' => $agent->agent_id,
                    'total_points' => $totalPoints,
                    'cycle_points' => $cyclePoints,
                    'cycle_needed' => $cycleNeeded,
                    'can_redeem' => $p?->can_redeem ?? false,
                ];
            });

            return [
                'id' => $offer->id,
                'offer_id' => $offer->id,
                'offer_title' => $offer->title,
                'title' => $offer->title,
                'target_points' => (float)$offer->target_points,
                'prize_label' => $offer->prize_label,
                'prize_image_url' => $offer->prize_image_url,
                'offer_to' => $offer->offer_to?->format('d M Y'),
                'team_totals' => [
                    'total_points' => $teamTotal,
                    'redeemed_points' => $teamRedeemed,
                    'unredeemed_points' => $teamTotal - $teamRedeemed,
                    'redemption_count' => $teamRedemptionCount,
                ],
                'agents' => $agentStats,
            ];
        })->toArray();
    }
}
