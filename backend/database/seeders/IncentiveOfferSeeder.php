<?php

namespace Database\Seeders;

use App\Models\IncentiveOffer;
use Illuminate\Database\Seeder;

class IncentiveOfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'title' => 'Umrah Trip for Two',
                'description' => 'Complete Umrah package for two persons including flight, hotel, and visa. Achieve 50 installations in a quarter to qualify.',
                'offer_type' => 'trip',
                'target_installs' => 50,
                'reward_value' => 150000.00,
                'valid_from' => '2026-04-01',
                'valid_until' => '2026-09-30',
                'is_active' => true,
            ],
            [
                'title' => 'Hero Splendor Plus Bike',
                'description' => 'Brand new Hero Splendor Plus motorcycle. Achieve 30 installations in a quarter to qualify.',
                'offer_type' => 'physical',
                'target_installs' => 30,
                'reward_value' => 75000.00,
                'valid_from' => '2026-04-01',
                'valid_until' => '2026-09-30',
                'is_active' => true,
            ],
            [
                'title' => '₹25,000 Cash Bonus',
                'description' => 'Direct bank transfer of ₹25,000. Achieve 20 installations in a quarter to qualify.',
                'offer_type' => 'cash',
                'target_installs' => 20,
                'reward_value' => 25000.00,
                'valid_from' => '2026-04-01',
                'valid_until' => '2026-06-30',
                'is_active' => true,
            ],
        ];

        foreach ($offers as $offer) {
            IncentiveOffer::updateOrCreate(
                ['title' => $offer['title']],
                $offer
            );
        }
    }
}
