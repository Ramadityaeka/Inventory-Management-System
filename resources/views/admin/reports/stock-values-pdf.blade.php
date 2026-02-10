<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok & Nilai Barang</title>
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
            width: 33.33%;
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
        table tfoot td {
            font-weight: bold;
            background-color: #e9ecef;
            padding: 6px 5px;
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
        .badge-info {
            background-color: #cff4fc;
            color: #055160;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        .footer-info {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #666;
        }
        code {
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <h1>LAPORAN DAFTAR STOK BARANG & NILAI</h1>
    <h2>Per Tanggal: {{ formatDateIndo(now(), 'd F Y') }}</h2>

    <div class="header-info">
        <p><strong>Tanggal Cetak:</strong> {{ formatDateIndoLong(now()) }} WIB</p>
        <p><strong>Dicetak oleh:</strong> {{ auth()->user()->name }}</p>
        @if(isset($filters['warehouse_id']))
            <p><strong>Gudang:</strong> {{ \App\Models\Warehouse::find($filters['warehouse_id'])->name }}</p>
        @endif
        @if(isset($filters['category_id']))
            <p><strong>Kategori:</strong> {{ \App\Models\Category::find($filters['category_id'])->name }}</p>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3 style="color: #0d6efd;">{{ number_format($stats['total_items']) }}</h3>
            <p>Total Jenis Barang</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #0dcaf0;">{{ number_format($stats['total_quantity']) }}</h3>
            <p>Total Stok Barang</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #198754;">Rp {{ number_format($stats['total_stock_value'], 0, ',', '.') }}</h3>
            <p>Total Nilai Keseluruhan</p>
        </div>
    </div>

    <!-- Stock Values Table -->
    @if($stocksData->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="11%">Gudang</th>
                    <th width="9%">Kode</th>
                    <th width="18%">Nama Barang</th>
                    <th width="10%">Kategori</th>
                    <th width="7%">Stok</th>
                    <th width="6%">Satuan</th>
                    <th width="15%">Harga/Satuan</th>
                    <th width="15%">Harga Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stocksData as $index => $data)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><span class="badge badge-info">{{ $data['warehouse']->name }}</span></td>
                        <td><code>{{ $data['item']->code }}</code></td>
                        <td>
                            <strong>{{ $data['item']->name }}</strong>
                            @if($data['quantity'] <= 0)
                                <br><span class="badge badge-danger">Habis</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $data['item']->category->name }}</span>
                        </td>
                        <td class="text-center"><strong>{{ number_format($data['quantity']) }}</strong></td>
                        <td class="text-center">{{ $data['item']->unit }}</td>
                        <td class="text-right">
                            @if($data['unit_price'] > 0)
                                Rp {{ number_format($data['unit_price'], 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            @if($data['total_value'] > 0)
                                <strong>Rp {{ number_format($data['total_value'], 0, ',', '.') }}</strong>
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
                    <td class="text-center">{{ number_format($stats['total_quantity']) }}</td>
                    <td class="text-center">item</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($stats['total_stock_value'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #666;">Tidak ada data stok barang</p>
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
