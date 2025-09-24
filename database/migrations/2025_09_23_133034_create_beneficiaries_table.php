<?php

/**
 * Create Beneficiaries Table
 *
 * This migration creates a beneficiaries table for managing
 * user's saved beneficiaries for quick transfers.
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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Owner of the beneficiary
            $table->unsignedBigInteger('beneficiary_user_id'); // The beneficiary user
            $table->string('nickname')->nullable(); // Custom nickname for the beneficiary
            $table->text('notes')->nullable(); // Optional notes
            $table->boolean('is_favorite')->default(false); // Mark as favorite
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('beneficiary_user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index('beneficiary_user_id');
            $table->index('is_favorite');

            // Unique constraint to prevent duplicate beneficiaries
            $table->unique(['user_id', 'beneficiary_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     * Author: Fahed
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
