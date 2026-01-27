@extends('layouts.app')

@section('page-title', 'Detail Submission - #' . $submission->id)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Detail Submission - #{{ $submission->id }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('unit.submissions.index') }}">Verifikasi Barang</a></li>
                        <li class="breadcrumb-item active">Detail #{{ $submission->id }}</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Status Badge (Large) -->
            <div>
                @switch($submission->status)
                    @case('pending')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="bi bi-clock me-2"></i>Menunggu Verifikasi
                        </span>
                        @break
                    @case('approved')
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle me-2"></i>Telah Disetujui
                        </span>
                        @break
                    @case('rejected')
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-x-circle me-2"></i>Ditolak
                        </span>
                        @break
                    @default
                        <span class="badge bg-secondary fs-6 px-3 py-2">{{ ucfirst($submission->status) }}</span>
                @endswitch
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Column 1: Submission Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Informasi Submission
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Item Name -->
                    <div class="col-12">
                        <h5 class="text-primary mb-1">{{ $submission->item ? $submission->item->name : $submission->item_name }}</h5>
                        @if($submission->item && $submission->item->category)
                            <span class="badge bg-info">{{ $submission->item->category->name }}</span>
                        @endif
                        @if($submission->item && $submission->item->code)
                            <small class="text-muted ms-2">Kode: {{ $submission->item->code }}</small>
                        @endif
                    </div>

                    <!-- Quantity & Unit -->
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Jumlah</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-box-seam text-primary me-2 fs-4"></i>
                            <span class="fs-4 fw-bold text-primary">{{ number_format($submission->quantity, 0) }}</span>
                            <span class="text-muted ms-2">{{ $submission->unit }}</span>
                        </div>
                    </div>

                    <!-- Supplier -->
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Supplier</label>
                        <div>
                            <div class="fw-medium">{{ $submission->supplier->name ?? 'N/A' }}</div>
                            @if($submission->supplier && $submission->supplier->phone)
                                <small class="text-muted">{{ $submission->supplier->phone }}</small>
                            @endif
                        </div>
                    </div>

                    <!-- Nota Number -->
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Nomor Nota</label>
                        <div class="fw-medium">{{ $submission->nota_number ?? 'N/A' }}</div>
                    </div>

                    <!-- Receive Date -->
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Tanggal Terima</label>
                        <div class="fw-medium">
                            {{ $submission->receive_date ? \Carbon\Carbon::parse($submission->receive_date)->format('d M Y') : 'N/A' }}
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($submission->notes)
                        <div class="col-12">
                            <label class="form-label text-muted small">Catatan</label>
                            <div class="bg-light p-3 rounded">
                                {{ $submission->notes }}
                            </div>
                        </div>
                    @endif

                    <!-- Submitted By -->
                    <div class="col-12">
                        <label class="form-label text-muted small">Disubmit Oleh</label>
                        <div class="card bg-light">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    @if($submission->staff && $submission->staff->avatar)
                                        <img src="{{ Storage::url($submission->staff->avatar) }}" 
                                             alt="{{ $submission->staff->name }}" 
                                             class="rounded-circle me-3" 
                                             style="width: 48px; height: 48px; object-fit: cover;">
                                    @else
                                        <div class="avatar-initial rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 48px; height: 48px; background-color: #007bff; color: white; font-size: 1.1rem; font-weight: bold;">
                                            {{ strtoupper(substr($submission->staff->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-medium">{{ $submission->staff->name ?? 'Unknown User' }}</div>
                                        <small class="text-muted">{{ $submission->staff->role ?? 'Staff' }}</small>
                                        @if($submission->staff && $submission->staff->phone)
                                            <br><small class="text-muted">{{ $submission->staff->phone }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submitted At -->
                    <div class="col-12">
                        <label class="form-label text-muted small">Waktu Submit</label>
                        <div class="fw-medium">
                            {{ $submission->submitted_at ? formatDateIndoLong($submission->submitted_at) . ' WIB' : 'N/A' }}
                            @if($submission->submitted_at)
                                <small class="text-muted ms-2">({{ $submission->submitted_at->diffForHumans() }})</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Information (if approved or rejected) -->
        @if(in_array($submission->status, ['approved', 'rejected']) && $submission->approvals->count() > 0)
            @php
                $approval = $submission->approvals->first();
            @endphp
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>Informasi Verifikasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Aksi</label>
                            <div>
                                @if($approval->action == 'approved')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Disetujui
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Ditolak
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Admin</label>
                            <div class="fw-medium">{{ $approval->admin->name ?? 'N/A' }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Waktu {{ $approval->action == 'approved' ? 'Approve' : 'Reject' }}</label>
                            <div class="fw-medium">
                                {{ $approval->created_at ? formatDateIndoLong($approval->created_at) . ' WIB' : 'N/A' }}
                            </div>
                        </div>

                        @if($approval->rejection_reason && $approval->action == 'rejected')
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Alasan Penolakan</label>
                                <div class="fw-medium text-danger">
                                    @switch($approval->rejection_reason)
                                        @case('incomplete_data')
                                            Data tidak lengkap atau tidak valid
                                            @break
                                        @case('invalid_quantity')
                                            Jumlah quantity tidak sesuai
                                            @break
                                        @case('duplicate_entry')
                                            Data duplikat
                                            @break
                                        @case('item_not_found')
                                            Item tidak ditemukan
                                            @break
                                        @case('supplier_issue')
                                            Masalah dengan supplier
                                            @break
                                        @default
                                            Alasan lainnya
                                    @endswitch
                                </div>
                            </div>
                        @endif

                        @if($approval->notes)
                            <div class="col-12">
                                <label class="form-label text-muted small">Catatan</label>
                                <div class="bg-light p-3 rounded">
                                    {{ $approval->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Column 2: Photos -->
    <div class="col-md-4">
        <!-- Foto Nota/Invoice -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-receipt me-2"></i>Foto Nota/Invoice
                </h6>
            </div>
            <div class="card-body">
                @if($submission->invoice_photo)
                    @if(str_ends_with($submission->invoice_photo, '.pdf'))
                        <div class="alert alert-info text-center">
                            <i class="bi bi-file-pdf fs-1"></i>
                            <p class="mt-2 mb-2">File PDF</p>
                            <a href="{{ asset('storage/' . $submission->invoice_photo) }}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="bi bi-download me-1"></i>Lihat PDF
                            </a>
                        </div>
                    @else
                        <div class="position-relative">
                            <img src="{{ asset('storage/' . $submission->invoice_photo) }}" 
                                 alt="Foto Invoice" 
                                 class="img-fluid rounded shadow-sm photo-thumbnail" 
                                 style="width: 100%; cursor: pointer;"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#photoModal"
                                 data-photo-src="{{ asset('storage/' . $submission->invoice_photo) }}"
                                 data-photo-title="Foto Invoice">
                            <div class="position-absolute top-0 end-0 m-1">
                                <span class="badge bg-dark bg-opacity-75">
                                    <i class="bi bi-zoom-in"></i>
                                </span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-image fs-1 opacity-50"></i>
                        <p class="mt-2 mb-0">Tidak ada foto nota</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Foto Kondisi Barang -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-box me-2"></i>Foto Kondisi Barang
                </h6>
            </div>
            <div class="card-body">
                @if($submission->photos && $submission->photos->count() > 0)
                    <div class="row g-2">
                        @foreach($submission->photos as $photo)
                            <div class="col-6">
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                         alt="Foto Kondisi Barang" 
                                         class="img-fluid rounded shadow-sm photo-thumbnail" 
                                         style="height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#photoModal"
                                         data-photo-src="{{ asset('storage/' . $photo->file_path) }}"
                                         data-photo-title="Foto Kondisi Barang">
                                    <div class="position-absolute top-0 end-0 m-1">
                                        <span class="badge bg-dark bg-opacity-75">
                                            <i class="bi bi-zoom-in"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1 text-center">
                                    {{ $photo->file_name ?? basename($photo->file_path) }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-image fs-1 opacity-50"></i>
                        <p class="mt-2 mb-0">Tidak ada foto kondisi</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons (only if status = pending) -->
@if($submission->status == 'pending')
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning bg-light">
            <div class="card-body text-center py-4">
                <h6 class="text-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>Submission ini menunggu verifikasi Anda
                </h6>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-success btn-lg px-4" 
                            data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-circle me-2"></i>Disetujui
                    </button>
                    <button type="button" class="btn btn-danger btn-lg px-4" 
                            data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-2"></i>Ditolak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Photo Modal (Lightbox) -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalTitle">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="photoModalImage" src="" alt="Photo" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

@if($submission->status == 'pending')
<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="bi bi-check-circle me-2"></i>d
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bi bi-question-circle text-warning fs-1 mb-3"></i>
                    <h6>Approve submission ini?</h6>
                    <p class="text-muted">Stok akan otomatis bertambah setelah diapprove.</p>
                    
                    <div class="alert alert-info">
                        <strong>{{ $submission->item ? $submission->item->name : $submission->item_name }}</strong><br>
                        Quantity: <strong>{{ number_format($submission->quantity, 0) }} {{ $submission->unit }}</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('unit.submissions.approve', $submission) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Ya, Approve
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('unit.submissions.reject', $submission) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-x-circle me-2"></i>Reject Submission
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Alasan Reject <span class="text-danger">*</span></label>
                        <select class="form-select" id="rejection_reason" name="rejection_reason" required>
                            <option value="">Pilih alasan...</option>
                            <option value="incomplete_data">Nota tidak jelas</option>
                            <option value="invalid_quantity">Barang tidak sesuai</option>
                            <option value="duplicate_entry">Data tidak lengkap</option>
                            <option value="item_not_found">Kondisi barang rusak</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan Tambahan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Berikan keterangan lebih detail..."></textarea>
                        <small class="form-text text-muted">Maksimal 500 karakter</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Submission yang ditolak tidak dapat diubah kembali.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Submit Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
// Photo modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const photoModal = document.getElementById('photoModal');
    const photoModalImage = document.getElementById('photoModalImage');
    const photoModalTitle = document.getElementById('photoModalTitle');

    // Handle photo click
    document.querySelectorAll('.photo-thumbnail').forEach(function(img) {
        img.addEventListener('click', function() {
            const src = this.getAttribute('data-photo-src');
            const title = this.getAttribute('data-photo-title');
            
            photoModalImage.src = src;
            photoModalTitle.textContent = title;
        });
    });

    // Clear modal when hidden
    photoModal.addEventListener('hidden.bs.modal', function() {
        photoModalImage.src = '';
    });
});

// Form validation for reject modal
document.addEventListener('DOMContentLoaded', function() {
    const rejectForm = document.querySelector('#rejectModal form');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const rejectionReason = document.getElementById('rejection_reason').value;
            if (!rejectionReason) {
                e.preventDefault();
                alert('Mohon pilih alasan reject terlebih dahulu.');
                document.getElementById('rejection_reason').focus();
            }
        });
    }
});

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('notes');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});

// Photo hover effects
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.photo-thumbnail').forEach(function(img) {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<style>
/* Enhanced photo styling */
.photo-thumbnail {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 2px solid transparent;
}

.photo-thumbnail:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border-color: #007bff;
}

/* Avatar styling */
.avatar-initial {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

/* Card enhancements */
.card {
    transition: box-shadow 0.15s ease-in-out;
    border: 1px solid rgba(0,0,0,.125);
}

.card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Badge improvements */
.badge.fs-6 {
    font-size: 1rem !important;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}

/* Button hover effects */
.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal enhancements */
.modal-content {
    border-radius: 0.75rem;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Form control improvements */
.form-select:focus,
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Alert styling */
.alert {
    border-radius: 0.5rem;
}

/* Photo grid spacing */
.photo-thumbnail {
    border-radius: 0.5rem;
}

/* Empty state styling */
.text-muted.py-4 i {
    opacity: 0.3;
}

/* Breadcrumb styling */
.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
}

/* Action buttons area */
.card.border-warning {
    border-width: 2px;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .fs-4 {
        font-size: 1.25rem !important;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .d-flex.gap-3 {
        flex-direction: column;
        gap: 1rem !important;
    }
}
</style>
@endsection
