<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'phone' => '0900000001',
                'password' => Hash::make('hieu1234@A'),
                'role' => 'admin',
                'is_verified' => true,
                'email_verified_at' => now(), // nếu có cột này
            ]
        );

        // User account
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Test User',
                'phone' => '0900000002',
                'password' => Hash::make('hieu4321@A'),
                'role' => 'customer', // đổi nếu hệ thống bạn dùng 'user'
                'is_verified' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
