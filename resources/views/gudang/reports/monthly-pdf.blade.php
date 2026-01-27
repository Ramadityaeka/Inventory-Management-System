<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Report - {{ $reportData['warehouse']->name }} - {{ $reportData['period'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 20px;
            margin-bottom: 5px;
        }
        h2 {
            text-align: center;
            color: #666;
            font-size: 16px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .header-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .header-info p {
            margin: 5px 0;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .summary-card h3 {
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .summary-card p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        .section-title {
            background-color: #0d6efd;
            color: white;
            padding: 8px 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #e9ecef;
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
            font-size: 11px;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            font-size: 11px;
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
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #664d03;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #41464b;
        }
        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stats-col {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stats-col h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        .stats-col p {
            margin: 0;
            font-size: 10px;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>MONTHLY WAREHOUSE REPORT</h1>
    <h2>{{ $reportData['warehouse']->name }}</h2>
    
    <div class="header-info">
        <p><strong>Report Period:</strong> {{ $reportData['period'] }}</p>
        <p><strong>Warehouse Location:</strong> {{ $reportData['warehouse']->location }}</p>
        <p><strong>Generated on:</strong> {{ formatDateIndoLong(now()) }} WIB</p>
    </div>

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
            <h3 style="color: #0dcaf0;">{{ number_format($reportData['submissions_count']) }}</h3>
            <p>Submissions</p>
        </div>
    </div>

    <div class="section-title">Pergerakan Stok per Barang</div>
    @if($reportData['item_movements']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th class="text-right">Barang Masuk</th>
                    <th class="text-right">Barang Keluar</th>
                    <th class="text-right">Penyesuaian</th>
                    <th class="text-right">Stok Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['item_movements'] as $movement)
                    <tr>
                        <td>{{ $movement['item']->code }}</td>
                        <td>{{ $movement['item']->name }}</td>
                        <td class="text-right">
                            @if($movement['stock_in'] > 0)
                                +{{ number_format($movement['stock_in']) }}
                                <br><small>({{ $movement['in_movements'] }} trx)</small>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            @if($movement['stock_out'] > 0)
                                -{{ number_format($movement['stock_out']) }}
                                <br><small>({{ $movement['out_movements'] }} trx)</small>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            @if($movement['adjustments'] != 0)
                                {{ $movement['adjustments'] > 0 ? '+' : '' }}{{ number_format($movement['adjustments']) }}
                                <br><small>({{ $movement['adjustment_movements'] }} adj)</small>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            <strong>{{ number_format($movement['current_stock']) }} {{ $movement['unit'] }}</strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">No stock movements this month</div>
    @endif

    <div class="section-title">Submissions Summary</div>
    <div class="stats-row">
        <div class="stats-col">
            <h4 style="color: #ffc107;">{{ $reportData['submissions_pending'] }}</h4>
            <p>Pending</p>
        </div>
        <div class="stats-col">
            <h4 style="color: #198754;">{{ $reportData['submissions_approved'] }}</h4>
            <p>Approved</p>
        </div>
        <div class="stats-col">
            <h4 style="color: #dc3545;">{{ $reportData['submissions_rejected'] }}</h4>
            <p>Rejected</p>
        </div>
    </div>

    <div class="section-title">Transfer Summary</div>
    <div class="stats-row">
        <div class="stats-col">
            <h4 style="color: #dc3545;">{{ $reportData['transfers_out'] }}</h4>
            <p>Transfers Out</p>
        </div>
        <div class="stats-col">
            <h4 style="color: #198754;">{{ $reportData['transfers_in'] }}</h4>
            <p>Transfers In</p>
        </div>
        <div class="stats-col">
            <h4>{{ $reportData['transfers_out'] + $reportData['transfers_in'] }}</h4>
            <p>Total Transfers</p>
        </div>
    </div>

    <div class="section-title">Current Stock Status</div>
    <div class="stats-row">
        <div class="stats-col">
            <h4 style="color: #0dcaf0;">{{ $reportData['current_stocks']->count() }}</h4>
            <p>Total Items</p>
        </div>
        <div class="stats-col">
            <h4 style="color: #ffc107;">{{ $reportData['low_stock_items'] }}</h4>
            <p>Low Stock Items</p>
        </div>
        <div class="stats-col">
            <h4 style="color: #dc3545;">{{ $reportData['out_of_stock_items'] }}</h4>
            <p>Out of Stock Items</p>
        </div>
    </div>

    @if($reportData['current_stocks']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th class="text-right">Current Stock</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['current_stocks'] as $stock)
                    <tr>
                        <td>{{ $stock->item->code }}</td>
                        <td>{{ $stock->item->name }}</td>
                        <td class="text-right"><strong>{{ number_format($stock->quantity) }}</strong></td>
                        <td class="text-center">
                            @if($stock->quantity == 0)
                                <span class="badge badge-danger">Habis</span>
                            @else
                                <span class="badge badge-success">Tersedia</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>This is a system-generated report from Inventory System ESDM</p>
    </div>
</body>
</html>
