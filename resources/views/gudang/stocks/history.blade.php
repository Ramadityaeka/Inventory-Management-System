@extends('layouts.app')

@section('page-title', 'Riwayat Pergerakan Stok - ' . $item->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <a href="{{ route('gudang.stocks.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-arrow-left"></i>
                </a>
                Riwayat Pergerakan Stok
            </h4>
        </div>
    </div>
</div>

<!-- Item Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">{{ $item->name }}</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="150">Kode Item:</th>
                        <td><code>{{ $item->code }}</code></td>
                    </tr>
                    <tr>
                        <th>Kategori:</th>
                        <td>{{ $item->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Satuan:</th>
                        <td>{{ $item->unit }}</td>
                    </tr>
                    <tr>
                        <th>Supplier:</th>
                        <td>
                            @php
                                // Get latest supplier from submissions
                                $latestSubmission = $item->submissions()
                                    ->with('supplier')
                                    ->where('status', 'approved')
                                    ->latest('submitted_at')
                                    ->first();
                            @endphp
                            @if($latestSubmission && $latestSubmission->supplier)
                                <strong>{{ $latestSubmission->supplier->name }}</strong>
                                @if($latestSubmission->supplier->phone)
                                    <br><small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $latestSubmission->supplier->phone }}</small>
                                @endif
                                @if($latestSubmission->supplier->email)
                                    <br><small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $latestSubmission->supplier->email }}</small>
                                @endif
                            @else
                                <span class="text-muted">Belum ada data supplier</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">Stok Saat Ini per Unit</h6>
                @if($currentStocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th class="text-end">Stok</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentStocks as $stock)
                                    <tr>
                                        <td>{{ $stock->warehouse->name }}</td>
                                        <td class="text-end">
                                            {{ number_format($stock->quantity) }} {{ $stock->item->unit }}
                                        </td>
                                        <td class="text-end">
                                            @if($stock->quantity == 0)
                                                <span class="badge bg-danger">Habis</span>
                                            @else
                                                <span class="badge bg-success">Tersedia</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Tidak ada informasi stok</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('gudang.stocks.history', $item) }}" class="row g-3">
            <div class="col-md-3">
                <label for="warehouse_id" class="form-label">Unit</label>
                <select class="form-select" id="warehouse_id" name="warehouse_id">
                    <option value="">Semua Unit</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Tipe</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Barang Masuk</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Barang Keluar</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('gudang.stocks.history', $item) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Movement History Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Riwayat Pergerakan</h6>
        <span class="badge bg-secondary">{{ $movements->total() }} pergerakan</span>
    </div>
    <div class="card-body">
        @if($movements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal/Waktu</th>
                            <th>Unit</th>
                            <th>Tipe</th>
                            <th class="text-end">Jumlah</th>
                            <th>Supplier</th>
                            <th>Diajukan Oleh</th>
                            <th>Catatan</th>
                            <th class="text-center" width="80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr>
                                <td>
                                    <div>{{ $movement->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $movement->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $movement->warehouse->name }}</span>
                                </td>
                                <td>
                                    @switch($movement->movement_type)
                                        @case('in')
                                            <span class="badge bg-success">
                                                <i class="bi bi-arrow-up-circle me-1"></i>Barang Masuk
                                            </span>
                                            @break
                                        @case('out')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-arrow-down-circle me-1"></i>Barang Keluar
                                            </span>
                                            @break
                                        @case('adjustment')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-gear me-1"></i>Penyesuaian
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($movement->movement_type) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold fs-5 {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($movement->quantity) }}
                                    </span>
                                    <br><small class="text-muted">{{ $movement->item->unit }}</small>
                                </td>
                                <td>
                                    @if($movement->submission && $movement->submission->supplier)
                                        <small>
                                            <strong>{{ $movement->submission->supplier->name }}</strong>
                                            @if($movement->submission->supplier->phone)
                                                <br><span class="text-muted">{{ $movement->submission->supplier->phone }}</span>
                                            @endif
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->submission && $movement->submission->staff)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 28px; height: 28px; background-color: #28a745; color: white; font-size: 0.7rem; font-weight: bold;">
                                                {{ strtoupper(substr($movement->submission->staff->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <small><strong>{{ $movement->submission->staff->name }}</strong></small>
                                                <br><small class="text-muted">Staff Unit</small>
                                            </div>
                                        </div>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->notes)
                                        <small class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $movement->notes }}">
                                            {{ Str::limit($movement->notes, 50) }}
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal{{ $movement->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Detail Modal -->
                            <div class="modal fade" id="detailModal{{ $movement->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail Pergerakan Stok</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Tanggal & Waktu</label>
                                                    <p class="fw-bold">{{ $movement->created_at->format('d M Y, H:i:s') }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Unit/Gudang</label>
                                                    <p class="fw-bold">{{ $movement->warehouse->name }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Tipe Pergerakan</label>
                                                    <p>
                                                        @switch($movement->movement_type)
                                                            @case('in')
                                                                <span class="badge bg-success">
                                                                    <i class="bi bi-arrow-up-circle me-1"></i>Barang Masuk
                                                                </span>
                                                                @break
                                                            @case('out')
                                                                <span class="badge bg-danger">
                                                                    <i class="bi bi-arrow-down-circle me-1"></i>Barang Keluar
                                                                </span>
                                                                @break
                                                            @case('adjustment')
                                                                <span class="badge bg-warning text-dark">
                                                                    <i class="bi bi-gear me-1"></i>Penyesuaian
                                                                </span>
                                                                @break
                                                        @endswitch
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-muted small">Jumlah</label>
                                                    <p class="fw-bold fs-4 {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($movement->quantity) }} {{ $movement->item->unit }}
                                                    </p>
                                                </div>
                                                @if($movement->submission && $movement->submission->supplier)
                                                    <div class="col-12">
                                                        <label class="form-label text-muted small">Supplier</label>
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <h6 class="mb-2">{{ $movement->submission->supplier->name }}</h6>
                                                                @if($movement->submission->supplier->phone)
                                                                    <p class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $movement->submission->supplier->phone }}</p>
                                                                @endif
                                                                @if($movement->submission->supplier->email)
                                                                    <p class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $movement->submission->supplier->email }}</p>
                                                                @endif
                                                                @if($movement->submission->supplier->address)
                                                                    <p class="mb-0"><i class="bi bi-geo-alt me-2"></i>{{ $movement->submission->supplier->address }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($movement->submission && $movement->submission->staff)
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small">Diajukan Oleh</label>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-initial rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px; background-color: #28a745; color: white; font-weight: bold;">
                                                                {{ strtoupper(substr($movement->submission->staff->name, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-bold">{{ $movement->submission->staff->name }}</p>
                                                                <small class="text-muted">Staff Unit</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($movement->creator)
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small">Disetujui Oleh</label>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-initial rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px; background-color: #007bff; color: white; font-weight: bold;">
                                                                {{ strtoupper(substr($movement->creator->name, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-bold">{{ $movement->creator->name }}</p>
                                                                <small class="text-muted">
                                                                    @if($movement->creator->role === 'super_admin')
                                                                        Super Admin
                                                                    @elseif($movement->creator->role === 'admin_gudang')
                                                                        Admin Unit
                                                                    @elseif($movement->creator->role === 'staff_gudang')
                                                                        Staff Unit
                                                                    @else
                                                                        {{ ucfirst($movement->creator->role) }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-12">
                                                    <label class="form-label text-muted small">Catatan</label>
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            @if($movement->notes)
                                                                <p class="mb-0">{{ $movement->notes }}</p>
                                                            @else
                                                                <p class="mb-0 text-muted">Tidak ada catatan</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($movements->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $movements->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-0">Tidak ada riwayat pergerakan untuk barang ini</p>
                @if(request()->hasAny(['warehouse_id', 'type', 'start_date', 'end_date']))
                    <p class="text-muted small">Coba sesuaikan filter Anda</p>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Summary Statistics -->
@if($movements->count() > 0)
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Total Barang Masuk</h6>
                    <h3 class="text-success mb-0">
                        +{{ number_format($movements->where('movement_type', 'in')->sum('quantity')) }}
                    </h3>
                    <small class="text-muted">Barang Masuk</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Total Barang Keluar</h6>
                    <h3 class="text-danger mb-0">
                        {{ number_format(abs($movements->where('movement_type', 'out')->sum('quantity'))) }}
                    </h3>
                    <small class="text-muted">Barang Keluar</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Total Penyesuaian</h6>
                    <h3 class="text-warning mb-0">
                        {{ number_format($movements->where('movement_type', 'adjustment')->count()) }}
                    </h3>
                    <small class="text-muted">Penyesuaian Stok</small>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
