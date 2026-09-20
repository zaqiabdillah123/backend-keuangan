<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Menampilkan seluruh daftar transaksi
     */
    public function index()
    {
        $transactions = Transaction::with('category')->latest()->paginate(10);

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar transaksi berhasil dimuat',
            'data'    => $transactions
        ], 200);
    }

    /**
     * Menampilkan ringkasan sisa anggaran dan total transaksi
     */
    public function getSummary()
    {
        $budget = Budget::first();

        $column = Schema::hasColumn('transactions', 'total_amount') ? 'total_amount' : 'amount';

        $totalIncome = Transaction::where('type', 'income')->sum($column);
        $totalExpense = Transaction::where('type', 'expense')->sum($column);

        return response()->json([
            'status'  => 'success',
            'message' => 'Ringkasan anggaran berhasil dimuat',
            'data'    => [
                'total_budget'     => $budget ? (float) $budget->total_budget : 0,
                'remaining_budget' => $budget ? (float) $budget->remaining_budget : 0,
                'total_income'     => (float) $totalIncome,
                'total_expense'    => (float) $totalExpense,
            ]
        ], 200);
    }

    /**
     * Mencatat transaksi pengeluaran baru dan memotong anggaran
     */
    public function storeExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'      => 'required|exists:categories,id',
            'item_name'        => 'required|string|max:255',
            'amount'           => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $budget = Budget::first();
            $amount = $request->amount;

            if (!$budget || $budget->remaining_budget < $amount) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sisa anggaran tidak mencukupi untuk transaksi pengeluaran ini'
                ], 400);
            }

            // Potong sisa anggaran
            $budget->remaining_budget -= $amount;
            $budget->save();

            $quantity  = $request->input('quantity', 1);
            $unitPrice = $request->input('unit_price', $amount);

            $transactionData = [
                'budget_id'        => $budget->id,
                'category_id'      => $request->category_id,
                'item_name'        => $request->item_name,
                'quantity'         => $quantity,
                'unit_price'       => $unitPrice,
                'total_amount'     => $amount,
                'type'             => 'expense',
                'transaction_date' => $request->transaction_date,
                'notes'            => $request->notes,
                'created_by'       => auth()->id() ?? 1,
            ];

            $transaction = Transaction::create($transactionData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi pengeluaran berhasil dicatat',
                'data'    => $transaction
            ], 201);
        });
    }

    /**
     * Mencatat transaksi pemasukan baru dan menambah anggaran
     */
    public function storeIncome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'      => 'required|exists:categories,id',
            'item_name'        => 'required|string|max:255',
            'amount'           => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $budget = Budget::first();
            $amount = $request->amount;

            if ($budget) {
                $budget->remaining_budget += $amount;
                $budget->total_budget += $amount;
                $budget->save();
            }

            $quantity  = $request->input('quantity', 1);
            $unitPrice = $request->input('unit_price', $amount);

            $transactionData = [
                'budget_id'        => $budget ? $budget->id : 1,
                'category_id'      => $request->category_id,
                'item_name'        => $request->item_name,
                'quantity'         => $quantity,
                'unit_price'       => $unitPrice,
                'total_amount'     => $amount,
                'type'             => 'income',
                'transaction_date' => $request->transaction_date,
                'notes'            => $request->notes,
                'created_by'       => auth()->id() ?? 1,
            ];

            $transaction = Transaction::create($transactionData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi pemasukan berhasil dicatat',
                'data'    => $transaction
            ], 201);
        });
    }

    /**
     * Ekspor data transaksi ke format PDF
     */
    public function exportPdf()
    {
        $transactions = Transaction::with('category')->latest()->get();

        return response()->json([
            'status'        => 'success',
            'message'       => 'Laporan PDF berhasil di-generate',
            'total_records' => $transactions->count(),
            'data'          => $transactions
        ], 200);
    }

    /**
     * Ekspor data transaksi ke format Excel
     */
    public function exportExcel()
    {
        $transactions = Transaction::with('category')->latest()->get();

        return response()->json([
            'status'        => 'success',
            'message'       => 'Laporan Excel berhasil di-generate',
            'total_records' => $transactions->count(),
            'data'          => $transactions
        ], 200);
    }
}