<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 16px;
            margin-bottom: 5px;
        }
        h2 {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .header-info {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .header-info p {
            margin: 3px 0;
            font-size: 10px;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .summary-card h3 {
            margin: 0 0 3px 0;
            font-size: 18px;
        }
        .summary-card p {
            margin: 0;
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #e9ecef;
            padding: 5px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
            font-size: 9px;
        }
        table td {
            padding: 4px 5px;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        .badge-info {
            background-color: #cff4fc;
            color: #055160;
        }
        .footer-info {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <h1>LAPORAN TRANSAKSI BARANG MASUK & KELUAR</h1>
    <h2>
        @if(isset($filters['year']) || isset($filters['month']))
            @if(isset($filters['month']) && isset($filters['year']))
                {{ \Carbon\Carbon::create($filters['year'], $filters['month'])->locale('id')->translatedFormat('F Y') }}
            @elseif(isset($filters['year']))
                Tahun {{ $filters['year'] }}
            @endif
        @else
            Semua Periode
        @endif
    </h2>

    <div class="header-info">
        <p><strong>Tanggal Cetak:</strong> {{ formatDateIndoLong(now()) }} WIB</p>
        <p><strong>Dicetak oleh:</strong> {{ auth()->user()->name }}</p>
        <p><strong>Gudang:</strong> {{ $warehouses->pluck('name')->implode(', ') }}</p>
        @if(isset($filters['category_id']))
            <p><strong>Kategori:</strong> {{ \App\Models\Category::find($filters['category_id'])->name }}</p>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3 style="color: #198754;">{{ number_format($stats['total_transactions']) }}</h3>
            <p>Total Transaksi</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #0d6efd;">{{ number_format($stats['approved_count']) }}</h3>
            <p>Disetujui</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #ffc107;">{{ number_format($stats['pending_count']) }}</h3>
            <p>Menunggu</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #dc3545;">{{ number_format($stats['rejected_count']) }}</h3>
            <p>Ditolak</p>
        </div>
    </div>

    <!-- Transactions Table -->
    @if($transactions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="10%">Gudang</th>
                    <th width="17%">Nama Barang</th>
                    <th width="7%">Jumlah</th>
                    <th width="5%">Satuan</th>
                    <th width="7%">Sisa Stok</th>
                    <th width="20%">Keterangan</th>
                    <th width="8%">Status</th>
                    <th width="13%">Diproses Oleh</th>
                    <th width="10%">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $index => $transaction)
                    @php
                        $currentStock = \App\Models\Stock::where('warehouse_id', $transaction->warehouse_id)
                            ->where('item_id', $transaction->item_id)
                            ->first();
                        $remainingStock = $currentStock ? $currentStock->quantity : 0;
                        $approval = $transaction->approvals->first();
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><span class="badge badge-info">{{ $transaction->warehouse->name }}</span></td>
                        <td>
                            <strong>{{ $transaction->item->name }}</strong><br>
                            <small style="color: #666;">{{ $transaction->item->code }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">{{ number_format($transaction->quantity) }}</span>
                        </td>
                        <td class="text-center">{{ $transaction->item->unit }}</td>
                        <td class="text-center"><strong>{{ number_format($remainingStock) }}</strong></td>
                        <td>
                            @if($transaction->notes)
                                {{ strlen($transaction->notes) > 60 ? substr($transaction->notes, 0, 60) . '...' : $transaction->notes }}
                            @else
                                <small>Penerimaan dari {{ $transaction->supplier->name ?? '-' }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($transaction->status == 'approved')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($transaction->status == 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                            @else
                                <span class="badge badge-warning">Menunggu</span>
                            @endif
                        </td>
                        <td>
                            @if($approval)
                                <strong>{{ $approval->admin->name }}</strong>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            {{ formatDateIndo($transaction->submitted_at, 'd/m/Y H:i') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #666;">Tidak ada data transaksi</p>
    @endif

    <!-- Footer -->
    <div class="footer-info">
        <p><strong>Catatan:</strong> Laporan ini dibuat secara otomatis oleh sistem.</p>
    </div>
</body>
</html>
