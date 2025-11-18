<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => env('ADMIN_EMAIL', 'admin@narrativegame.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create test users
        User::factory()->count(10)->create();
    }
}
