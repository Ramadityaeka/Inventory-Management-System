@extends('layouts.app')

@section('page-title', 'Verifikasi Barang Masuk')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Verifikasi Barang Masuk</h4>
            <div class="text-muted">
                <i class="bi bi-clipboard-check me-1"></i>
                Kelola persetujuan submission barang
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills" id="status-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentStatus == 'pending' ? 'active' : '' }}" 
                           href="{{ route('unit.submissions.index', ['status' => 'pending']) }}">
                            <i class="bi bi-clock me-1"></i>
                            Pending
                            @if($pendingCount > 0)
                                <span class="badge bg-warning text-dark ms-2">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentStatus == 'approved' ? 'active' : '' }}" 
                           href="{{ route('unit.submissions.index', ['status' => 'approved']) }}">
                            <i class="bi bi-check-circle me-1"></i>
                            Disetujui
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentStatus == 'rejected' ? 'active' : '' }}" 
                           href="{{ route('unit.submissions.index', ['status' => 'rejected']) }}">
                            <i class="bi bi-x-circle me-1"></i>
                            Ditolak
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentStatus == 'all' ? 'active' : '' }}" 
                           href="{{ route('unit.submissions.index', ['status' => 'all']) }}">
                            <i class="bi bi-list me-1"></i>
                            All
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Submissions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-table me-2"></i>Daftar Submission
                </h6>
                <div class="text-muted small">
                    @if($submissions->count() > 0)
                        Showing {{ $submissions->firstItem() }} - {{ $submissions->lastItem() }} of {{ $submissions->total() }} submissions
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($submissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th width="80">ID</th>
                                    <th width="180">Diajukan Oleh</th>
                                    <th>Nama Barang</th>
                                    <th width="120">Jumlah & Satuan</th>
                                    <th width="150">Supplier</th>
                                    <th width="140">Waktu Pengajuan</th>
                                    <th width="100">Status</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td>
                                            <small class="text-muted">#{{ $submission->id }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($submission->staff && $submission->staff->avatar)
                                                    <img src="{{ Storage::url($submission->staff->avatar) }}" 
                                                         alt="{{ $submission->staff->name }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 32px; height: 32px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-initial rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px; background-color: #007bff; color: white; font-size: 0.8rem; font-weight: bold;">
                                                        {{ strtoupper(substr($submission->staff->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="small fw-medium">{{ $submission->staff->name ?? 'Unknown' }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $submission->staff->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $submission->item ? $submission->item->name : $submission->item_name }}</div>
                                            @if($submission->item && $submission->item->code)
                                                <small class="text-muted">{{ $submission->item->code }}</small>
                                            @elseif($submission->item_name)
                                                <small class="badge bg-info">Manual Entry</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ number_format($submission->quantity, 0) }}</div>
                                            <small class="text-muted">{{ $submission->unit }}</small>
                                        </td>
                                        <td>
                                            <div class="small">{{ $submission->supplier->name ?? 'N/A' }}</div>
                                            @if($submission->supplier && $submission->supplier->phone)
                                                <small class="text-muted">{{ $submission->supplier->phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small" 
                                                  data-bs-toggle="tooltip" 
                                                  data-bs-placement="top" 
                                                  title="{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i:s') : 'N/A' }}">
                                                {{ $submission->submitted_at ? $submission->submitted_at->diffForHumans() : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @switch($submission->status)
                                                @case('pending')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-clock me-1"></i>Pending
                                                    </span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Disetujui
                                                    </span>
                                                @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Ditolak
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($submission->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('unit.submissions.show', $submission) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $submissions->firstItem() }} - {{ $submissions->lastItem() }} of {{ $submissions->total() }} submissions
                        </div>
                        <div>
                            {{ $submissions->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">Tidak ada submission</h5>
                        <p class="text-muted mb-0">
                            @switch($currentStatus)
                                @case('pending')
                                    Belum ada submission yang menunggu verifikasi.
                                    @break
                                @case('approved')
                                    Belum ada submission yang telah diapprove.
                                    @break
                                @case('rejected')
                                    Belum ada submission yang ditolak.
                                    @break
                                @default
                                    Belum ada submission barang masuk.
                            @endswitch
                        </p>
                        @if($currentStatus !== 'all')
                            <a href="{{ route('unit.submissions.index', ['status' => 'all']) }}" 
                               class="btn btn-outline-primary mt-3">
                                <i class="bi bi-list me-1"></i>Lihat Semua Submission
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions (if pending submissions exist) -->
@if($currentStatus == 'pending' && $submissions->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        Tip: Pilih beberapa submission untuk approve sekaligus
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick="showBulkActions()">
                        <i class="bi bi-check2-all me-1"></i>Bulk Actions
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Table row hover effects
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(0,123,255,0.05)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});

// Show bulk actions modal/form
function showBulkActions() {
    // Implementation for bulk actions
    alert('Bulk actions feature will be implemented here');
}

// Auto-refresh pending count every 30 seconds if on pending tab
@if($currentStatus == 'pending')
setInterval(function() {
    fetch('{{ route("unit.submissions.statistics") }}')
        .then(response => response.json())
        .then(data => {
            // Update the pending badge
            const pendingBadge = document.querySelector('a[href*="pending"] .badge');
            if (pendingBadge && data.pending > 0) {
                pendingBadge.textContent = data.pending;
            } else if (pendingBadge && data.pending == 0) {
                pendingBadge.remove();
            }
        })
        .catch(error => console.log('Error refreshing stats:', error));
}, 30000);
@endif

// Quick status update via AJAX (for future enhancement)
function quickApprove(submissionId) {
    if (confirm('Apakah Anda yakin ingin approve submission ini?')) {
        fetch(`/gudang/submissions/${submissionId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat approve submission.');
        });
    }
}
</script>
@endsection

@push('styles')
<style>
/* Enhanced table styling */
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05) !important;
}

.table-sm td {
    vertical-align: middle;
    padding: 0.5rem 0.25rem;
}

/* Nav pills improvements */
.nav-pills .nav-link {
    color: #6c757d;
    border-radius: 0.5rem;
    margin-right: 0.5rem;
}

.nav-pills .nav-link.active {
    background-color: #007bff;
    color: white;
}

.nav-pills .nav-link:hover:not(.active) {
    background-color: rgba(0,123,255,0.1);
    color: #007bff;
}

/* Badge improvements */
.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Avatar styling */
.avatar-initial {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

/* Empty state styling */
.text-center.py-5 i {
    opacity: 0.5;
}

/* Button hover effects */
.btn-outline-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,123,255,0.2);
}

/* Card hover effects */
.card {
    transition: box-shadow 0.15s ease-in-out;
    border: 1px solid rgba(0,0,0,.125);
}

.card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .nav-pills {
        flex-direction: column;
    }
    
    .nav-pills .nav-item {
        margin-bottom: 0.5rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Status badge animations */
.badge {
    transition: transform 0.2s ease;
}

.badge:hover {
    transform: scale(1.05);
}

/* Loading state for refresh */
.refreshing {
    opacity: 0.6;
    pointer-events: none;
}

/* Smooth transitions */
.table tbody tr {
    transition: background-color 0.2s ease;
}

/* Improved pagination */
.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #007bff;
    border-color: #dee2e6;
}

.page-link:hover {
    background-color: rgba(0,123,255,0.1);
    border-color: #007bff;
}
</style>
@endpush

