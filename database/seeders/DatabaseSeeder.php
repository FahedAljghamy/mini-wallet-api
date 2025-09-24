<?php

/**
 * Database Seeder
 *
 * Main seeder class that calls all other seeders
 *
 * @author Fahed
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Author: Fahed
     */
    public function run(): void
    {
        $this->call([
            ProfessionalSeeder::class,
            TestUserSeeder::class,
        ]);
    }
}
