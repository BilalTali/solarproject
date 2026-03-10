<?php
namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Pagination\LengthAwarePaginator;

class LeadRepository
{
    public function getPaginatedLeads(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Lead::with(['assignedAgent', 'submittedByAgent']);

        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }
        
        if (!empty($filters['assigned_agent_id'])) {
            $query->where('assigned_agent_id', $filters['assigned_agent_id']);
        }
        
        if (!empty($filters['state'])) {
            $query->where('beneficiary_state', $filters['state']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['search'])) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('beneficiary_name', 'like', "%{$search}%")
                  ->orWhere('beneficiary_mobile', 'like', "%{$search}%")
                  ->orWhere('consumer_number', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
    
    public function findByUlid(string $ulid): ?Lead
    {
        return Lead::with(['assignedAgent', 'statusLogs', 'documents'])->where('ulid', $ulid)->first();
    }
}
