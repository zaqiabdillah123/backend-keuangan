<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 20px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Transaksi Keuangan</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="18%">Tanggal</th>
                <th width="35%">Nama Barang / Transaksi</th>
                <th width="17%">Kategori</th>
                <th class="text-center" width="10%">Tipe</th>
                <th class="text-right" width="15%">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->transaction_date }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->category->name ?? '-' }}</td>
                <td class="text-center">{{ strtoupper($item->type) }}</td>
                <td class="text-right">{{ number_format($item->total_amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data transaksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>