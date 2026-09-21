<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Route Autentikasi Publik
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);

    // Route Terproteksi Token Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // CRUD Kategori
        Route::apiResource('categories', CategoryController::class);

        // Ringkasan & Transaksi Keuangan
        Route::get('/summary', [TransactionController::class, 'getSummary']);
        Route::get('/budgets/summary', [TransactionController::class, 'getSummary']);
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
        Route::post('/transactions/expense', [TransactionController::class, 'storeExpense']);
        Route::post('/transactions/income', [TransactionController::class, 'storeIncome']);
        
        // Export Laporan
        Route::get('/transactions/export-pdf', [TransactionController::class, 'exportPdf']);
        Route::get('/transactions/export-excel', [TransactionController::class, 'exportExcel']);
    });
});