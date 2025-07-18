<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // unique identifier
            [
                'name' => 'Admin User',
                'password' => Hash::make('adminpassword'), // set a strong password
                'role' => 'admin',
            ]
        );

        // Create farmer user
        User::updateOrCreate(
            ['email' => 'farmer@example.com'],
            [
                'name' => 'Farmer User',
                'password' => Hash::make('farmerpassword'),
                'role' => 'farmer',
            ]
        );
    }
}
