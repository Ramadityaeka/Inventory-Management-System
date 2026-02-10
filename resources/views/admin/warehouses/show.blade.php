@extends('layouts.app')

@section('page-title', 'Detail Unit: ' . $warehouse->name)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $warehouse->name }}</h4>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt me-1"></i>{{ $warehouse->location }}
                    @if($warehouse->is_active)
                        <span class="badge bg-success ms-2">Aktif</span>
                    @else
                        <span class="badge bg-secondary ms-2">Nonaktif</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Edit Unit
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Warehouse Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3 text-muted">Informasi Unit</h6>
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="150">Kode Unit</td>
                                    <td><strong>{{ $warehouse->code }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nama Unit</td>
                                    <td><strong>{{ $warehouse->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Lokasi</td>
                                    <td>{{ $warehouse->location }}</td>
                                </tr>
                                @if($warehouse->address)
                                <tr>
                                    <td class="text-muted">Alamat</td>
                                    <td>{{ $warehouse->address }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3 text-muted">Penanggung Jawab</h6>
                        <table class="table table-borderless">
                            <tbody>
                                @if($warehouse->pic_name)
                                <tr>
                                    <td class="text-muted" width="150">Nama PIC</td>
                                    <td><strong>{{ $warehouse->pic_name }}</strong></td>
                                </tr>
                                @endif
                                @if($warehouse->pic_phone)
                                <tr>
                                    <td class="text-muted">Telepon</td>
                                    <td>
                                        <a href="tel:{{ $warehouse->pic_phone }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $warehouse->pic_phone }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Dibuat</td>
                                    <td>{{ formatDateIndoLong($warehouse->created_at) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Update Terakhir</td>
                                    <td>{{ formatDateIndoLong($warehouse->updated_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4 g-3">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-box-seam fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ number_format($stats['total_items']) }}</h5>
                        <p class="text-muted small mb-0">Total Item</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-boxes fs-4 text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ number_format($stats['total_stock']) }}</h5>
                        <p class="text-muted small mb-0">Total Stok</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm {{ $stats['low_stock'] > 0 ? 'border-warning' : '' }}" style="{{ $stats['low_stock'] > 0 ? 'border-width: 2px !important;' : '' }}">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ number_format($stats['low_stock']) }}</h5>
                        <p class="text-muted small mb-0">Stok Rendah</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm {{ $stats['out_of_stock'] > 0 ? 'border-danger' : '' }}" style="{{ $stats['out_of_stock'] > 0 ? 'border-width: 2px !important;' : '' }}">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-x-circle fs-4 text-danger"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ number_format($stats['out_of_stock']) }}</h5>
                        <p class="text-muted small mb-0">Stok Habis</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Items Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul text-primary me-2"></i>Daftar Barang di Unit
                    </h6>
                    <div class="d-flex gap-2">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari barang..." style="width: 250px;">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($stocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="stockTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Status</th>
                                    <th>Update Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $index => $stock)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><code>{{ $stock['item_code'] }}</code></td>
                                        <td>
                                            <strong>{{ $stock['item_name'] }}</strong>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $stock['category_name'] }}</span></td>
                                        <td class="text-center">
                                            <strong class="fs-6 \
                                                @if($stock['status'] == 'out_of_stock') text-danger
                                                @elseif($stock['status'] == 'low_stock') text-warning
                                                @else text-success
                                                @endif
                                            ">
                                                {{ number_format($stock['quantity']) }}
                                            </strong>
                                            <small class="text-muted d-block">{{ $stock['base_unit'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($stock['status'] == 'out_of_stock')
                                                <span class="badge bg-danger">Habis</span>
                                            @elseif($stock['status'] == 'low_stock')
                                                <span class="badge bg-warning">Rendah</span>
                                            @else
                                                <span class="badge bg-success">Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $stock['last_updated']->diffForHumans() }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada barang di unit ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Stock Movements -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history text-info me-2"></i>Pergerakan Stok Terbaru
                </h6>
            </div>
            <div class="card-body p-0">
                @if($recentMovements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Tanggal</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Jumlah</th>
                                    <th>Tipe</th>
                                    <th>Keterangan</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMovements as $index => $movement)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td><code>{{ $movement->item_code }}</code></td>
                                        <td>{{ $movement->item_name }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $movement->quantity > 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                                {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $movement->type == 'in' ? 'success' : 'danger' }}">
                                                {{ $movement->type == 'in' ? 'Masuk' : 'Keluar' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ Str::limit($movement->description ?? '-', 40) }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $movement->user_name }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada pergerakan stok</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        transition: all 0.3s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .border-warning {
        animation: pulse-border 2s infinite;
    }

    .border-danger {
        animation: pulse-border-red 2s infinite;
    }

    @keyframes pulse-border {
        0%, 100% {
            border-color: #ffc107;
        }
        50% {
            border-color: #ffdb6d;
        }
    }

    @keyframes pulse-border-red {
        0%, 100% {
            border-color: #dc3545;
        }
        50% {
            border-color: #e67882;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#stockTable tbody tr');
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
</script>
@endpush
