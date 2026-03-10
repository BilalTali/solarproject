<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\AgentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingsSeeder::class,
            CommissionSlabSeeder::class,
            IncentiveOfferSeeder::class,
        ]);
        // No agents seeded. Real agents will be created dynamically via the application UI.
    }
}
