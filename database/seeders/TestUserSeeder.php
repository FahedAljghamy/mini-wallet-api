<?php

/**
 * Test User Seeder
 * 
 * Creates test users for development and testing
 * 
 * @author Fahed
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Author: Fahed
     */
    public function run(): void
    {
        // Create test users
        User::create([
            'name' => 'Test User 1',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'balance' => 1000.00,
        ]);

        User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => Hash::make('password123'),
            'balance' => 500.00,
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'balance' => 5000.00,
        ]);

        $this->command->info('Test users created successfully!');
        $this->command->info('Email: test@example.com, Password: password123');
        $this->command->info('Email: test2@example.com, Password: password123');
        $this->command->info('Email: admin@example.com, Password: admin123');
    }
}