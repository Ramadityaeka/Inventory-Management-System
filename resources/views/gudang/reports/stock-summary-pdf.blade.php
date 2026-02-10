<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Ringkasan Stok</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 9px;
            color: #666;
        }
        
        .filters {
            margin: 10px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }
        
        .filters strong {
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        
        table thead tr {
            background-color: #343a40;
            color: white;
        }
        
        table th,
        table td {
            border: 1px solid #dee2e6;
            padding: 5px 4px;
            text-align: left;
        }
        
        table th {
            font-weight: bold;
            font-size: 8px;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        table tbody tr:hover {
            background-color: #e9ecef;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-end {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #198754;
            color: white;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-info {
            background-color: #0dcaf0;
            color: #000;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 8px;
        }
        
        .summary {
            margin: 15px 0;
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        
        .summary-item h6 {
            margin: 0 0 5px 0;
            font-size: 9px;
            color: #666;
        }
        
        .summary-item h3 {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        
        tfoot {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
        }
        
        .keterangan {
            margin-top: 20px;
            font-size: 9px;
        }
        
        .keterangan p {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN RINGKASAN STOK MASUK & KELUAR</h2>
        <p>Data Ringkasan Pergerakan Stok Barang</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if(!empty($filters) && (isset($filters['category_id']) || isset($filters['year']) || isset($filters['month'])))
    <div class="filters">
        <strong>Filter Diterapkan:</strong>
        @if(isset($filters['category_id']) && !empty($filters['category_id']))
            Kategori: {{ \App\Models\Category::find($filters['category_id'])->name ?? '-' }};
        @endif
        @if(isset($filters['item_name']) && !empty($filters['item_name']))
            Nama Barang: {{ $filters['item_name'] }};
        @endif
        @if(isset($filters['item_code']) && !empty($filters['item_code']))
            Kode Barang: {{ $filters['item_code'] }};
        @endif
        @if(isset($filters['year']) && !empty($filters['year']))
            Tahun: {{ $filters['year'] }};
        @endif
        @if(isset($filters['month']) && !empty($filters['month']))
            Bulan: {{ \Carbon\Carbon::create()->month($filters['month'])->locale('id')->translatedFormat('F') }};
        @endif
    </div>
    @endif

    <div class="summary">
        <div class="summary-item">
            <h6>Total Item</h6>
            <h3>{{ number_format($totals['total_items']) }}</h3>
        </div>
        <div class="summary-item">
            <h6>Total Masuk</h6>
            <h3>{{ number_format($totals['total_stock_in']) }}</h3>
        </div>
        <div class="summary-item">
            <h6>Total Keluar</h6>
            <h3>{{ number_format($totals['total_stock_out']) }}</h3>
        </div>
        <div class="summary-item">
            <h6>Sisa Stok</h6>
            <h3>{{ number_format($totals['total_current_stock']) }}</h3>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%" class="text-center">NO</th>
                <th width="10%">UNIT</th>
                <th width="20%">NAMA BARANG</th>
                <th width="12%">KATEGORI</th>
                <th width="8%">SATUAN</th>
                <th width="8%" class="text-right">MASUK</th>
                <th width="8%">SATUAN</th>
                <th width="8%" class="text-right">KELUAR</th>
                <th width="8%">SATUAN</th>
                <th width="8%" class="text-right">SISA STOK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summaryData as $index => $data)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><span class="badge badge-info">{{ $data['warehouse_name'] }}</span></td>
                    <td>
                        <strong>{{ $data['name'] }}</strong><br>
                        <span class="text-muted">{{ $data['code'] }}</span>
                    </td>
                    <td>{{ $data['category'] }}</td>
                    <td>{{ $data['unit'] }}</td>
                    <td class="text-right">
                        <span class="badge badge-success">{{ number_format($data['stock_in'], 0, ',', '.') }}</span>
                    </td>
                    <td>{{ $data['unit'] }}</td>
                    <td class="text-right">
                        <span class="badge badge-danger">{{ number_format($data['stock_out'], 0, ',', '.') }}</span>
                    </td>
                    <td>{{ $data['unit'] }}</td>
                    <td class="text-right"><strong>{{ number_format($data['current_stock'], 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['total_stock_in'], 0, ',', '.') }}</strong></td>
                <td>-</td>
                <td class="text-right"><strong>{{ number_format($totals['total_stock_out'], 0, ',', '.') }}</strong></td>
                <td>-</td>
                <td class="text-right"><strong>{{ number_format($totals['total_current_stock'], 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="keterangan">
        <p><strong>Keterangan:</strong></p>
        <p>- Masuk: Total kuantitas barang yang masuk ke unit</p>
        <p>- Keluar: Total kuantitas barang yang keluar dari unit</p>
        <p>- Sisa Stok: Kuantitas barang yang tersisa saat ini</p>
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Manajemen Inventori</p>
        <p>&copy; {{ date('Y') }} - Laporan Ringkasan Stok</p>
    </div>
</body>
</html>
