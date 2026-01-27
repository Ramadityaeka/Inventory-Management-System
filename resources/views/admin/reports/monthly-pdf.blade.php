<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan - {{ $reportData['warehouse_count'] > 1 ? $reportData['warehouse_count'] . ' Gudang' : $reportData['warehouses'] }} - {{ $reportData['period'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 18px;
            margin-bottom: 5px;
        }
        h2 {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .header-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .header-info p {
            margin: 4px 0;
            font-size: 11px;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .summary-card h3 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }
        .summary-card p {
            margin: 0;
            font-size: 10px;
            color: #666;
        }
        .section-title {
            background-color: #0d6efd;
            color: white;
            padding: 7px 10px;
            margin-top: 15px;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: bold;
        }
        .section-title.green {
            background-color: #198754;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #e9ecef;
            padding: 6px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
            font-size: 10px;
        }
        table td {
            padding: 5px 6px;
            border: 1px solid #dee2e6;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        table tfoot td {
            font-weight: bold;
            background-color: #e9ecef;
            padding: 8px 6px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
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
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer-info {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
        }
        .summary-box {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .summary-box-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .summary-box-item h4 {
            margin: 0 0 3px 0;
            font-size: 16px;
            color: #0d6efd;
        }
        .summary-box-item p {
            margin: 0;
            font-size: 9px;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <h1>LAPORAN BULANAN SEMUA GUDANG</h1>
    <h2>{{ $reportData['period'] }}</h2>

    <div class="header-info">
        <p><strong>Gudang:</strong> 
            @if($reportData['warehouse_count'] > 1)
                {{ $reportData['warehouse_count'] }} Gudang Terpilih ({{ $reportData['warehouses'] }})
            @else
                {{ $reportData['warehouses'] }}
            @endif
        </p>
        <p><strong>Tanggal Cetak:</strong> {{ formatDateIndoLong(now()) }} WIB</p>
        <p><strong>Dicetak oleh:</strong> {{ auth()->user()->name }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3 style="color: #198754;">{{ number_format($reportData['total_stock_in']) }}</h3>
            <p>Total Barang Masuk</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #dc3545;">{{ number_format($reportData['total_stock_out']) }}</h3>
            <p>Total Barang Keluar</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #0d6efd;">{{ number_format($reportData['total_movements']) }}</h3>
            <p>Total Pergerakan</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #0dcaf0;">Rp {{ number_format($reportData['total_purchase_value'] ?? 0, 0, ',', '.') }}</h3>
            <p>Nilai Pembelian</p>
        </div>
    </div>

    <!-- TABLE 1: TRANSACTIONS -->
    <div class="section-title">TABEL 1: TRANSAKSI BARANG MASUK & KELUAR</div>
    
    @if($reportData['transactions']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="10%">Gudang</th>
                    <th width="15%">Nama Barang</th>
                    <th width="7%" class="text-center">Jumlah</th>
                    <th width="7%" class="text-center">Sisa</th>
                    <th width="17%">Keterangan</th>
                    <th width="8%" class="text-center">Status</th>
                    <th width="12%">Diproses Oleh</th>
                    <th width="10%" class="text-center">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['transactions'] as $index => $transaction)
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
                            <span class="badge badge-success">+{{ number_format($transaction->quantity) }}</span><br>
                            <small>{{ $transaction->item->unit }}</small>
                        </td>
                        <td class="text-center">
                            <strong>{{ number_format($remainingStock) }}</strong><br>
                            <small>{{ $transaction->item->unit }}</small>
                        </td>
                        <td>
                            @if($transaction->notes)
                                {{ strlen($transaction->notes) > 50 ? substr($transaction->notes, 0, 50) . '...' : $transaction->notes }}
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
                                <strong>{{ $approval->admin->name }}</strong><br>
                                <small>Admin</small>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $transaction->created_at->format('d/m/Y') }}<br>
                            <small>{{ $transaction->created_at->format('H:i') }}</small>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Transaction Summary -->
        <div class="summary-box">
            <div class="summary-box-item">
                <h4 style="color: #198754;">{{ $reportData['submissions_approved'] }}</h4>
                <p>Transaksi Disetujui</p>
            </div>
            <div class="summary-box-item">
                <h4 style="color: #ffc107;">{{ $reportData['submissions_pending'] }}</h4>
                <p>Transaksi Menunggu</p>
            </div>
            <div class="summary-box-item">
                <h4 style="color: #dc3545;">{{ $reportData['submissions_rejected'] }}</h4>
                <p>Transaksi Ditolak</p>
            </div>
        </div>
    @else
        <p style="text-align: center; padding: 20px; color: #666;">Tidak ada transaksi bulan ini</p>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- TABLE 2: STOCK WITH VALUES -->
    <div class="section-title green">TABEL 2: DAFTAR STOK BARANG & NILAI</div>
    
    @if($reportData['stocks_with_values']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="12%">Gudang</th>
                    <th width="9%">Kode</th>
                    <th width="18%">Nama Barang</th>
                    <th width="10%">Kategori</th>
                    <th width="7%" class="text-center">Jumlah</th>
                    <th width="6%" class="text-center">Satuan</th>
                    <th width="13%" class="text-right">Harga/Satuan</th>
                    <th width="15%" class="text-right">Harga Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['stocks_with_values'] as $index => $stockData)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><span class="badge badge-info">{{ $stockData['warehouse']->name }}</span></td>
                        <td><code>{{ $stockData['item']->code }}</code></td>
                        <td>
                            <strong>{{ $stockData['item']->name }}</strong>
                            @if($stockData['quantity'] <= 0)
                                <br><span class="badge badge-danger">Habis</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $stockData['item']->category->name }}</span>
                        </td>
                        <td class="text-center"><strong>{{ number_format($stockData['quantity']) }}</strong></td>
                        <td class="text-center">{{ $stockData['item']->unit }}</td>
                        <td class="text-right">
                            @if($stockData['unit_price'] > 0)
                                Rp {{ number_format($stockData['unit_price'], 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            @if($stockData['total_value'] > 0)
                                <strong>Rp {{ number_format($stockData['total_value'], 0, ',', '.') }}</strong>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
                    <td class="text-center">{{ number_format($reportData['stocks_with_values']->sum('quantity')) }}</td>
                    <td class="text-center">item</td>
                    <td></td>
                    <td class="text-right" style="font-size: 12px;">Rp {{ number_format($reportData['total_stock_value'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Stock Value Summary -->
        <div class="summary-box">
            <div class="summary-box-item">
                <h4 style="color: #0d6efd;">{{ number_format($reportData['stocks_with_values']->count()) }}</h4>
                <p>Total Jenis Barang</p>
            </div>
            <div class="summary-box-item">
                <h4 style="color: #0dcaf0;">{{ number_format($reportData['stocks_with_values']->sum('quantity')) }}</h4>
                <p>Total Jumlah Barang</p>
            </div>
            <div class="summary-box-item">
                <h4 style="color: #198754;">Rp {{ number_format($reportData['total_stock_value'], 0, ',', '.') }}</h4>
                <p>Total Nilai Keseluruhan</p>
            </div>
        </div>
    @else
        <p style="text-align: center; padding: 20px; color: #666;">Tidak ada stok barang</p>
    @endif

    <!-- Footer -->
    <div class="footer-info">
        <p>
            <strong>Catatan:</strong> Laporan ini dibuat secara otomatis oleh sistem.<br>
            Harga satuan dan total nilai diambil dari data submission terakhir yang disetujui untuk setiap barang.
        </p>
    </div>
</body>
</html>
