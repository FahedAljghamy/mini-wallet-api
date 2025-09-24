<?php

/**
 * Add Transaction Locks Table
 * 
 * This migration creates a transaction_locks table for managing
 * high-concurrency scenarios and preventing race conditions.
 * 
 * @author Fahed
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Author: Fahed
     */
    public function up(): void
    {
        Schema::create('transaction_locks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('lock_type')->default('balance'); // balance, transaction, etc.
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('lock_id')->unique(); // UUID for lock identification
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['user_id', 'lock_type']);
            $table->index('expires_at');
            $table->index('lock_id');
        });
    }

    /**
     * Reverse the migrations.
     * Author: Fahed
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_locks');
    }
};