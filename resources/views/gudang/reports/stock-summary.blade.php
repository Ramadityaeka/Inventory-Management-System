@extends('layouts.app')

@section('title', 'Laporan Ringkasan Stok')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Laporan Ringkasan Stok Masuk & Keluar</h1>
                <div>
                    <button type="button" class="btn btn-danger" onclick="exportPdf()">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('gudang.reports.stock-summary') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Category Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Item Name Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" 
                               name="item_name" 
                               class="form-control" 
                               placeholder="Ketik nama barang..." 
                               value="{{ request('item_name') }}">
                    </div>

                    <!-- Item Code Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="item_code" class="form-control" placeholder="Ketik kode..." value="{{ request('item_code') }}">
                    </div>

                    <!-- Year Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select">
                            <option value="">Semua Tahun</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-select">
                            <option value="">Semua Bulan</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Warehouse Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Gudang / Unit</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('gudang.reports.stock-summary') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total Jenis Barang</h5>
                    <h2>{{ number_format($totals['total_items']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Barang Masuk</h5>
                    <h2>{{ number_format($totals['total_stock_in']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Barang Keluar</h5>
                    <h2>{{ number_format($totals['total_stock_out']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5>Total Sisa Stok</h5>
                    <h2>{{ number_format($totals['total_current_stock']) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Data Ringkasan Stok</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Unit</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Masuk</th>
                            <th>Satuan</th>
                            <th>Keluar</th>
                            <th>Satuan</th>
                            <th>Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary as $index => $item)
                            <tr>
                                <td>{{ $summary->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $item['warehouse_name'] }}</span>
                                </td>
                                <td>
                                    <strong>{{ $item['name'] }}</strong><br>
                                    <small class="text-muted">{{ $item['code'] }}</small>
                                </td>
                                <td>{{ $item['category'] }}</td>
                                <td>{{ $item['unit'] }}</td>
                                <td>
                                    <span class="badge bg-success">{{ number_format($item['stock_in']) }}</span>
                                </td>
                                <td>{{ $item['unit'] }}</td>
                                <td>
                                    <span class="badge bg-danger">{{ number_format($item['stock_out']) }}</span>
                                </td>
                                <td>{{ $item['unit'] }}</td>
                                <td>
                                    <strong>{{ number_format($item['current_stock']) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data untuk ditampilkan. Silakan sesuaikan filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($summary->hasPages())
            <div class="card-footer">
                {{ $summary->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function exportPdf() {
    console.log('Export PDF clicked');
    const params = new URLSearchParams(window.location.search);
    const url = "{{ route('gudang.reports.stock-summary.pdf') }}" + '?' + params.toString();
    console.log('PDF URL:', url);
    window.location.href = url;
}

function exportExcel() {
    console.log('Export Excel clicked');
    const params = new URLSearchParams(window.location.search);
    const url = "{{ route('gudang.reports.stock-summary.excel') }}" + '?' + params.toString();
    console.log('Excel URL:', url);
    window.location.href = url;
}
</script>
@endpush
