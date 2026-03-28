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
            ['email' => 'superadmin@andleebsurya.in'],
            [
                'name' => 'System Super Admin',
                'mobile' => '9999999999',
                'password' => \Illuminate\Support\Facades\Hash::make('SuperAdmin@123'),
                'role' => \App\Models\User::ROLE_SUPER_ADMIN,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
