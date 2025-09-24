<?php

/**
 * Professional Seeder
 *
 * Creates professional test data for Mini Wallet application
 *
 * @author Fahed
 */

namespace Database\Seeders;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProfessionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Author: Fahed
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create professional users with English names
            $users = [
                [
                    'name' => 'Alexander Johnson',
                    'email' => 'alexander.johnson@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 25000.00,
                    'email_verified_at' => now()->subDays(30),
                    'created_at' => now()->subDays(30),
                ],
                [
                    'name' => 'Sarah Williams',
                    'email' => 'sarah.williams@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 15000.00,
                    'email_verified_at' => now()->subDays(25),
                    'created_at' => now()->subDays(25),
                ],
                [
                    'name' => 'Michael Brown',
                    'email' => 'michael.brown@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 35000.00,
                    'email_verified_at' => now()->subDays(20),
                    'created_at' => now()->subDays(20),
                ],
                [
                    'name' => 'Emily Davis',
                    'email' => 'emily.davis@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 12000.00,
                    'email_verified_at' => now()->subDays(15),
                    'created_at' => now()->subDays(15),
                ],
                [
                    'name' => 'David Miller',
                    'email' => 'david.miller@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 45000.00,
                    'email_verified_at' => now()->subDays(10),
                    'created_at' => now()->subDays(10),
                ],
                [
                    'name' => 'Jessica Wilson',
                    'email' => 'jessica.wilson@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 18000.00,
                    'email_verified_at' => now()->subDays(8),
                    'created_at' => now()->subDays(8),
                ],
                [
                    'name' => 'Christopher Moore',
                    'email' => 'christopher.moore@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 30000.00,
                    'email_verified_at' => now()->subDays(5),
                    'created_at' => now()->subDays(5),
                ],
                [
                    'name' => 'Amanda Taylor',
                    'email' => 'amanda.taylor@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 22000.00,
                    'email_verified_at' => now()->subDays(3),
                    'created_at' => now()->subDays(3),
                ],
                [
                    'name' => 'James Anderson',
                    'email' => 'james.anderson@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 28000.00,
                    'email_verified_at' => now()->subDays(2),
                    'created_at' => now()->subDays(2),
                ],
                [
                    'name' => 'Jennifer Thomas',
                    'email' => 'jennifer.thomas@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 16000.00,
                    'email_verified_at' => now()->subDay(),
                    'created_at' => now()->subDay(),
                ],
                [
                    'name' => 'Robert Jackson',
                    'email' => 'robert.jackson@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 32000.00,
                    'email_verified_at' => now()->subHours(12),
                    'created_at' => now()->subHours(12),
                ],
                [
                    'name' => 'Lisa White',
                    'email' => 'lisa.white@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 19000.00,
                    'email_verified_at' => now()->subHours(6),
                    'created_at' => now()->subHours(6),
                ],
                [
                    'name' => 'Daniel Harris',
                    'email' => 'daniel.harris@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 41000.00,
                    'email_verified_at' => now()->subHours(3),
                    'created_at' => now()->subHours(3),
                ],
                [
                    'name' => 'Michelle Martin',
                    'email' => 'michelle.martin@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 14000.00,
                    'email_verified_at' => now()->subHours(1),
                    'created_at' => now()->subHours(1),
                ],
                [
                    'name' => 'Kevin Garcia',
                    'email' => 'kevin.garcia@example.com',
                    'password' => Hash::make('password123'),
                    'balance' => 26000.00,
                    'email_verified_at' => now()->subMinutes(30),
                    'created_at' => now()->subMinutes(30),
                ],
            ];

            $createdUsers = [];
            foreach ($users as $userData) {
                $user = User::create($userData);
                $createdUsers[] = $user;
            }

            // Create realistic transactions
            $this->createTransactions($createdUsers);

            $this->command->info('Professional data created successfully!');
            $this->command->info('Created ' . count($createdUsers) . ' users with realistic transactions');
            $this->command->info('Test credentials:');
            $this->command->info('Email: alexander.johnson@example.com, Password: password123');
            $this->command->info('Email: sarah.williams@example.com, Password: password123');
            $this->command->info('Email: michael.brown@example.com, Password: password123');
        });
    }

    /**
     * Create realistic transactions between users
     * Author: Fahed
     */
    private function createTransactions(array $users): void
    {
        $transactionTypes = [
            'Transfer to family member',
            'Payment for services',
            'Business transaction',
            'Personal loan repayment',
            'Gift transfer',
            'Bill payment',
            'Shopping refund',
            'Salary transfer',
            'Investment return',
            'Emergency fund transfer'
        ];

        $statuses = ['completed', 'completed', 'completed', 'completed', 'pending']; // Mostly completed

        for ($i = 0; $i < 50; $i++) {
            $sender = $users[array_rand($users)];
            $receiver = $users[array_rand($users)];

            // Don't allow self-transfers
            while ($sender->id === $receiver->id) {
                $receiver = $users[array_rand($users)];
            }

            $amount = $this->generateRealisticAmount();
            $commissionFee = $amount * 0.015; // 1.5% commission
            $status = $statuses[array_rand($statuses)];

            Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commissionFee,
                'status' => $status,
                'created_at' => now()->subDays(rand(1, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            ]);
        }
    }

    /**
     * Generate realistic transaction amounts
     * Author: Fahed
     */
    private function generateRealisticAmount(): float
    {
        $amounts = [
            // Small amounts (common)
            50, 75, 100, 125, 150, 200, 250, 300, 350, 400, 450, 500,
            // Medium amounts
            750, 1000, 1250, 1500, 1750, 2000, 2500, 3000, 3500, 4000, 4500, 5000,
            // Large amounts (less common)
            7500, 10000, 12500, 15000, 20000, 25000, 30000, 50000
        ];

        // Weight towards smaller amounts
        $weights = [
            // Small amounts get higher weight
            10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10,
            // Medium amounts get medium weight
            5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5,
            // Large amounts get lower weight
            2, 2, 2, 2, 1, 1, 1, 1
        ];

        $randomIndex = $this->weightedRandom($weights);
        return $amounts[$randomIndex];
    }

    /**
     * Weighted random selection
     * Author: Fahed
     */
    private function weightedRandom(array $weights): int
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($weights as $index => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $index;
            }
        }

        return 0;
    }
}
