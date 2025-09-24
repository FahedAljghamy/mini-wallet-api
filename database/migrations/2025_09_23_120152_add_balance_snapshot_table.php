<?php

/**
 * Add Balance Snapshot Table
 * 
 * This migration creates a balance_snapshots table for efficient
 * balance tracking in large-scale scenarios.
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
        Schema::create('balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('balance', 15, 2);
            $table->timestamp('snapshot_date');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['user_id', 'snapshot_date']);
            $table->index('snapshot_date');
        });
    }

    /**
     * Reverse the migrations.
     * Author: Fahed
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_snapshots');
    }
};