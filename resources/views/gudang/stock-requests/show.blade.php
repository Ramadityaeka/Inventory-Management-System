@extends('layouts.app')

@section('page-title', 'Tindak lanjut barang keluar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Tindak lanjut barang keluar #{{ $stockRequest->id }}</h4>
            <a href="{{ route('gudang.stock-requests.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Permintaan
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Permintaan</h5>
                    @if($stockRequest->status === 'pending')
                        <span class="badge bg-warning">
                            <i class="bi bi-clock me-1"></i>Menunggu Persetujuan
                        </span>
                    @elseif($stockRequest->status === 'approved')
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>Diterima
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle me-1"></i>Ditolak
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Tanggal Request</label>
                        <p class="mb-0">{{ $stockRequest->created_at->format('d F Y, H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Permintaan Oleh</label>
                        <p class="mb-0">{{ $stockRequest->staff->name }}</p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Nama Barang</label>
                        <p class="mb-0"><strong>{{ $stockRequest->item->name }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Code Barang</label>
                        <p class="mb-0">{{ $stockRequest->item->code }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Kategori</label>
                        <p class="mb-0">
                            <span class="badge bg-secondary">{{ $stockRequest->item->category->name }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Unit / Gudang</label>
                        <p class="mb-0">{{ $stockRequest->warehouse->name ?? '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Jumlah Permintaan</label>
                        <p class="mb-0"><strong class="fs-5">{{ $stockRequest->quantity }} {{ $stockRequest->item->unit }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Stock Saat ini</label>
                        <p class="mb-0">
                            @php
                                $stockQty = $currentStock ? $currentStock->quantity : 0;
                            @endphp
                            <strong class="fs-5 {{ $stockQty >= $stockRequest->quantity ? 'text-success' : 'text-danger' }}">
                                {{ $stockQty }} {{ $stockRequest->item->unit }}
                            </strong>
                            @if($stockQty < $stockRequest->quantity)
                                <br><small class="text-danger">⚠️Stock Tidak Mencukupi</small>
                            @endif
                        </p>
                    </div>
                </div>

                @if($stockRequest->status !== 'pending')
                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Diproses Oleh</label>
                            <p class="mb-0">{{ $stockRequest->approver->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Tanggal DiProses</label>
                            <p class="mb-0">{{ $stockRequest->approved_at->format('d F Y, H:i') }}</p>
                        </div>
                    </div>

                    @if($stockRequest->status === 'rejected' && $stockRequest->rejection_reason)
                        <div class="alert alert-danger">
                            <strong>Alasan Ditolak:</strong><br>
                            {{ $stockRequest->rejection_reason }}
                        </div>
                    @endif
                @endif
            </div>
            
            @if($stockRequest->status === 'pending')
                <div class="card-footer bg-light">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button type="button" 
                                    class="btn btn-success w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#approveModal"
                                    {{ ($currentStock && $currentStock->quantity >= $stockRequest->quantity) ? '' : 'disabled' }}>
                                <i class="bi bi-check-circle me-1"></i>Setujui Permintaan
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-1"></i>Tolak Permintaan
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Informasi Stok</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Stok Saat Ini</label>
                    <h3 class="mb-0 {{ ($currentStock && $currentStock->quantity >= $stockRequest->quantity) ? 'text-success' : 'text-danger' }}">
                        {{ $currentStock ? $currentStock->quantity : 0 }} {{ $stockRequest->item->unit }}
                    </h3>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Stok setelah di setujui</label>
                    <h3 class="mb-0">
                        {{ ($currentStock ? $currentStock->quantity : 0) - $stockRequest->quantity }} {{ $stockRequest->item->unit }}
                    </h3>
                </div>
                @if($currentStock && $currentStock->quantity < $stockRequest->quantity)
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <small>Stock tidak mencukupi untuk approve request ini.</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Timeline Permintaan</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <p class="mb-0 small text-muted">{{ $stockRequest->created_at->format('d M Y, H:i') }}</p>
                            <p class="mb-0"><strong>Permintaan Dibuat</strong></p>
                            <p class="mb-0 small">Oleh {{ $stockRequest->staff->name }}</p>
                        </div>
                    </div>

                    @if($stockRequest->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker {{ $stockRequest->status === 'approved' ? 'bg-success' : 'bg-danger' }}"></div>
                            <div class="timeline-content">
                                <p class="mb-0 small text-muted">{{ $stockRequest->approved_at->format('d M Y, H:i') }}</p>
                                <p class="mb-0"><strong>Permintaan {{ $stockRequest->status === 'approved' ? 'Disetujui' : 'Ditolak' }}</strong></p>
                                <p class="mb-0 small">Oleh {{ $stockRequest->approver->name }}</p>
                            </div>
                        </div>

                        @if($stockRequest->status === 'approved')
                            @if($stockRequest->received_proof_image)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <p class="mb-0 small text-muted">{{ $stockRequest->received_at->format('d M Y, H:i') }}</p>
                                        <p class="mb-0"><strong>Barang Diterima</strong></p>
                                        <p class="mb-0 small">Staff telah upload bukti penerimaan</p>
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $stockRequest->received_proof_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-image me-1"></i>Lihat Bukti Penerimaan
                                            </a>
                                        </div>
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $stockRequest->received_proof_image) }}" 
                                                 alt="Bukti Penerimaan" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 200px; cursor: pointer;"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#proofImageModal">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <p class="mb-0"><strong>Menunggu Bukti Penerimaan</strong></p>
                                        <p class="mb-0 small text-muted">Staff belum upload bukti penerimaan barang</p>
                                        <div class="mt-2">
                                            <span class="badge bg-warning">
                                                <i class="bi bi-hourglass-split me-1"></i>Pending
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Menunggu Persetujuan</strong></p>
                                <p class="mb-0 small text-muted">Admin Unit akan memeriksa permintaan</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proof Image Modal -->
<div class="modal fade" id="proofImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-image me-2"></i>Bukti Penerimaan Barang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0 bg-dark">
                @if($stockRequest->received_proof_image)
                    <img src="{{ asset('storage/' . $stockRequest->received_proof_image) }}" 
                         alt="Bukti Penerimaan" 
                         class="img-fluid"
                         style="max-height: 80vh; width: auto;">
                @endif
            </div>
            <div class="modal-footer bg-dark text-white">
                <small class="me-auto">
                    <i class="bi bi-calendar me-1"></i>
                    {{ $stockRequest->received_at ? $stockRequest->received_at->format('d F Y, H:i') : '-' }}
                </small>
                @if($stockRequest->received_proof_image)
                    <a href="{{ asset('storage/' . $stockRequest->received_proof_image) }}" 
                       download 
                       class="btn btn-sm btn-outline-light">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                @endif
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Konfirmasi Persetujuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-question-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Apakah Anda yakin ingin menyetujui permintaan ini?</h6>
                
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Barang:</small>
                            <p class="mb-0 fw-bold">{{ $stockRequest->item->name }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Jumlah:</small>
                            <p class="mb-0 fw-bold">{{ $stockRequest->quantity }} {{ $stockRequest->item->unit }}</p>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Stok Sekarang:</small>
                            <p class="mb-0 fw-bold text-primary">{{ $currentStock ? $currentStock->quantity : 0 }} {{ $stockRequest->item->unit }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Stok Setelah Approve:</small>
                            <p class="mb-0 fw-bold text-success">{{ ($currentStock ? $currentStock->quantity : 0) - $stockRequest->quantity }} {{ $stockRequest->item->unit }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Stok akan otomatis berkurang setelah permintaan disetujui.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Batal
                </button>
                <form action="{{ route('gudang.stock-requests.approve', $stockRequest) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Ya, Setujui Permintaan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('gudang.stock-requests.reject', $stockRequest) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle me-2"></i>Tolak Permintaan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-center mb-3">Apakah Anda yakin ingin menolak permintaan ini?</h6>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Pastikan Anda memberikan alasan penolakan yang jelas.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" 
                                  id="rejection_reason" 
                                  class="form-control" 
                                  rows="4" 
                                  required 
                                  placeholder="Jelaskan alasan mengapa permintaan ini ditolak..."></textarea>
                        <small class="form-text text-muted">Alasan ini akan dikirimkan sebagai notifikasi kepada staff yang mengajukan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Ya, Tolak Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 20px;
    width: 2px;
    height: calc(100% - 10px);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -28px;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
    z-index: 1;
}

.timeline-content {
    padding: 0;
}

/* Proof image hover effect */
.img-thumbnail[data-bs-toggle="modal"] {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.img-thumbnail[data-bs-toggle="modal"]:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

/* Modal image styling */
#proofImageModal .modal-body img {
    display: block;
    margin: auto;
}

/* Badge animation for pending status */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.timeline-item .badge.bg-warning {
    animation: pulse 2s infinite;
}
</style>
@endsection
