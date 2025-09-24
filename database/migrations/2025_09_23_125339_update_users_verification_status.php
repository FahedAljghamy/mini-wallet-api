<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Author: Fahed
     */
    public function up(): void
    {
        // Update all existing users to be verified
        DB::table('users')->update([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     * Author: Fahed
     */
    public function down(): void
    {
        // Set all users as unverified
        DB::table('users')->update([
            'email_verified_at' => null,
        ]);
    }
};
