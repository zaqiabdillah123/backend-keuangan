<?php

namespace App\Services;

use App\Exceptions\InsufficientBudgetException;
use App\Models\Budget;
use App\Models\BudgetLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function storeExpense(array $data, int $userId): Transaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $budgetId = $data['budget_id'] ?? 1;

            $budget = Budget::where('id', $budgetId)
                ->lockForUpdate()
                ->firstOrFail();

            $totalAmount = $data['quantity'] * $data['unit_price'];

            if ($budget->remaining_budget < $totalAmount) {
                throw new InsufficientBudgetException(
                    "Saldo anggaran tidak mencukupi. Sisa: Rp" . number_format($budget->remaining_budget, 0, ',', '.')
                );
            }

            $previousBalance = $budget->remaining_budget;
            $currentBalance  = $previousBalance - $totalAmount;

            $budget->update([
                'remaining_budget' => $currentBalance,
            ]);

            $transaction = Transaction::create([
                'budget_id'        => $budgetId,
                'category_id'      => $data['category_id'],
                'item_name'        => $data['item_name'],
                'quantity'         => $data['quantity'],
                'unit_price'       => $data['unit_price'],
                'total_amount'     => $totalAmount,
                'type'             => 'expense',
                'transaction_date' => $data['transaction_date'],
                'notes'            => $data['notes'] ?? null,
                'created_by'       => $userId,
            ]);

            BudgetLog::create([
                'budget_id'        => $budget->id,
                'transaction_id'   => $transaction->id,
                'previous_balance' => $previousBalance,
                'current_balance'  => $currentBalance,
                'amount'           => $totalAmount,
                'type'             => 'expense',
            ]);

            return $transaction->load(['category', 'budget', 'budgetLog']);
        });
    }

    public function storeIncome(array $data, int $userId): Transaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $budgetId = $data['budget_id'] ?? 1;

            $budget = Budget::where('id', $budgetId)
                ->lockForUpdate()
                ->firstOrFail();

            $quantity    = $data['quantity'] ?? 1;
            $unitPrice   = $data['unit_price'];
            $totalAmount = $quantity * $unitPrice;

            $previousBalance = $budget->remaining_budget;
            $currentBalance  = $previousBalance + $totalAmount;

            $budget->update([
                'remaining_budget' => $currentBalance,
            ]);

            $transaction = Transaction::create([
                'budget_id'        => $budgetId,
                'category_id'      => $data['category_id'],
                'item_name'        => $data['item_name'],
                'quantity'         => $quantity,
                'unit_price'       => $unitPrice,
                'total_amount'     => $totalAmount,
                'type'             => 'income',
                'transaction_date' => $data['transaction_date'],
                'notes'            => $data['notes'] ?? null,
                'created_by'       => $userId,
            ]);

            BudgetLog::create([
                'budget_id'        => $budget->id,
                'transaction_id'   => $transaction->id,
                'previous_balance' => $previousBalance,
                'current_balance'  => $currentBalance,
                'amount'           => $totalAmount,
                'type'             => 'income',
            ]);

            return $transaction->load(['category', 'budget', 'budgetLog']);
        });
    }
}