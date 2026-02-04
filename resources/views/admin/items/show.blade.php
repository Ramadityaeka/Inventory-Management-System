@extends('layouts.app')

@section('page-title', 'Detail Item')

@section('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    .timeline-marker i {
        font-size: 14px;
    }

    .timeline-content {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid #dee2e6;
    }

    .text-purple {
        color: #6f42c1 !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Detail Item: {{ $item->name }}</h4>
            <div>
                <a href="{{ route('admin.items.edit', $item) }}" class="btn btn-primary me-2">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Item Information -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Item Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Code:</strong> <code>{{ $item->code }}</code></p>
                        <p><strong>Nama:</strong> {{ $item->name }}</p>
                        <p><strong>Kategori:</strong> {{ $item->category->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Supplier:</strong> {{ $item->supplier->name ?? '-' }}</p>
                        <p><strong>Satuan:</strong> {{ $item->unit }}</p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Total Stock:</strong> {{ number_format($totalStock) }}</p>
                        <p><strong>Status:</strong>
                            @if($item->is_active)
                                <span class="badge bg-success">Active</span>
                                @if($item->inactive_reason === 'seasonal')
                                    <span class="badge bg-info ms-1">Seasonal</span>
                                @endif
                            @else
                                @if($item->inactive_reason === 'discontinued')
                                    <span class="badge bg-danger">Discontinued</span>
                                @elseif($item->inactive_reason === 'wrong_input')
                                    <span class="badge bg-warning text-dark">Wrong Input</span>
                                @elseif($item->inactive_reason === 'seasonal')
                                    <span class="badge bg-info">Seasonal (Off)</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            @endif
                        </p>
                        <p><strong>Stock Status:</strong>
                            @if($totalStock == 0)
                                <span class="badge bg-danger">Habis</span>
                            @else
                                <span class="badge bg-success">Tersedia</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Created:</strong> {{ formatDateIndoLong($item->created_at) }} WIB</p>
                        <p><strong>Updated:</strong> {{ formatDateIndoLong($item->updated_at) }} WIB</p>
                    </div>
                </div>
                
                @if(!$item->is_active && $item->inactive_reason)
                    <div class="alert alert-warning mt-3 mb-0">
                        <h6 class="alert-heading">Informasi Tidak aktif</h6>
                        <p class="mb-1"><strong>Alasannya:</strong> 
                            @if($item->inactive_reason === 'discontinued')
                                Barang Tidak Diproduksi Lagi
                            @elseif($item->inactive_reason === 'wrong_input')
                                Salah Input
                                @if($item->replacementItem)
                                    <br><small>→ Digantikan oleh: <a href="{{ route('admin.items.show', $item->replacementItem) }}">{{ $item->replacementItem->code }} - {{ $item->replacementItem->name }}</a></small>
                                @endif
                            @elseif($item->inactive_reason === 'seasonal')
                                Barang Musiman
                            @endif
                        </p>
                        @if($item->inactive_notes)
                            <p class="mb-1"><strong>Catatan:</strong> {{ $item->inactive_notes }}</p>
                        @endif
                        @if($item->deactivated_at)
                            <p class="mb-0"><small><strong>Deactivated:</strong> {{ formatDateIndoLong($item->deactivated_at) }} WIB
                            @if($item->deactivatedBy)
                                by {{ $item->deactivatedBy->name }}
                            @endif
                            </small></p>
                        @endif
                    </div>
                @endif
                
                @if($item->description)
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <p class="mt-1">{{ $item->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Stock per Gudang -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Stock per Unit</h6>
            </div>
            <div class="card-body">
                @if($item->stocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Unit</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->stocks as $stock)
                                    <tr>
                                        <td>{{ $stock->warehouse->name ?? 'Unknown' }}</td>
                                        <td>{{ number_format($stock->quantity) }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>
                                            @if($stock->quantity == 0)
                                                <span class="badge bg-danger">Habis</span>
                                            @else
                                                <span class="badge bg-success">Tersedia</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Total Row -->
                                <tr class="table-primary fw-bold">
                                    <td><strong>Total</strong></td>
                                    <td><strong>{{ number_format($totalStock) }}</strong></td>
                                    <td><strong>{{ $item->unit }}</strong></td>
                                    <td>
                                        @if($totalStock == 0)
                                            <span class="badge bg-danger">Habis</span>
                                        @else
                                            <span class="badge bg-success">Tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-building text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Item belum memiliki stock di unit manapun.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Purchases (from Submissions) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Riwayat Pembelian (10 Terakhir)</h6>
            </div>
            <div class="card-body">
                @php
                    $recentPurchases = $item->submissions()
                        ->where('status', 'approved')
                        ->whereNotNull('unit_price')
                        ->with(['warehouse', 'supplier', 'staff'])
                        ->orderBy('created_at', 'desc')
                        ->take(10)
                        ->get();
                @endphp
                
                @if($recentPurchases->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Unit</th>
                                    <th>Supplier</th>
                                    <th>Jumlah Stok</th>
                                    <th>Harga/Satuan</th>
                                    <th>Total Harga</th>
                                    <th>Staff</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPurchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $purchase->warehouse->name }}</td>
                                        <td>{{ $purchase->supplier->name ?? '-' }}</td>
                                        <td>{{ number_format($purchase->quantity) }} {{ $purchase->unit }}</td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-25 text-info">
                                                Rp {{ number_format($purchase->unit_price, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-25 text-success fw-bold">
                                                Rp {{ number_format($purchase->total_price, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td><small>{{ $purchase->staff->name ?? '-' }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-receipt text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Belum ada riwayat pembelian dengan informasi harga.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Movements -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Riwayat perpindahan barang</h6>
            </div>
            <div class="card-body">
                @if($item->stockMovements->count() > 0)
                    <div class="timeline">
                        @foreach($item->stockMovements as $movement)
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    @switch($movement->movement_type)
                                        @case('in')
                                            <i class="bi bi-arrow-up-circle-fill text-success"></i>
                                            @break
                                        @case('out')
                                            <i class="bi bi-arrow-down-circle-fill text-danger"></i>
                                            @break
                                        @case('adjustment')
                                            <i class="bi bi-gear-fill text-purple"></i>
                                            @break
                                        @default
                                            <i class="bi bi-circle-fill text-secondary"></i>
                                    @endswitch
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                @switch($movement->movement_type)
                                                    @case('in')
                                                        Barang Masuk
                                                        @break
                                                    @case('out')
                                                        Barang Keluar
                                                        @break
                                                    @case('adjustment')
                                                        Penyesuaian
                                                        @break
                                                    @default
                                                        {{ ucfirst($movement->movement_type) }}
                                                @endswitch
                                            </h6>
                                            <p class="text-muted mb-1">{{ $movement->warehouse->name ?? 'Satuan Tidak Diketahui' }}</p>
                                            @if($movement->notes)
                                                <p class="text-muted small mb-0">{{ $movement->notes }}</p>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold {{ $movement->movement_type === 'out' ? 'text-danger' : 'text-success' }}">
                                                {{ $movement->movement_type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity) }}
                                            </div>
                                            <small class="text-muted">{{ formatDateIndo($movement->created_at) }} WIB</small>
                                            <br>
                                            <small class="text-muted">{{ $movement->creator->name ?? 'System' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Belum ada riwayat pergerakan stock.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for Chart.js
    const stockData = @json($item->getStockMovementData(30));

    if (stockData.datasets.length > 0) {
        const ctx = document.getElementById('stockMovementChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: stockData.labels,
                datasets: stockData.datasets.map(dataset => ({
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: dataset.borderColor,
                    backgroundColor: dataset.backgroundColor,
                    tension: 0.1,
                    fill: false
                }))
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Stock Movement Over Time'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    } else {
        // Show message when no data available
        const chartContainer = document.getElementById('stockMovementChart').parentElement;
        chartContainer.innerHTML = '<div class="text-center py-4"><i class="bi bi-graph-up text-muted fs-1 mb-3"></i><p class="text-muted mb-0">Belum ada data pergerakan stock untuk ditampilkan.</p></div>';
    }
});
</script>
@endsection