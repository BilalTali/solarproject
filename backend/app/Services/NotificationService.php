<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Offer;
use App\Models\OfferRedemption;
use App\Models\SuperAgentAbsorbedPoints;

class NotificationService
{
    public function create(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        $dbNotif = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        try {
            $user = User::find($userId);
            if ($user) {
                // Determine the best redirect URL dynamically if absent
                $url = $data['url'] ?? '/notifications';
                $user->notify(new \App\Notifications\WebPushNotification($title, $message, $url));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WebPush failed: ' . $e->getMessage());
        }

        return $dbNotif;
    }

    public function send(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        return $this->create($userId, $type, $title, $message, $data);
    }

    public function notifyAdmins(string $type, string $title, string $message, array $data = []): void
    {
        $admins = User::query()->admins()->get();
        foreach ($admins as $admin) {
            $this->create($admin->id, $type, $title, $message, $data);
        }
    }

    // --- Offer specific ---

    public function notifyOfferRedeemable(Offer $offer, User $user, int $pendingCount): void
    {
        $times = $pendingCount > 1 ? "{$pendingCount} times" : 'once';
        $this->create($user->id, 'offer_redeemable',
            "🎁 You can redeem: {$offer->title}",
            "You've earned enough installations to claim your prize ({$offer->prize_label}) — {$times}! Go to Offers to redeem."
        );
    }

    public function notifyOfferMilestone(Offer $offer, User $user, int $toNext, int $pendingCount): void
    {
        $extra = $pendingCount > 0 ? " (+{$pendingCount} ready to redeem!)" : '';
        $this->create($user->id, 'offer_milestone',
            "⚡ {$toNext} more to next prize — {$offer->title}",
            "Just {$toNext} more installation(s) to claim: {$offer->prize_label}{$extra}"
        );
    }

    public function notifyAdminOfferRedemptionClaimed(Offer $offer, User $agent, OfferRedemption $r): void
    {
        $this->notifyAdmins('offer_redemption_claimed',
            "🎁 Redemption #{$r->redemption_number} — {$offer->title}",
            "{$agent->name} ({$agent->agent_id}) has claimed their prize: {$offer->prize_label}. Please arrange delivery."
        );
    }

    public function notifyAgentRedemptionApproved(Offer $offer, User $agent, OfferRedemption $r): void
    {
        $this->create($agent->id, 'redemption_approved',
            "✅ Prize approved — {$offer->prize_label}",
            "Admin has approved your claim (Redemption #{$r->redemption_number}). Prize delivery is being arranged."
        );
    }

    public function notifyAgentPrizeDelivered(Offer $offer, User $agent): void
    {
        $this->create($agent->id, 'prize_delivered',
            "📦 Prize delivered — {$offer->prize_label}",
            'Your prize has been marked as delivered. Congratulations and keep going!'
        );
    }

    public function notifyCollectiveOfferCompleted(Offer $offer): void
    {
        $this->notifyAdmins('collective_offer_completed',
            "🎯 Collective Offer Completed: {$offer->title}",
            "The team has hit the target of {$offer->target_points} points. Prize: {$offer->prize_label}"
        );
    }

    public function notifyAgentGracePeriodExpired(User $agent, Offer $offer): void
    {
        $this->create($agent->id, 'offer_grace_expired',
            "⏰ Offer Ended: {$offer->title}",
            "The redemption window for \"{$offer->title}\" has now closed. Your eligible reward was not claimed in time."
        );
    }

    public function notifySuperAgentNewAbsorption(int $saId, Offer $offer): void
    {
        $sa = User::find($saId);
        if (! $sa) {
            return;
        }

        $absorbedRecords = SuperAgentAbsorbedPoints::query()
            ->where(fn($q) => $q->where('super_agent_id', $saId))
            ->where(fn($q) => $q->where('offer_id', $offer->id))
            ->where(fn($q) => $q->where('status', 'unclaimed'))
            ->with('sourceAgent:id,name,agent_id')
            ->get();

        if ($absorbedRecords->isEmpty()) {
            return;
        }

        $agentNames = $absorbedRecords->map(fn ($r) => $r->sourceAgent->name)->join(', ');
        $totalPoints = $absorbedRecords->sum('absorbed_installations');

        $visibilityNote = match ($offer->visible_to) {
            'agents' => 'This was an Agents-only offer — your agents worked toward it and their unclaimed points are now yours to claim.',
            'super_agents' => 'This offer was for Super Agents.',
            default => 'This offer was open to all participants.',
        };

        $this->create($sa->id, 'absorbed_points_available',
            "📥 Points Absorbed: \"{$offer->title}\"",
            "The offer \"{$offer->title}\" has ended. " . (string)$totalPoints . " installation points from your agent(s) (" . (string)$agentNames . ") have been transferred to you. {$visibilityNote} Visit Offers → Absorbed Points to claim your reward."
        );
    }

    public function notifyAdminSAAbsorbedClaim(SuperAgentAbsorbedPoints $ap, User $sa): void
    {
        $this->notifyAdmins('absorbed_claim_request',
            "📥 Absorbed Points Claim: {$sa->name}",
            "{$sa->name} ({$sa->super_agent_code}) has claimed {$ap->absorbed_installations} absorbed points from offer \"{$ap->offer->title}\". Please review and deliver."
        );
    }
}
