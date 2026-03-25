<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
