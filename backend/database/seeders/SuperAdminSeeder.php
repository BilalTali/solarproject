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
            ['email' => env('SUPER_ADMIN_EMAIL', 'superadmin@andleebsurya.in')],
            [
                'name' => env('SUPER_ADMIN_NAME', 'System Super Admin'),
                'mobile' => env('SUPER_ADMIN_MOBILE', '8888888888'),
                'password' => \Illuminate\Support\Facades\Hash::make(env('SUPER_ADMIN_PASSWORD', 'SuperAdmin@123')),
                'role' => \App\Models\User::ROLE_SUPER_ADMIN,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
