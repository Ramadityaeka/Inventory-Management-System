@extends('layouts.app')

@section('page-title', 'Permintaan Stok')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Permintaan Stok</h4>
            <div>
                <a href="{{ route('staff.stock-requests.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-box-seam me-1"></i>Lihat stok barang
                </a>
                <a href="{{ route('staff.stock-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Buat Permintaan Baru
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white">Menunggu</h6>
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                    <i class="bi bi-clock-history display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white">Diterima</h6>
                        <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                    </div>
                    <i class="bi bi-check-circle display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white">Ditolak</h6>
                        <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                    </div>
                    <i class="bi bi-x-circle display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Diterima</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID Permintaan</th>
                                    <th>Tanggal</th>
                                    <th>Barang</th>
                                    <th>Jumlah</th>
                                    <th>Unit / Gudang</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td><strong>#{{ $request->id }}</strong></td>
                                        <td>{{ formatDateIndo($request->created_at) }} WIB</td>
                                        <td>
                                            <strong>{{ $request->item->name }}</strong><br>
                                            <small class="text-muted">{{ $request->item->code }}</small>
                                        </td>
                                        <td>{{ $request->quantity }} {{ $request->item->unit }}</td>
                                        <td>{{ $request->warehouse->name }}</td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>Menunggu
                                                </span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Diterima
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Ditolak
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('staff.stock-requests.show', $request) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($request->status === 'pending')
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#cancelModal"
                                                        data-request-id="{{ $request->id }}"
                                                        data-item-name="{{ $request->item->name }}"
                                                        onclick="setModalData(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $requests->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada permintaan barang.</p>
                        <a href="{{ route('staff.stock-requests.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Buat Permintaan Baru
                        </a>
                    </div>
                @endif
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
                
                <div class="alert alert-info">
                    <strong>Barang:</strong> <span id="modalItemName"></span>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Tidak
                </button>
                <form id="cancelForm" method="POST" class="d-inline">
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

@push('scripts')
<script>
function setModalData(button) {
    const requestId = button.getAttribute('data-request-id');
    const itemName = button.getAttribute('data-item-name');
    
    // Update modal content
    document.getElementById('modalItemName').textContent = itemName;
    
    // Update form action
    const form = document.getElementById('cancelForm');
    form.action = `/staff/stock-requests/${requestId}`;
}
</script>
@endpush
@endsection
