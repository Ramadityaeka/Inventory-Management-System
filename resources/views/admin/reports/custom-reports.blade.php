@extends('layouts.app')

@section('page-title', 'Laporan Stok Per Masing-Masing')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Laporan Stok Custom</h4>
        <p class="text-muted">Export laporan stok berdasarkan kategori, gudang, atau supplier</p>
    </div>
</div>

<div class="row">
    <!-- Laporan Per Kategori -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Laporan Per Kategori</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Export stok dikelompokkan berdasarkan kategori item</p>
                <form action="{{ route('admin.reports.export-by-category') }}" method="GET" class="mt-3">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Pilih Kategori (Opsional)</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Kosongkan untuk export semua kategori</small>
                    </div>

                    <div class="mb-3">
                        <label for="category_warehouse_id" class="form-label">Filter Gudang (Opsional)</label>
                        <select class="form-select" id="category_warehouse_id" name="warehouse_id">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Laporan Per Gudang -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Laporan Per Gudang</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Export stok dikelompokkan berdasarkan gudang/unit</p>
                <form action="{{ route('admin.reports.export-by-warehouse') }}" method="GET" class="mt-3">
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Pilih Gudang (Opsional)</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Kosongkan untuk export semua gudang</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <p class="small text-muted mb-0">
                            Laporan ini menampilkan:<br>
                            • Stok per gudang<br>
                            • Nilai stok (estimasi)<br>
                            • Status ketersediaan
                        </p>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Laporan Per Supplier -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Laporan Per Supplier</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Export stok dikelompokkan berdasarkan supplier</p>
                <form action="{{ route('admin.reports.export-by-supplier') }}" method="GET" class="mt-3">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Pilih Supplier (Opsional)</label>
                        <select class="form-select" id="supplier_id" name="supplier_id">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Kosongkan untuk export semua supplier</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <p class="small text-muted mb-0">
                            Laporan ini berguna untuk:<br>
                            • Analisis supplier per item<br>
                            • Perencanaan pembelian<br>
                            • Evaluasi kerjasama
                        </p>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Laporan Detail (Semua Filter) -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Laporan Detail Lengkap</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Export laporan lengkap dengan banyak kolom informasi</p>
                <form action="{{ route('admin.reports.export-detailed') }}" method="GET" class="mt-3">
                    <div class="mb-3">
                        <label for="detailed_warehouse_id" class="form-label">Filter Gudang</label>
                        <select class="form-select form-select-sm" id="detailed_warehouse_id" name="warehouse_id">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="detailed_category_id" class="form-label">Filter Kategori</label>
                        <select class="form-select form-select-sm" id="detailed_category_id" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="detailed_supplier_id" class="form-label">Filter Supplier</label>
                        <select class="form-select form-select-sm" id="detailed_supplier_id" name="supplier_id">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="detailed_status" class="form-label">Filter Status</label>
                        <select class="form-select form-select-sm" id="detailed_status" name="status">
                            <option value="">Semua Status</option>
                            <option value="available">Tersedia</option>
                            <option value="out">Habis</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-info w-100">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel Detail
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Info Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Informasi Format Excel</h6>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted small">Kolom Standard:</h6>
                        <ul class="small">
                            <li>No</li>
                            <li>Kode Item</li>
                            <li>Nama Item</li>
                            <li>Kategori</li>
                            <li>Gudang</li>
                            <li>Stok</li>
                            <li>Satuan</li>
                            <li>Status (Habis/Tersedia)</li>
                            <li>Terakhir Update</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small">Kolom Tambahan (Detail):</h6>
                        <ul class="small">
                            <li>Supplier</li>
                            <li>Deskripsi Item</li>
                            <li>Harga Terakhir</li>
                            <li>Nilai Stok (estimasi)</li>
                            <li>Status Aktif Item</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <small>
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Tips:</strong> File Excel dapat dibuka dengan Microsoft Excel atau Google Sheets. 
                        Header berwarna dan data sudah terformat otomatis untuk memudahkan analisis.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
