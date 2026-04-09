<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'talibilal342@gmail.com'],
            [
                'name' => 'System Super Admin',
                'mobile' => '8899055335',
                'password' => \Illuminate\Support\Facades\Hash::make('Sugen@9313'),
                'role' => \App\Models\User::ROLE_SUPER_ADMIN,
                'status' => 'active',
                'is_wa_lead_handler' => true,
                'is_public_contact' => true,
                'whatsapp_number' => '8899055335',
                'email_verified_at' => now(),
            ]
        );
    }
}
