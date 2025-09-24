<?php

/**
 * Author: Eng.Fahed
 * API routes for Mini Wallet API
 */

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BeneficiaryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
    });

    // User routes (for frontend compatibility)
    Route::get('user', [AuthController::class, 'profile']);

    // Wallet routes
    Route::prefix('wallet')->group(function () {
        Route::get('balance', [WalletController::class, 'balance']);
        Route::post('add-money', [WalletController::class, 'addMoney']);
        Route::get('statistics', [WalletController::class, 'statistics']);
    });

    // Transaction routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('statistics', [TransactionController::class, 'statistics']);
        Route::get('{id}', [TransactionController::class, 'show']);
    });

    // Beneficiary routes
    Route::prefix('beneficiaries')->group(function () {
        Route::get('/', [BeneficiaryController::class, 'index']);
        Route::post('/', [BeneficiaryController::class, 'store']);
        Route::get('statistics', [BeneficiaryController::class, 'statistics']);
        Route::get('{id}', [BeneficiaryController::class, 'show']);
        Route::put('{id}', [BeneficiaryController::class, 'update']);
        Route::delete('{id}', [BeneficiaryController::class, 'destroy']);
        Route::patch('{id}/toggle-favorite', [BeneficiaryController::class, 'toggleFavorite']);
    });
});
