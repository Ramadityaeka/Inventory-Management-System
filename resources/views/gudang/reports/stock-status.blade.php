@extends('layouts.app')

@section('page-title', 'Laporan Status Stok')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Laporan Status Stok</h4>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-boxes text-primary fs-1 mb-2"></i>
                <h3 class="mb-1 text-primary">{{ number_format($stats['total_items']) }}</h3>
                <p class="text-muted mb-0 small">Total Barang</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                <h3 class="mb-1 text-success">{{ number_format($stats['available']) }}</h3>
                <p class="text-muted mb-0 small">Stok Tersedia</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                <h3 class="mb-1 text-warning">{{ number_format($stats['low_stock']) }}</h3>
                <p class="text-muted mb-0 small">Stok Menipis</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                <h3 class="mb-1 text-danger">{{ number_format($stats['out_of_stock']) }}</h3>
                <p class="text-muted mb-0 small">Stok Habis</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
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
            
            <div class="col-md-4">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="status" class="form-label">Status Stok</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Stok Tersedia</option>
                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Stok Menipis</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                </select>
            </div>
            
            <div class="col-md-12 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <a href="{{ route('gudang.reports.stock-status') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-body">
        @if($stocks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th class="text-center">Stok Saat Ini</th>
                            <th class="text-center">Stok Minimum</th>
                            <th>Status</th>
                            <th>Terakhir Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>
                                    <code>{{ $stock->item->code }}</code>
                                </td>
                                <td>
                                    <strong>{{ $stock->item->name }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $stock->item->category->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <small>{{ $stock->warehouse->name }}</small>
                                </td>
                                <td class="text-center">
                                    <h5 class="mb-0">
                                        <span class="badge {{ $stock->quantity == 0 ? 'bg-danger' : 'bg-success' }}">
                                            {{ $stock->quantity }}
                                        </span>
                                    </h5>
                                    <small class="text-muted">{{ $stock->item->unit }}</small>
                                </td>
                                <td>
                                    @if($stock->quantity == 0)
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Habis
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Tersedia
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $stock->last_updated ? \Carbon\Carbon::parse($stock->last_updated)->format('d/m/Y H:i') : '-' }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $stocks->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">Tidak ada data stok</p>
                <small class="text-muted">Gunakan filter untuk menampilkan data</small>
            </div>
        @endif
    </div>
</div>
@endsection
