@extends('layouts.app')

@section('title', 'Laporan Ringkasan Stok')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Laporan Ringkasan Stok Masuk & Keluar</h4>
                    <p class="text-muted mb-0">Ringkasan barang masuk, keluar, dan sisa stok di unit Anda</p>
                </div>
                <div>
                    <a href="{{ route('gudang.reports.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-success me-2" onclick="exportExcel()">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="exportPdf()">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filter Laporan</h6>
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
                        <input type="text" name="item_name" class="form-control" placeholder="Ketik untuk mencari barang..." value="{{ request('item_name') }}">
                    </div>

                    <!-- Item Code Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="item_code" class="form-control" placeholder="Cari kode..." value="{{ request('item_code') }}">
                    </div>

                    <!-- Year Filter -->
                    <div class="col-md-2">
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
                    <div class="col-md-2">
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

                    <!-- Submit Buttons -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('gudang.reports.stock-summary') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="mb-2">Total Item</h6>
                    <h3 class="mb-0">{{ number_format($totals['total_items']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="mb-2">Total Masuk</h6>
                    <h3 class="mb-0">{{ number_format($totals['total_stock_in']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <h6 class="mb-2">Total Keluar</h6>
                    <h3 class="mb-0">{{ number_format($totals['total_stock_out']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6 class="mb-2">Sisa Stok</h6>
                    <h3 class="mb-0">{{ number_format($totals['total_current_stock']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Summary Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">Data Ringkasan Stok ({{ $summary->total() }} data)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Unit</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th class="text-end">Masuk</th>
                            <th>Satuan</th>
                            <th class="text-end">Keluar</th>
                            <th>Satuan</th>
                            <th class="text-end">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary as $index => $data)
                            <tr>
                                <td>{{ $summary->firstItem() + $index }}</td>
                                <td><span class="badge bg-info">{{ $data['warehouse_name'] }}</span></td>
                                <td>
                                    <strong>{{ $data['name'] }}</strong>
                                    <br><small class="text-muted">{{ $data['code'] }}</small>
                                </td>
                                <td>{{ $data['category'] }}</td>
                                <td>{{ $data['unit'] }}</td>
                                <td class="text-end">
                                    <span class="badge bg-success">{{ number_format($data['stock_in'], 0, ',', '.') }}</span>
                                </td>
                                <td>{{ $data['unit'] }}</td>
                                <td class="text-end">
                                    <span class="badge bg-danger">{{ number_format($data['stock_out'], 0, ',', '.') }}</span>
                                </td>
                                <td>{{ $data['unit'] }}</td>
                                <td class="text-end">
                                    <strong>{{ number_format($data['current_stock'], 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Tidak ada data stok
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($summary->hasPages())
            <div class="card-footer">
                {{ $summary->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportExcel() {
    const params = new URLSearchParams(window.location.search);
    const url = "{{ route('gudang.reports.stock-summary.excel') }}" + '?' + params.toString();
    window.location.href = url;
}

function exportPdf() {
    const params = new URLSearchParams(window.location.search);
    const url = "{{ route('gudang.reports.stock-summary.pdf') }}" + '?' + params.toString();
    window.location.href = url;
}
</script>
@endpush
