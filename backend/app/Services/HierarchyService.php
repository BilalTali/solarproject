<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lead;

class HierarchyService
{
    /**
     * Get the dynamic commission chain for a lead based on its submitter.
     * Returns: Array of Users who should receive a commission for this lead.
     * Order: [Immediate Parent, Grandparent, ...] (up to, but not including, Admin)
     */
    public function getCommissionChain(User $submitter): array
    {
        $chain = [];
        $current = $submitter;

        // Traverse up the parent chain
        while ($current->parent_id && $current->id !== $current->parent_id) {
            $parent = User::find($current->parent_id);
            if (! $parent) {
                break;
            }

            // Admin is the final payer, they don't receive commission themselves in this flow
            if ($parent->isAdmin()) {
                break;
            }

            $chain[] = $parent;
            $current = $parent;

            // Safety break for deep hierarchies
            if (count($chain) > 5) break;
        }

        return $chain;
    }

    /**
     * Resolve the initial owner, pool, and verification status for a new lead.
     * Implement Case 1-5 logic dynamically.
     */
    public function resolveInitialRouting(User $submitter): array
    {
        // CASE 5 fallback: Super Agent created directly
        if ($submitter->isSuperAgent()) {
            return [
                'owner_type' => 'super_agent_pool',
                'verification_status' => 'pending_super_agent_verification',
                'assigned_super_agent_id' => $submitter->id,
            ];
        }

        // CASE 4 fallback: Agent created directly (usually under an SA)
        if ($submitter->isAgent()) {
            $saId = $submitter->parent_id; 
            return [
                'owner_type' => $saId ? 'super_agent_pool' : 'admin_pool',
                'verification_status' => $saId ? 'pending_super_agent_verification' : 'not_required',
                'assigned_super_agent_id' => $saId,
                'assigned_agent_id' => $submitter->id,
            ];
        }

        // CASE 1, 2, 3: Enumerator submissions
        if ($submitter->isEnumerator()) {
            $parent = $submitter->parent;
            
            if (! $parent || $parent->isAdmin()) {
                // Case 1: Enum under Admin
                return [
                    'owner_type' => 'admin_pool',
                    'verification_status' => 'not_required',
                    'assigned_super_agent_id' => $parent?->id, // Admin id if exists
                ];
            }

            if ($parent->isSuperAgent()) {
                // Case 2: Enum under Super Agent
                return [
                    'owner_type' => 'super_agent_pool',
                    'verification_status' => 'pending_super_agent_verification',
                    'assigned_super_agent_id' => $parent->id,
                ];
            }

            if ($parent->isAgent()) {
                // Case 3: Enum under Agent
                return [
                    'owner_type' => 'agent_pool',
                    'verification_status' => 'pending_agent_verification',
                    'assigned_agent_id' => $parent->id,
                    'assigned_super_agent_id' => $parent->parent_id,
                ];
            }
        }

        // Fallback for Admin or Public submissions (Public form has own logic in LeadService)
        return [
            'owner_type' => 'admin_pool',
            'verification_status' => 'not_required',
        ];
    }

    /**
     * Check if setting a new parent would create a circular reference.
     */
    public function hasCircularReference(User $user, int $newParentId): bool
    {
        if ($user->id === $newParentId) {
            return true;
        }

        $currentId = $newParentId;
        $visited = [$user->id];

        while ($currentId) {
            if (in_array($currentId, $visited)) {
                return true;
            }
            $visited[] = $currentId;
            $parent = User::find($currentId);
            if (! $parent || ! $parent->parent_id) {
                break;
            }
            $currentId = $parent->parent_id;

            // Safety break
            if (count($visited) > 20) break;
        }

        return false;
    }
}
