<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Add this

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Optional: create 10 random users
        // User::factory(10)->create();

        // Create a default test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create farmer user
        User::create([
            'name' => 'Farmer User',
            'email' => 'farmer@farmer.com',
            'password' => Hash::make('password'),
            'role' => 'farmer',
        ]);

        // Create consumer user
        User::create([
            'name' => 'Consumer User',
            'email' => 'consumer@example.com',
            'password' => Hash::make('password'),
            'role' => 'consumer',
        ]);
    }
}
