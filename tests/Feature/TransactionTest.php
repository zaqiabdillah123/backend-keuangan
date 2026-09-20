<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Budget $budget;
    protected Category $categoryExpense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->budget = Budget::create([
            'company_id' => 1,
            'total_budget' => 1000000.00,
            'remaining_budget' => 1000000.00,
            'period_month' => 9,
            'period_year' => 2026,
        ]);

        $this->categoryExpense = Category::create([
            'name' => 'Operasional Kantor',
            'type' => 'expense',
        ]);
    }

    public function test_successfully_records_expense_and_deducts_budget(): void
    {
        $payload = [
            'budget_id' => $this->budget->id,
            'category_id' => $this->categoryExpense->id,
            'item_name' => 'Kertas A4 5 Rim',
            'quantity' => 5,
            'unit_price' => 50000.00,
            'transaction_date' => '2026-09-18 10:00:00',
            'notes' => 'Pembelian ATK Bulanan',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions/expense', $payload);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'status' => 'success',
                'message' => 'Pengeluaran berhasil dicatat.',
                'data' => [
                    'item_name' => 'Kertas A4 5 Rim',
                    'quantity' => 5,
                    'total_amount' => 250000.00,
                    'type' => 'expense',
                ],
            ]);

        // Verifikasi Sisa Anggaran Terpotong
        $this->assertDatabaseHas('budgets', [
            'id' => $this->budget->id,
            'remaining_budget' => 750000.00,
        ]);

        // Verifikasi Transaksi Tersimpan
        $this->assertDatabaseHas('transactions', [
            'budget_id' => $this->budget->id,
            'item_name' => 'Kertas A4 5 Rim',
            'total_amount' => 250000.00,
        ]);

        // Verifikasi Audit Log
        $this->assertDatabaseHas('budget_logs', [
            'budget_id' => $this->budget->id,
            'previous_balance' => 1000000.00,
            'current_balance' => 750000.00,
            'amount' => 250000.00,
            'type' => 'expense',
        ]);
    }

    public function test_fails_expense_when_insufficient_budget_and_rolls_back(): void
    {
        $payload = [
            'budget_id' => $this->budget->id,
            'category_id' => $this->categoryExpense->id,
            'item_name' => 'Laptop Server Utama',
            'quantity' => 1,
            'unit_price' => 2000000.00, // Melebihi anggaran (1,000,000)
            'transaction_date' => '2026-09-18 11:00:00',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions/expense', $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'status' => 'error',
                'error_code' => 'INSUFFICIENT_BUDGET',
            ]);

        // Pastikan Anggaran Tidak Berubah (Rollback)
        $this->assertDatabaseHas('budgets', [
            'id' => $this->budget->id,
            'remaining_budget' => 1000000.00,
        ]);

        // Pastikan Transaksi Tidak Tersimpan
        $this->assertDatabaseMissing('transactions', [
            'item_name' => 'Laptop Server Utama',
        ]);

        // Pastikan Log Tidak Tersimpan
        $this->assertDatabaseMissing('budget_logs', [
            'budget_id' => $this->budget->id,
            'amount' => 2000000.00,
        ]);
    }
}