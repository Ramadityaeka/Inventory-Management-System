@extends('layouts.app')

@section('page-title', 'Dashboard Admin Gudang - ' . $warehouseName)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Dashboard Admin Gudang - {{ $warehouseName }}</h4>
                <p class="text-muted mb-0">Pantau dan kelola aktivitas gudang dengan mudah</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('gudang.reports.monthly') }}" class="btn btn-outline-primary">
                    <i class="bi bi-graph-up me-1"></i>Laporan
                </a>
                <a href="{{ route('gudang.stocks.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-gear me-1"></i>Pengaturan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Important Reminders -->
@if(($stats['pending_submissions'] ?? 0) > 0 || ($lowStockItems->count() ?? 0) > 0)
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
                        @if(($stats['pending_submissions'] ?? 0) > 0)
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history text-warning me-2"></i>
                                <span>{{ $stats['pending_submissions'] }} submission menunggu approval</span>
                                <a href="{{ route('gudang.submissions.index') }}" class="btn btn-sm btn-warning ms-auto">Tinjau</a>
                            </div>
                        </div>
                        @endif
                        @if(($lowStockItems->count() ?? 0) > 0)
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-circle text-danger me-2"></i>
                                <span>{{ $lowStockItems->count() }} item stok rendah</span>
                                <a href="{{ route('gudang.alerts') }}" class="btn btn-sm btn-danger ms-auto">Periksa</a>
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
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card" onclick="window.location='{{ route('gudang.stocks.index') }}'">
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
                                <p class="text-muted small mb-0">Total Items di Gudang</p>
                                <small class="text-success">Klik untuk detail</small>
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
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card" onclick="window.location='{{ route('gudang.stocks.index') }}'">
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
                                <small class="text-info">Semua item</small>
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
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card {{ $stats['pending_submissions'] > 0 ? 'border-warning' : '' }}" onclick="window.location='{{ route('gudang.submissions.index') }}'" style="{{ $stats['pending_submissions'] > 0 ? 'border-width: 2px !important;' : '' }}">
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
                                <h5 class="mb-1">{{ number_format($stats['pending_submissions']) }}</h5>
                                <p class="text-muted small mb-0">Pending Submissions</p>
                                @if($stats['pending_submissions'] > 0)
                                    <small class="text-warning fw-bold">Perlu ditinjau</small>
                                @else
                                    <small class="text-success">Semua clear</small>
                                @endif
                            </div>
                            @if($stats['pending_submissions'] > 0)
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

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card clickable-card" onclick="window.location='{{ route('gudang.stock-requests.index') }}'">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-arrow-down-circle fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['incoming_transfers']) }}</h5>
                                <p class="text-muted small mb-0">Incoming Transfers</p>
                                <small class="text-primary">Lihat transfer</small>
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
                        <a href="{{ route('gudang.submissions.index') }}" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-clock-history fs-4 mb-2"></i>
                                <div class="small">Pending Approval</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('gudang.stock-requests.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-arrow-left-right fs-4 mb-2"></i>
                                <div class="small">Stock Requests</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('gudang.stocks.index') }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-boxes fs-4 mb-2"></i>
                                <div class="small">Kelola Stok</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('gudang.reports.monthly') }}" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-graph-up fs-4 mb-2"></i>
                                <div class="small">Laporan</div>
                            </div>
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
                    <i class="bi bi-calendar-day text-info me-2"></i>Ringkasan Hari Ini - {{ formatDateIndo(now(), 'l, d F Y') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-plus-circle fs-3 text-success"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_stock_in'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Stock Masuk</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-dash-circle fs-3 text-danger"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_stock_out'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Stock Keluar</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-check-circle fs-3 text-primary"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_approved'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Disetujui</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="bi bi-clock-history fs-3 text-warning"></i>
                            </div>
                            <h5 class="mb-1">{{ number_format($stats['today_pending'] ?? 0) }}</h5>
                            <p class="text-muted small mb-0">Menunggu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tasks and Alerts Row -->
<div class="row mb-4 g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Pending Submissions
                </h6>
                @if($recentSubmissions->count() > 0)
                    <span class="badge bg-warning">{{ $recentSubmissions->count() }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($recentSubmissions->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentSubmissions->take(4) as $submission)
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="bi bi-clock-history text-warning fs-5"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">{{ $submission->item ? $submission->item->name : $submission->item_name }}</h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-person me-1"></i>{{ $submission->staff->name }}
                                                    <span class="mx-2">•</span>
                                                    <i class="bi bi-clock me-1"></i>{{ $submission->submitted_at->format('d/m H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-info">{{ number_format($submission->quantity) }} {{ $submission->unit }}</span>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <form action="{{ route('gudang.submissions.approve', $submission->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve submission ini?')">
                                                    <i class="bi bi-check-lg me-1"></i>Approve
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger reject-btn" data-submission-id="{{ $submission->id }}">
                                                <i class="bi bi-x-lg me-1"></i>Reject
                                            </button>
                                            <a href="{{ route('gudang.submissions.show', $submission->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('gudang.submissions.index') }}" class="btn btn-primary">
                            <i class="bi bi-list me-1"></i>Lihat Semua
                        </a>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle text-success fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Tidak ada pending submissions</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100" id="lowStockSection">
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
                        @foreach($lowStockItems->take(4) as $item)
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-exclamation-triangle text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $item->item_name }}</h6>
                                                <small class="text-muted d-block mb-2">{{ $item->warehouse_name }}</small>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge {{ $item->current_stock == 0 ? 'bg-danger' : 'bg-warning' }}">Stok: {{ $item->current_stock }}</span>
                                                    @if($item->current_stock == 0)
                                                        <span class="badge bg-danger">Habis</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <a href="{{ route('gudang.stocks.history', $item->item_id) }}" class="btn btn-sm btn-outline-danger ms-2">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('gudang.alerts') }}" class="btn btn-danger">
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
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-activity text-primary me-2"></i>Aktivitas Terbaru
                </h6>
                <a href="{{ route('gudang.reports.monthly') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-list me-1"></i>Lihat Semua
                </a>
            </div>
            <div class="card-body">
                @if($recentActivities->count() > 0)
                    <div class="timeline">
                        @foreach($recentActivities->take(6) as $activity)
                            <div class="timeline-item mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="timeline-icon me-3">
                                        @if($activity->quantity > 0)
                                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="bi bi-arrow-up-circle-fill text-success fs-5"></i>
                                            </div>
                                        @else
                                            <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="bi bi-arrow-down-circle-fill text-danger fs-5"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-1">{{ $activity->item->name ?? 'Unknown Item' }}</h6>
                                            <span class="badge bg-{{ $activity->quantity > 0 ? 'success' : 'danger' }} fs-6 px-3 py-2">
                                                {{ $activity->quantity > 0 ? '+' : '' }}{{ number_format($activity->quantity) }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-1">
                                            <i class="bi bi-person-circle me-1"></i>{{ $activity->user->name ?? 'System' }}
                                            <span class="mx-2">•</span>
                                            <i class="bi bi-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                                        </p>
                                        @if($activity->description)
                                            <small class="text-muted">{{ Str::limit($activity->description, 50) }}</small>
                                        @endif
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



<!-- Single Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Reject <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
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

    .timeline-item {
        position: relative;
        padding-left: 60px;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 22px;
        top: 50px;
        width: 2px;
        height: calc(100% - 30px);
        background: linear-gradient(180deg, #e9ecef 0%, transparent 100%);
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        z-index: 1;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .clickable-card {
        cursor: pointer;
    }

    .clickable-card:active {
        transform: translateY(0px);
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
    
    .timeline-item {
        transition: all 0.3s ease;
    }

    .badge {
        font-weight: 600;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        animation: slideIn 0.5s ease-out;
    }
    
    kbd {
        background-color: #2a5298;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .progress {
        height: 10px;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease-in-out;
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

    // Handle reject button clicks
    document.addEventListener('DOMContentLoaded', function() {
        const rejectButtons = document.querySelectorAll('.reject-btn');
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const rejectForm = document.getElementById('rejectForm');
        const rejectionReasonTextarea = document.getElementById('rejection_reason');

        rejectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const submissionId = this.getAttribute('data-submission-id');
                const rejectUrl = `/gudang/submissions/${submissionId}/reject`;
                
                // Set form action
                rejectForm.setAttribute('action', rejectUrl);
                
                // Clear previous rejection reason
                rejectionReasonTextarea.value = '';
                
                // Show modal
                rejectModal.show();
            });
        });

        // Reset form when modal is hidden
        document.getElementById('rejectModal').addEventListener('hidden.bs.modal', function () {
            rejectionReasonTextarea.value = '';
        });
    });

    // Daily stock movements line chart
    const ctx = document.getElementById('dailyChart').getContext('2d');
    const dailyData = @json($dailyMovements);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
            }),
            datasets: [{
                label: 'Stock In',
                data: dailyData.map(item => item.stock_in),
                borderColor: 'rgb(25, 135, 84)',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(25, 135, 84)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }, {
                label: 'Stock Out',
                data: dailyData.map(item => item.stock_out),
                borderColor: 'rgb(220, 53, 69)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(220, 53, 69)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        maxTicksLimit: 10
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
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
    // Auto-refresh notification for new submissions
    @if($stats['pending_submissions'] > 0)
        // Check for new submissions every 5 minutes
        setInterval(function() {
            fetch('{{ route("gudang.submissions.statistics") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.pending > {{ $stats['pending_submissions'] }}) {
                        // Show notification
                        const notification = document.createElement('div');
                        notification.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                        notification.style.zIndex = '9999';
                        notification.innerHTML = `
                            <i class="bi bi-bell-fill me-2"></i>
                            <strong>Submission Baru!</strong> Ada ${data.pending - {{ $stats['pending_submissions'] }}} submission baru yang perlu ditinjau.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.body.appendChild(notification);
                        
                        // Auto dismiss after 5 seconds
                        setTimeout(() => {
                            notification.remove();
                        }, 5000);
                    }
                })
                .catch(error => console.log('Error checking for updates'));
        }, 300000); // 5 minutes
    @endif



    // Show success message on approval
    @if(session('success'))
        const successToast = document.createElement('div');
        successToast.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
        successToast.style.zIndex = '9999';
        successToast.innerHTML = `
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(successToast);
        setTimeout(() => successToast.remove(), 4000);
    @endif

    // Export daily report function
    function exportDailyReport() {
        const today = new Date().toISOString().split('T')[0];
        const reportData = {
            date: today,
            stats: {
                stockIn: {{ $stats['today_stock_in'] ?? 0 }},
                stockOut: {{ $stats['today_stock_out'] ?? 0 }},
                submissions: {{ $stats['today_submissions'] ?? 0 }},
                pending: {{ $stats['pending_submissions'] ?? 0 }},
                approved: {{ $stats['total_approved'] ?? 0 }},
                rejected: {{ $stats['total_rejected'] ?? 0 }},
                approvalRate: {{ $stats['approval_rate'] ?? 0 }}
            }
        };

        // Create CSV content with proper formatting for Excel using comma separators with quotes
        let csvContent = "data:text/csv;charset=utf-8,%EF%BB%BF"; // Add BOM for UTF-8
        csvContent += '"LAPORAN HARIAN GUDANG"\n';
        csvContent += '"Tanggal","' + today + '"\n\n';
        csvContent += '"Metrik","Nilai"\n';
        csvContent += '"Stock Masuk","' + reportData.stats.stockIn + '"\n';
        csvContent += '"Stock Keluar","' + reportData.stats.stockOut + '"\n';
        csvContent += '"Total Pengajuan","' + reportData.stats.submissions + '"\n';
        csvContent += '"Pending Review","' + reportData.stats.pending + '"\n';
        csvContent += '"Disetujui","' + reportData.stats.approved + '"\n';
        csvContent += '"Ditolak","' + reportData.stats.rejected + '"\n';
        csvContent += '"Approval Rate","' + reportData.stats.approvalRate + '%"\n';

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "laporan_harian_" + today + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Show success message
        showToast('success', 'Laporan berhasil diekspor!');
    }

    // Refresh data function
    function refreshDashboard() {
        showToast('info', 'Memperbarui data...');
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    // Show toast notification
    function showToast(type, message) {
        const colors = {
            success: 'success',
            info: 'info',
            warning: 'warning',
            danger: 'danger'
        };
        
        const icons = {
            success: 'check-circle-fill',
            info: 'info-circle-fill',
            warning: 'exclamation-triangle-fill',
            danger: 'x-circle-fill'
        };

        const toast = document.createElement('div');
        toast.className = `alert alert-${colors[type]} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        toast.innerHTML = `
            <i class="bi bi-${icons[type]} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Add refresh button to header
    document.addEventListener('DOMContentLoaded', function() {
        // Add floating action button
        const fab = document.createElement('div');
        fab.className = 'position-fixed bottom-0 end-0 mb-4 me-4';
        fab.style.zIndex = '1000';
        fab.innerHTML = `
            <div class="btn-group-vertical" role="group">
                <button type="button" class="btn btn-primary rounded-circle shadow-lg mb-2" 
                        onclick="refreshDashboard()" 
                        style="width: 56px; height: 56px;"
                        title="Refresh Dashboard">
                    <i class="bi bi-arrow-clockwise fs-5"></i>
                </button>
                <button type="button" class="btn btn-success rounded-circle shadow-lg mb-2" 
                        onclick="window.location.href='{{ route('gudang.stocks.create') }}'" 
                        style="width: 56px; height: 56px;"
                        title="Tambah Stok Cepat">
                    <i class="bi bi-plus-lg fs-5"></i>
                </button>
                <button type="button" class="btn btn-warning rounded-circle shadow-lg" 
                        onclick="exportDailyReport()" 
                        style="width: 56px; height: 56px;"
                        title="Export Laporan">
                    <i class="bi bi-download fs-5"></i>
                </button>
            </div>
        `;
        document.body.appendChild(fab);


        // Auto refresh notification count
        setInterval(function() {
            fetch('{{ route("notifications.count") }}')
                .then(response => response.json())
                .then(data => {
                    const notifBadges = document.querySelectorAll('.notification-count');
                    notifBadges.forEach(badge => {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }
                    });
                })
                .catch(error => console.log('Error fetching notification count:', error));
        }, 30000); // Check every 30 seconds
    });</script>
@endpush