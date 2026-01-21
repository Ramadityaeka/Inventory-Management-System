@extends('layouts.pdf')

@section('content')
<div class="pdf-header">
    <div class="row">
        <div class="col-6">
            <h2>Stock Overview Report</h2>
            <p class="mb-1"><strong>Generated:</strong> {{ now()->format('d M Y H:i') }}</p>
            @if($warehouse)
                <p class="mb-1"><strong>Warehouse:</strong> {{ $warehouse->name }}</p>
            @else
                <p class="mb-1"><strong>All Warehouses</strong></p>
            @endif
        </div>
        <div class="col-6 text-end">
            <h4 class="mb-0">Inventory System</h4>
            <p class="mb-0">Stock Management Report</p>
        </div>
    </div>
</div>

<div class="pdf-content">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="summary-card">
                <div class="summary-value">{{ number_format($totalItems) }}</div>
                <div class="summary-label">Total Items</div>
            </div>
        </div>
        <div class="col-3">
            <div class="summary-card">
                <div class="summary-value">{{ number_format($totalStock) }}</div>
                <div class="summary-label">Total Stock</div>
            </div>
        </div>
        <div class="col-3">
            <div class="summary-card">
                <div class="summary-value">{{ number_format($lowStockItems) }}</div>
                <div class="summary-label">Low Stock Items</div>
            </div>
        </div>
        <div class="col-3">
            <div class="summary-card">
                <div class="summary-value">{{ number_format($outOfStockItems) }}</div>
                <div class="summary-label">Out of Stock</div>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="table-responsive">
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="width: 8%">Item Code</th>
                    <th style="width: 20%">Item Name</th>
                    <th style="width: 12%">Category</th>
                    <th style="width: 12%">Warehouse</th>
                    <th style="width: 8%" class="text-center">Current Stock</th>
                    <th style="width: 12%" class="text-center">Harga Terakhir</th>
                    <th style="width: 8%" class="text-center">Min Stock</th>
                    <th style="width: 12%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stocks as $stock)
                    @php
                        $lastPurchase = \App\Models\Submission::where('item_id', $stock->item_id)
                            ->where('status', 'approved')
                            ->whereNotNull('unit_price')
                            ->orderBy('created_at', 'desc')
                            ->first();
                    @endphp
                    <tr>
                        <td><code>{{ $stock->item->code }}</code></td>
                        <td>{{ $stock->item->name }}</td>
                        <td>{{ $stock->item->category->name ?? 'N/A' }}</td>
                        <td>{{ $stock->warehouse->name }}</td>
                        <td class="text-center">{{ number_format($stock->quantity) }}</td>
                        <td class="text-center">
                            @if($lastPurchase)
                                Rp {{ number_format($lastPurchase->unit_price, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($stock->min_stock) }}</td>
                        <td class="text-center">
                            @if($stock->quantity <= 0)
                                <span class="status-badge status-danger">Out of Stock</span>
                            @elseif($stock->quantity <= $stock->min_stock)
                                <span class="status-badge status-warning">Low Stock</span>
                            @else
                                <span class="status-badge status-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="pdf-footer">
        <div class="row">
            <div class="col-6">
                <p class="mb-0"><strong>Report Period:</strong> All Time</p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-0"><strong>Page:</strong> {PAGENO} of {nbpg}</p>
            </div>
        </div>
    </div>
</div>
@endsection