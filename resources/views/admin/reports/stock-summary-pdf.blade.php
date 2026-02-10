<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Ringkasan Stok</title>
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
            font-size: 16px;
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
    <h1>LAPORAN RINGKASAN STOK MASUK & KELUAR</h1>
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
        <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }} WIB</p>
        <p><strong>Dicetak oleh:</strong> {{ auth()->user()->name }}</p>
        @if(isset($filters['warehouse_id']))
            @php
                $warehouse = \App\Models\Warehouse::find($filters['warehouse_id']);
            @endphp
            @if($warehouse)
                <p><strong>Gudang:</strong> {{ $warehouse->name }}</p>
            @endif
        @endif
        @if(isset($filters['category_id']))
            @php
                $category = \App\Models\Category::find($filters['category_id']);
            @endphp
            @if($category)
                <p><strong>Kategori:</strong> {{ $category->name }}</p>
            @endif
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3 style="color: #0dcaf0;">{{ number_format($totals['total_items']) }}</h3>
            <p>Total Jenis Barang</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #198754;">{{ number_format($totals['total_stock_in']) }}</h3>
            <p>Total Barang Masuk</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #dc3545;">{{ number_format($totals['total_stock_out']) }}</h3>
            <p>Total Barang Keluar</p>
        </div>
        <div class="summary-card">
            <h3 style="color: #ffc107;">{{ number_format($totals['total_current_stock']) }}</h3>
            <p>Total Sisa Stok</p>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">NO</th>
                <th style="width: 80px;">UNIT</th>
                <th style="width: 70px;">KODE</th>
                <th>NAMA BARANG</th>
                <th style="width: 80px;">KATEGORI</th>
                <th class="text-center" style="width: 50px;">SATUAN</th>
                <th class="text-right" style="width: 50px;">MASUK</th>
                <th class="text-center" style="width: 50px;">SATUAN</th>
                <th class="text-right" style="width: 50px;">KELUAR</th>
                <th class="text-center" style="width: 50px;">SATUAN</th>
                <th class="text-right" style="width: 50px;">SISA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summaryData as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['warehouse_name'] }}</td>
                    <td>{{ $item['code'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['category'] }}</td>
                    <td class="text-center">{{ $item['unit'] }}</td>
                    <td class="text-right">{{ number_format($item['stock_in']) }}</td>
                    <td class="text-center">{{ $item['unit'] }}</td>
                    <td class="text-right">{{ number_format($item['stock_out']) }}</td>
                    <td class="text-center">{{ $item['unit'] }}</td>
                    <td class="text-right">{{ number_format($item['current_stock']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data untuk ditampilkan</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['total_stock_in']) }}</strong></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($totals['total_stock_out']) }}</strong></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($totals['total_current_stock']) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-info">
        <p>
            <strong>Keterangan:</strong><br>
            - Data ini menampilkan ringkasan stok masuk dan keluar per barang<br>
            - Sisa stok adalah jumlah stok saat ini yang tersedia di gudang
        </p>
    </div>
</body>
</html>
