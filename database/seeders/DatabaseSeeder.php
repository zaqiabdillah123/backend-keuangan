<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User Contoh
        $user = User::create([
            'name' => 'Admin Keuangan',
            'email' => 'admin@perusahaan.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. Buat Anggaran Awal Periode Saat Ini
        Budget::create([
            'company_id' => 1,
            'total_budget' => 10000000.00,
            'remaining_budget' => 10000000.00,
            'period_month' => 9,
            'period_year' => 2026,
        ]);

        // 3. Buat Kategori Pengeluaran & Pemasukan
        Category::create(['name' => 'Operasional ATK', 'type' => 'expense']);
        Category::create(['name' => 'Konsumsi & Rapat', 'type' => 'expense']);
        Category::create(['name' => 'Injeksi Modal / Kas', 'type' => 'income']);
    }
}