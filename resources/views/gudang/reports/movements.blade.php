@extends('layouts.app')

@section('page-title', 'Laporan Perpindahan Barang')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Laporan Perpindahan Barang</h4>
            <form method="POST" action="{{ route('gudang.reports.movements.exportPdf') }}" class="d-inline">
                @csrf
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="month" value="{{ request('month') }}">
                <input type="hidden" name="year" value="{{ request('year') }}">
                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-file-pdf me-1"></i>Export PDF
                </button>
            </form>
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
            
            <div class="col-md-3">
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
            
            <div class="col-md-2">
                <label for="month" class="form-label">Bulan</label>
                <select class="form-select" id="month" name="month">
                    <option value="">Pilih Bulan</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="year" class="form-label">Tahun</label>
                <select class="form-select" id="year" name="year">
                    <option value="">Pilih Tahun</option>
                    @for($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="movement_type" class="form-label">Tipe</label>
                <select class="form-select" id="movement_type" name="movement_type">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('movement_type') == 'in' ? 'selected' : '' }}>Barang Masuk</option>
                    <option value="out" {{ request('movement_type') == 'out' ? 'selected' : '' }}>Barang Keluar</option>
                    <option value="adjustment" {{ request('movement_type') == 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
            </div>
            
            <div class="col-md-3">
                <label for="end_date" class="form-label">Tanggal Selesai</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
            </div>
            
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <a href="{{ route('gudang.reports.movements') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-body">
        @if($movements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal/Waktu</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Tipe</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Stok Sisa</th>
                            <th>Oleh</th>
                            <th>Referensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr>
                                <td>
                                    <small>{{ $movement->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <code>{{ $movement->item->code }}</code>
                                </td>
                                <td>{{ $movement->item->name }}</td>
                                <td>
                                    <small class="text-muted">{{ $movement->item->category->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <small>{{ $movement->warehouse->name }}</small>
                                </td>
                                <td>
                                    @if($movement->movement_type == 'in')
                                        <span class="badge bg-success">Masuk</span>
                                    @elseif($movement->movement_type == 'out')
                                        <span class="badge bg-danger">Keluar</span>
                                    @else
                                        <span class="badge bg-warning">Penyesuaian</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="{{ $movement->movement_type == 'in' ? 'text-success' : ($movement->movement_type == 'out' ? 'text-danger' : 'text-warning') }}">
                                        {{ $movement->movement_type == 'in' ? '+' : ($movement->movement_type == 'out' ? '-' : '') }}{{ abs($movement->quantity) }}
                                    </strong>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-secondary">{{ $movement->current_stock ?? 0 }}</span>
                                </td>
                                <td>
                                    <small>{{ $movement->creator->name ?? 'System' }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ ucfirst($movement->reference_type) }} #{{ $movement->reference_id }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $movements->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">Tidak ada data perpindahan barang</p>
                <small class="text-muted">Gunakan filter untuk menampilkan data</small>
            </div>
        @endif
    </div>
</div>
@endsection
