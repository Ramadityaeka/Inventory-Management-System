@extends('layouts.app')

@section('page-title', 'Request Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Detail Permintaan #{{ $stockRequest->id }}</h4>
            <a href="{{ route('staff.stock-requests.my-requests') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali 
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Request Information</h5>
                    @if($stockRequest->status === 'pending')
                        <span class="badge bg-warning">
                            <i class="bi bi-clock me-1"></i>Tertunda
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
                        <label class="text-muted small">Tanggal</label>
                        <p class="mb-0">{{ formatDateIndoLong($stockRequest->created_at) }} WIB</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Request ID</label>
                        <p class="mb-0"><strong>#{{ $stockRequest->id }}</strong></p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Nama barang</label>
                        <p class="mb-0"><strong>{{ $stockRequest->item->name }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Code barang</label>
                        <p class="mb-0">{{ $stockRequest->item->code }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Kategori barang</label>
                        <p class="mb-0">
                            <span class="badge bg-secondary">{{ $stockRequest->item->category->name }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Unit</label>
                        <p class="mb-0">{{ $stockRequest->warehouse->name }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">jumlah permintaan</label>
                        <p class="mb-0"><strong>{{ $stockRequest->quantity }} {{ $stockRequest->item->unit }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">jumlah stok saat ini</label>
                        <p class="mb-0">
                            @php
                                $currentStock = \App\Models\Stock::where('item_id', $stockRequest->item_id)
                                    ->where('warehouse_id', $stockRequest->warehouse_id)
                                    ->first();
                            @endphp
                            {{ $currentStock ? $currentStock->quantity : 0 }} {{ $stockRequest->item->unit }}
                        </p>
                    </div>
                </div>

                @if($stockRequest->status !== 'pending')
                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Diproses oleh</label>
                            <p class="mb-0">{{ $stockRequest->approver->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Tanggal proses</label>
                            <p class="mb-0">{{ formatDateIndoLong($stockRequest->approved_at) }} WIB</p>
                        </div>
                    </div>

                    @if($stockRequest->status === 'rejected' && $stockRequest->rejection_reason)
                        <div class="alert alert-danger">
                            <strong>Alasan ditolak:</strong><br>
                            {{ $stockRequest->rejection_reason }}
                        </div>
                    @endif

                    @if($stockRequest->status === 'approved')
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            permintaan ini sudah di terima, stok barang akan berubah.
                        </div>
                    @endif
                @endif
            </div>
            <div class="card-footer">
                @if($stockRequest->status === 'pending')
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-trash me-1"></i>Cancel Request
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
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
                                        <p class="mb-0 small">Bukti penerimaan telah diupload</p>
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $stockRequest->received_proof_image) }}" 
                                                 alt="Bukti Penerimaan" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 200px; cursor: pointer;"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#proofImageModal">
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $stockRequest->received_proof_image) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-image me-1"></i>Lihat Bukti
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <p class="mb-0"><strong>Upload Bukti Penerimaan</strong></p>
                                        <p class="mb-0 small text-muted">Upload foto bukti penerimaan barang</p>
                                        <div class="mt-2">
                                            <span class="badge bg-warning">
                                                <i class="bi bi-hourglass-split me-1"></i>Menunggu Upload
                                            </span>
                                        </div>
                                        <form action="{{ route('staff.stock-requests.upload-proof', $stockRequest) }}" 
                                              method="POST" 
                                              enctype="multipart/form-data" 
                                              class="mt-3">
                                            @csrf
                                            <div class="mb-2">
                                                <label class="form-label small">Pilih Foto Bukti:</label>
                                                <input type="file" 
                                                       class="form-control form-control-sm" 
                                                       name="proof_image" 
                                                       accept="image/*" 
                                                       required>
                                                <small class="text-muted">Format: JPG, PNG, JPEG (Max: 2MB)</small>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                <i class="bi bi-upload me-1"></i>Upload Bukti
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Menunggu Persetujuan</strong></p>
                                <p class="mb-0 small">Admin Unit akan memeriksa permintaan</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Pembatalan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-question-circle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Apakah Anda yakin ingin membatalkan permintaan ini?</h6>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Tidak
                </button>
                <form action="{{ route('staff.stock-requests.destroy', $stockRequest) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Ya, Batalkan Permintaan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Proof Image Modal -->
<div class="modal fade" id="proofImageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">
                    <i class="bi bi-image me-2"></i>Bukti Penerimaan Barang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/' . ($stockRequest->received_proof_image ?? '')) }}" 
                     alt="Bukti Penerimaan" 
                     class="img-fluid rounded"
                     style="max-height: 70vh;">
                     
                @if($stockRequest->received_at)
                    <div class="mt-3 text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Diupload: {{ $stockRequest->received_at->format('d M Y, H:i') }}
                    </div>
                @endif
            </div>
            <div class="modal-footer border-secondary">
                <a href="{{ asset('storage/' . ($stockRequest->received_proof_image ?? '')) }}" 
                   target="_blank" 
                   class="btn btn-primary"
                   download>
                    <i class="bi bi-download me-1"></i>Download Gambar
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
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

/* Proof thumbnail hover effect */
.img-thumbnail[data-bs-toggle="modal"] {
    transition: transform 0.2s, box-shadow 0.2s;
}

.img-thumbnail[data-bs-toggle="modal"]:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Badge animation */
.badge.bg-warning {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}
</style>
@endsection
