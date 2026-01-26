@extends('layouts.app')

@section('page-title', 'Dashboard Staff Gudang')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Dashboard Staff Gudang</h4>
                <p class="text-muted mb-0">Selamat datang! Kelola penerimaan barang dengan mudah</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('staff.receive-items.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Terima Barang
                </a>
                <a href="{{ route('staff.drafts') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-text me-1"></i>Draft
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Row -->
<div class="row mb-4 g-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['total_submissions']) }}</h5>
                                <p class="text-muted small mb-0">Total Submission</p>
                            </div>
                            <div class="text-primary">
                                <i class="bi bi-file-earmark-text fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card">
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
                                <h5 class="mb-1">{{ number_format($stats['pending_approval']) }}</h5>
                                <p class="text-muted small mb-0">Menunggu Persetujuan</p>
                            </div>
                            @if($stats['pending_approval'] > 0)
                                <span class="badge bg-warning rounded-pill">{{ $stats['pending_approval'] }}</span>
                            @else
                                <div class="text-muted">
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
        <div class="card h-100 border-0 shadow-sm hover-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['approved_this_month']) }}</h5>
                                <p class="text-muted small mb-0">Disetujui Bulan Ini</p>
                            </div>
                            @if($stats['approved_this_month'] > 0)
                                <span class="badge bg-success rounded-pill">{{ $stats['approved_this_month'] }}</span>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-dash-circle-fill fs-5"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm hover-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-x-circle fs-4 text-danger"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">{{ number_format($stats['rejected']) }}</h5>
                                <p class="text-muted small mb-0">Ditolak</p>
                            </div>
                            @if($stats['rejected'] > 0)
                                <span class="badge bg-danger rounded-pill">{{ $stats['rejected'] }}</span>
                            @else
                                <div class="text-muted">
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
                        <a href="{{ route('staff.receive-items.create') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-plus-circle fs-4 mb-2"></i>
                                <div class="small">Terima Barang Baru</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('staff.receive-items.index') }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-list-check fs-4 mb-2"></i>
                                <div class="small">Lihat Submissions</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('staff.drafts') }}" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-file-earmark-text fs-4 mb-2"></i>
                                <div class="small">Draft Tersimpan</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('staff.stock-requests.index') }}" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center p-3">
                            <div class="text-center">
                                <i class="bi bi-arrow-left-right fs-4 mb-2"></i>
                                <div class="small">Stock Requests</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drafts and Recent Activity Row -->
<div class="row mb-4 g-3">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-file-earmark-text text-secondary me-2"></i>Draft Tersimpan
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                    </div>
                    <h2 class="mb-1">{{ number_format($draftCount) }}</h2>
                    <p class="text-muted mb-3">Draft tersimpan</p>
                    @if($draftCount > 0)
                        <span class="badge bg-secondary fs-6">{{ $draftCount }}</span>
                    @endif
                </div>
                <a href="{{ route('staff.drafts') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-eye me-2"></i>Lihat Semua Draft
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-activity text-primary me-2"></i>Recent Submissions
                </h6>
                <a href="{{ route('staff.receive-items.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-list me-1"></i>Lihat Semua
                </a>
            </div>
            <div class="card-body">
                @if($recentSubmissions->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentSubmissions->take(5) as $submission)
                            <div class="list-group-item px-0 py-3 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @if($submission->status == 'pending')
                                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-clock-history text-warning"></i>
                                            </div>
                                        @elseif($submission->status == 'approved')
                                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-check-circle text-success"></i>
                                            </div>
                                        @elseif($submission->status == 'rejected')
                                            <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-x-circle text-danger"></i>
                                            </div>
                                        @else
                                            <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-file-earmark-text text-secondary"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $submission->item ? $submission->item->name : $submission->item_name }}</h6>
                                                <small class="text-muted">{{ $submission->warehouse->name }}</small>
                                                <div class="mt-1">
                                                    <span class="badge bg-info me-2">{{ number_format($submission->quantity) }} {{ $submission->unit }}</span>
                                                    @if($submission->status == 'pending')
                                                        <span class="badge bg-warning">Menunggu</span>
                                                    @elseif($submission->status == 'approved')
                                                        <span class="badge bg-success">Disetujui</span>
                                                    @elseif($submission->status == 'rejected')
                                                        <span class="badge bg-danger">Ditolak</span>
                                                    @elseif($submission->status == 'draft')
                                                        <span class="badge bg-secondary">Draft</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $submission->status }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">{{ $submission->submitted_at?->format('d/m H:i') ?: $submission->created_at->format('d/m H:i') }}</small>
                                                <div class="mt-1">
                                                    <a href="{{ route('staff.receive-items.show', $submission) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Belum ada submission.</p>
                        <a href="{{ route('staff.receive-items.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i>Buat Submission Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Notifications and Quick Stats -->
<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-bell text-primary me-2"></i>Notifications
                </h6>
                @if($unreadNotifications > 0)
                    <span class="badge bg-danger">{{ $unreadNotifications }} unread</span>
                @endif
            </div>
            <div class="card-body">
                @if($recentNotifications->count() > 0)
                    <div class="notifications-list">
                        @foreach($recentNotifications->take(5) as $notification)
                            <div class="notification-item d-flex align-items-start mb-3 pb-3 border-bottom">
                                <div class="notification-icon me-3">
                                    @if($notification->read_at)
                                        <i class="bi bi-envelope-open text-muted"></i>
                                    @else
                                        <i class="bi bi-envelope-fill text-primary"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $notification->title }}</div>
                                    <small class="text-muted">{{ $notification->message }}</small>
                                    <div class="mt-1">
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        @if(!$notification->read_at)
                                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="markAsRead({{ $notification->id }})">
                                                Mark as read
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('notifications.index') }}" class="btn btn-primary">
                            <i class="bi bi-bell me-2"></i>Lihat Semua Notifikasi
                        </a>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-bell text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Tidak ada notifikasi.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-graph-up text-success me-2"></i>Statistik Bulan Ini
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Rata-rata Approval Time</span>
                        <span class="fw-bold">2.3 hari</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 75%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Tingkat Keberhasilan</span>
                        <span class="fw-bold">94%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 94%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Target Bulanan</span>
                        <span class="fw-bold">{{ number_format($stats['approved_this_month']) }}/50</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: {{ min(($stats['approved_this_month'] / 50) * 100, 100) }}%"></div>
                    </div>
                </div>

                <hr>

                <div class="text-center">
                    <small class="text-muted">Tips: Pastikan semua field terisi dengan benar untuk mempercepat approval</small>
                </div>
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

    .clickable-row:hover {
        background-color: #f8f9fa;
        cursor: pointer;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    .notification-item:last-child {
        border-bottom: none !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }

    .progress {
        border-radius: 10px;
    }

    .progress-bar {
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    function markAsRead(notificationId) {
        // This would typically make an AJAX call to mark the notification as read
        // For now, we'll just reload the page or hide the button
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
@endpush