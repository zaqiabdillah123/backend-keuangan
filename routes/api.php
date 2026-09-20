<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // CRUD Kategori
        Route::apiResource('categories', CategoryController::class);

        // Transaksi & Anggaran
        Route::get('/budgets/summary', [TransactionController::class, 'getSummary']);
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/export-pdf', [TransactionController::class, 'exportPdf']);
        Route::get('/transactions/export-excel', [TransactionController::class, 'exportExcel']);
        Route::post('/transactions/expense', [TransactionController::class, 'storeExpense']);
        Route::post('/transactions/income', [TransactionController::class, 'storeIncome']);
    });
});