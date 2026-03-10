<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@suryamitra.in');

        $admin = \App\Models\User::where('email', $adminEmail)->first();
        if (!$admin) {
            \App\Models\User::forceCreate([
                'email' => $adminEmail,
                'name' => env('ADMIN_NAME', 'Super Admin'),
                'mobile' => '9999999999',
                'password' => \Illuminate\Support\Facades\Hash::make(env('ADMIN_PASSWORD', 'Admin@123456')),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }
    }
}
