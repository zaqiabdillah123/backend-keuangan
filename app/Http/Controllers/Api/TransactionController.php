<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransactionController extends Controller
{
    // Mengambil seluruh riwayat transaksi
    public function index()
    {
        $transactions = Transaction::with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    // Menghitung ringkasan anggaran
    public function getSummary()
    {
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $remainingBudget = $totalIncome - $totalExpense;

        return response()->json([
            'status' => 'success',
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'remaining_budget' => (float) $remainingBudget,
        ]);
    }

    // Menyimpan transaksi baru (termasuk Jumlah Barang / Quantity)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity'  => 'nullable|integer|min:1',
            'amount'    => 'required|numeric|min:1',
            'type'      => 'required|in:income,expense',
            'transaction_date' => 'required|date',
            'category_id'      => 'nullable|exists:categories,id',
            'notes'            => 'nullable|string',
        ]);

        $transaction = DB::transaction(function () use ($validated) {
            return Transaction::create([
                'item_name' => $validated['item_name'],
                'quantity'  => $validated['quantity'] ?? 1,
                'amount'    => $validated['amount'],
                'type'      => $validated['type'],
                'transaction_date' => $validated['transaction_date'],
                'category_id'      => $validated['category_id'] ?? null,
                'notes'            => $validated['notes'] ?? null,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan',
            'data' => $transaction
        ], 201);
    }

    // Menghapus transaksi
    public function destroy($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data transaksi/anggaran tidak ditemukan.'
            ], 404);
        }

        $actionTimestamp = now();

        if (Schema::hasTable('budget_logs') && !empty($transaction->budget_id)) {
            DB::table('budget_logs')->insert([
                'budget_id'       => $transaction->budget_id,
                'transaction_id'  => $transaction->id,
                'previous_balance'=> 0,
                'current_balance' => 0,
                'amount'          => $transaction->amount,
                'type'            => 'deletion',
                'created_at'      => $actionTimestamp,
            ]);
        }

        $transaction->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Anggaran/Transaksi berhasil dihapus.',
            'deleted_at' => $actionTimestamp->format('d M Y H:i:s')
        ]);
    }

    // Ekspor Excel (.xls) dengan format kolom terpisah dan styling tabel
    public function exportExcel()
    {
        $transactions = Transaction::orderBy('transaction_date', 'desc')->get();
        $filename = "Laporan_Keuangan_" . date('Y-m-d') . ".xls";

        $html = '<html xmlns:o="urn:schemas-microsoft-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Keuangan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body>';
        $html .= '<table border="1" style="border-collapse:collapse;">';
        $html .= '<tr style="background-color: #059669; color: #ffffff; font-weight: bold; text-align: center;">';
        $html .= '<th style="padding:10px;">ID</th>';
        $html .= '<th style="padding:10px;">Tanggal Transaksi</th>';
        $html .= '<th style="padding:10px;">Keterangan / Nama Barang</th>';
        $html .= '<th style="padding:10px;">Jumlah (Qty)</th>';
        $html .= '<th style="padding:10px;">Tipe Transaksi</th>';
        $html .= '<th style="padding:10px;">Total Nominal (Rp)</th>';
        $html .= '</tr>';

        foreach ($transactions as $item) {
            $html .= '<tr>';
            $html .= '<td style="text-align:center; padding:8px;">' . $item->id . '</td>';
            $html .= '<td style="text-align:center; padding:8px;">' . ($item->transaction_date ? date('Y-m-d', strtotime($item->transaction_date)) : '-') . '</td>';
            $html .= '<td style="padding:8px;">' . htmlspecialchars($item->item_name) . '</td>';
            $html .= '<td style="text-align:center; padding:8px;">' . ($item->quantity ?? 1) . '</td>';
            $html .= '<td style="text-align:center; padding:8px;">' . strtoupper($item->type) . '</td>';
            $html .= '<td style="text-align:right; padding:8px;">' . number_format($item->amount, 0, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'max-age=0'
        ]);
    }

    // Ekspor PDF dalam bentuk Dokumen HTML Cetak
    public function exportPdf()
    {
        $transactions = Transaction::orderBy('transaction_date', 'desc')->get();

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Keuangan</title>';
        $html .= '<style>body{font-family:"Plus Jakarta Sans",sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #ddd;padding:10px;text-align:left;} th{background-color:#059669;color:white;}</style>';
        $html .= '</head><body onload="window.print()">';
        $html .= '<h2>Laporan Keuangan Perusahaan</h2>';
        $html .= '<p>Tanggal Cetak: ' . date('d M Y H:i') . '</p>';
        $html .= '<table><thead><tr><th>No</th><th>Tanggal</th><th>Keterangan</th><th>Qty</th><th>Tipe</th><th>Nominal</th></tr></thead><tbody>';

        $no = 1;
        foreach($transactions as $tx) {
            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            $html .= '<td>' . date('d M Y', strtotime($tx->transaction_date)) . '</td>';
            $html .= '<td>' . htmlspecialchars($tx->item_name) . '</td>';
            $html .= '<td>' . ($tx->quantity ?? 1) . '</td>';
            $html .= '<td>' . strtoupper($tx->type) . '</td>';
            $html .= '<td>Rp ' . number_format($tx->amount, 0, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="Laporan_Keuangan.html"'
        ]);
    }
}