<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AgentRepository
{
    public function getPaginatedAgents(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::where('role', 'agent');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::where('role', 'agent')->find($id);
    }
}
