@extends('layouts.app')

@section('page-title', 'Dashboard Super Admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Dashboard Super Admin</h4>
                <p class="text-muted mb-0">Ringkasan sistem inventory per {{ now()->format('d F Y') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-outline-primary">
                    <i class="bi bi-graph-up me-1"></i>Laporan
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-gear me-1"></i>Pengaturan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Important Reminders -->
@if(($stats['pending_transfers'] ?? 0) > 0 || ($lowStockItems->count() ?? 0) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0 me-3">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-2">Pengingat Penting</h6>
                    <div class="row g-3">
                        @if(($stats['pending_transfers'] ?? 0) > 0)
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-arrow-left-right text-warning me-2"></i>
                                <span>{{ $stats['pending_transfers'] }} transfer menunggu approval</span>
                                <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-sm btn-warning ms-auto">Tinjau</a>
                            </div>
                        </div>
                        @endif
                        @if(($lowStockItems->count() ?? 0) > 0)
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-circle text-danger me-2"></i>
                                <span>{{ $lowStockItems->count() }} item stok rendah di seluruh gudang</span>
                                <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-sm btn-danger ms-auto">Periksa</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Key Metrics Row -->
<div class="row mb-4 g-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card" onclick="window.location='{{ route('admin.warehouses.index') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-building fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['total_warehouses'] ?? $stats['total_units'] ?? 0) }}</h5>
                                <p class="text-muted small mb-0">Total Unit</p>
                                <small class="text-primary">{{ $stats['active_warehouses'] ?? $stats['active_units'] ?? 0 }} aktif</small>
                            </div>
                            <div class="text-primary">
                                <i class="bi bi-arrow-right fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card" onclick="window.location='{{ route('admin.items.index') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-box-seam fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['total_items']) }}</h5>
                                <p class="text-muted small mb-0">Total Barang</p>
                                <small class="text-success">Semua kategori</small>
                            </div>
                            <div class="text-success">
                                <i class="bi bi-arrow-right fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card" onclick="window.location='{{ route('admin.reports.stock-overview') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-boxes fs-4 text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['total_stock']) }}</h5>
                                <p class="text-muted small mb-0">Total Stok</p>
                                <small class="text-info">Semua gudang</small>
                            </div>
                            <div class="text-info">
                                <i class="bi bi-arrow-right fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card {{ $stats['pending_transfers'] > 0 ? 'border-warning' : '' }}" onclick="window.location='{{ route('admin.reports.stock-overview') }}'" style="{{ $stats['pending_transfers'] > 0 ? 'border-width: 2px !important;' : '' }}">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-clock-history fs-4 text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['pending_transfers']) }}</h5>
                                <p class="text-muted small mb-0">Transfer Menunggu</p>
                                @if($stats['pending_transfers'] > 0)
                                    <small class="text-warning fw-bold">Butuh persetujuan</small>
                                @else
                                    <small class="text-success">Semua clear</small>
                                @endif
                            </div>
                            @if($stats['pending_transfers'] > 0)
                                <span class="badge bg-warning rounded-pill fs-6">!</span>
                            @else
                                <div class="text-success">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Metrics Row -->
<div class="row mb-4 g-3">
    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm hover-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-purple bg-opacity-10 rounded-3 p-3" style="background-color: rgba(128, 0, 128, 0.1) !important;">
                            <i class="bi bi-people-fill fs-4" style="color: #800080;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['total_users'] ?? 0) }}</h5>
                                <p class="text-muted small mb-0">Total Pengguna</p>
                                <small style="color: #800080;">Semua role</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm hover-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-arrow-up-circle fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['today_total_stock_in']) }}</h5>
                                <p class="text-muted small mb-0">Stock In Hari Ini</p>
                                <small class="text-success">Masuk gudang</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm hover-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-arrow-down-circle fs-4 text-danger"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['today_total_stock_out']) }}</h5>
                                <p class="text-muted small mb-0">Stock Out Hari Ini</p>
                                <small class="text-danger">Keluar gudang</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="bi bi-lightning-charge text-warning me-2"></i>Aksi Cepat
                </h6>
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-building me-2"></i>
                            <span>Kelola Gudang</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('admin.items.index') }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-box-seam me-2"></i>
                            <span>Kelola Barang</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-arrow-left-right me-2"></i>
                            <span>Laporan Transfer</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-people me-2"></i>
                            <span>Kelola User</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Summary -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-calendar-day text-info me-2"></i>Ringkasan Hari Ini - {{ now()->format('l, d F Y') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-plus-circle fs-3 text-success"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_total_stock_in'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Total Stok Masuk</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-dash-circle fs-3 text-danger"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_total_stock_out'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Total Stok Keluar</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-check-circle fs-3 text-primary"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_transfers_approved'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Transfer Disetujui</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-clock-history fs-3 text-warning"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_new_alerts'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Alert Baru</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Progress -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart-line text-success me-2"></i>Progress Bulan Ini
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Target Transfer Bulanan</span>
                                <span class="text-muted small">{{ $stats['monthly_transfers_current'] ?? 0 }} / {{ $stats['monthly_transfers_target'] ?? 50 }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, (($stats['monthly_transfers_current'] ?? 0) / max(1, $stats['monthly_transfers_target'] ?? 50)) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Target Stock Movement</span>
                                <span class="text-muted small">{{ $stats['monthly_movements_current'] ?? 0 }} / {{ $stats['monthly_movements_target'] ?? 1000 }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, (($stats['monthly_movements_current'] ?? 0) / max(1, $stats['monthly_movements_target'] ?? 1000)) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Warehouse Status Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-building text-primary me-2"></i>Status Gudang
                    </h6>
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-gear me-1"></i>Kelola
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($warehouses as $warehouse)
                        @php
                            $totalStock = $warehouse->stocks->sum('quantity');
                            $outOfStockCount = $warehouse->stocks->filter(function($stock) {
                                return $stock->quantity <= 0;
                            })->count();
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border {{ $outOfStockCount > 0 ? 'border-danger' : 'border-success' }} h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $warehouse->name }}</h6>
                                            <small class="text-muted">{{ $warehouse->location ?? 'N/A' }}</small>
                                        </div>
                                        @if($outOfStockCount > 0)
                                            <span class="badge bg-danger">{{ $outOfStockCount }} habis</span>
                                        @else
                                            <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                        @endif
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small">Total Items</span>
                                            <span class="fw-bold">{{ $warehouse->stocks_count }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small">Total Stock</span>
                                            <span class="fw-bold text-info">{{ number_format($totalStock) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="bi bi-building text-muted fs-1 mb-3"></i>
                                <p class="text-muted mb-0">Belum ada gudang</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics Row -->
<div class="row mb-4 g-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up text-primary me-2"></i>Pergerakan Stok 6 Bulan Terakhir
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-filter me-1"></i>Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Semua Gudang</a></li>
                            <li><a class="dropdown-item" href="#">Gudang A</a></li>
                            <li><a class="dropdown-item" href="#">Gudang B</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-trophy text-warning me-2"></i>Top 10 Items Terbanyak
                </h6>
            </div>
            <div class="card-body">
                @if($topItems->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topItems->take(10) as $index => $item)
                            <div class="list-group-item px-0 py-3 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index % 5] }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold text-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index % 5] }}">{{ $index + 1 }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $item->item_name }}</h6>
                                                <small class="text-muted">{{ $item->category_name }}</small>
                                            </div>
                                            <span class="badge bg-primary fs-6">{{ number_format($item->total_stock) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-bar-chart text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Belum ada data item</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Alerts and Pending Items Row -->
<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Persetujuan Menunggu
                </h6>
                @if($pendingTransfers->count() > 0)
                    <span class="badge bg-warning">{{ $pendingTransfers->count() }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($pendingTransfers->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($pendingTransfers->take(5) as $transfer)
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-arrow-left-right text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $transfer->transfer_number }}</h6>
                                                <small class="text-muted">{{ $transfer->fromWarehouse->name }} → {{ $transfer->toWarehouse->name }}</small>
                                                <div class="mt-1">
                                                    <small class="text-muted">{{ $transfer->quantity }} {{ $transfer->item->unit }}</small>
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-primary">
                            <i class="bi bi-list me-1"></i>Lihat Semua
                        </a>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle text-success fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Tidak ada pending approval</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-circle text-danger me-2"></i>Low Stock Alerts
                </h6>
                @if($lowStockItems->count() > 0)
                    <span class="badge bg-danger">{{ $lowStockItems->count() }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($lowStockItems->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($lowStockItems->take(5) as $item)
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-exclamation-triangle text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $item->item_name }}</h6>
                                                <small class="text-muted">{{ $item->warehouse_name }}</small>
                                                <div class="mt-1">
                                                    <span class="badge bg-danger me-2">Stok: {{ $item->current_stock }}</span>
                                                    <span class="badge bg-warning">Habis</span>
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-danger">
                            <i class="bi bi-bell me-1"></i>Lihat Semua Alerts
                        </a>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-graph-up text-success fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Semua stok dalam kondisi baik</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-activity text-primary me-2"></i>Aktivitas Terbaru
                </h6>
                <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-list me-1"></i>Lihat Semua
                </a>
            </div>
            <div class="card-body">
                @if($recentActivities->count() > 0)
                    <div class="timeline">
                        @foreach($recentActivities->take(10) as $activity)
                            <div class="timeline-item mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="timeline-icon bg-{{ $activity->quantity > 0 ? 'success' : 'danger' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-{{ $activity->quantity > 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $activity->quantity > 0 ? 'success' : 'danger' }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $activity->item->name ?? 'Item' }}</h6>
                                                <p class="text-muted small mb-1">
                                                    <i class="bi bi-building me-1"></i>{{ $activity->warehouse->name ?? 'N/A' }}
                                                    @if($activity->user)
                                                        <span class="mx-2">•</span>
                                                        <i class="bi bi-person me-1"></i>{{ $activity->user->name }}
                                                    @endif
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <span class="badge bg-{{ $activity->quantity > 0 ? 'success' : 'danger' }}">
                                                {{ $activity->quantity > 0 ? '+' : '' }}{{ number_format($activity->quantity) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-activity text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Belum ada aktivitas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-card {
        transition: all 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }

    .clickable-card {
        cursor: pointer;
    }

    .clickable-card:active {
        transform: translateY(0px);
    }

    .timeline-item {
        position: relative;
        padding-left: 50px;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        width: 2px;
        height: calc(100% - 20px);
        background: #e9ecef;
    }

    .timeline-icon {
        position: relative;
        z-index: 1;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .badge {
        font-size: 0.7em;
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05);
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .bg-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }
    
    .border-warning {
        animation: pulse-border 2s infinite;
    }
    
    @keyframes pulse-border {
        0%, 100% {
            border-color: #ffc107;
        }
        50% {
            border-color: #ffdb6d;
        }
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Monthly movements bar chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($monthlyMovements);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Stock In',
                data: monthlyData.map(item => item.stock_in),
                backgroundColor: 'rgba(25, 135, 84, 0.8)',
                borderColor: 'rgb(25, 135, 84)',
                borderWidth: 1
            }, {
                label: 'Stock Out',
                data: monthlyData.map(item => item.stock_out),
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: 'rgb(220, 53, 69)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y.toLocaleString();
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush