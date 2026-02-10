@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Laporan Unit</h4>
                    <p class="text-muted mb-0">Akses laporan transaksi dan nilai stok unit Anda</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Laporan Transaksi Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-receipt fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title mb-3">Laporan Transaksi</h5>
                    <p class="card-text text-muted mb-4">
                        Lihat dan ekspor data transaksi penerimaan barang di unit Anda.
                    </p>
                    <a href="{{ route('gudang.reports.transactions') }}" class="btn btn-primary px-4">
                        <i class="bi bi-file-earmark-text me-2"></i>Buka Laporan
                    </a>
                </div>
            </div>
        </div>

        <!-- Laporan Stok & Nilai Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-box-seam fs-1 text-success"></i>
                    </div>
                    <h5 class="card-title mb-3">Laporan Stok & Nilai</h5>
                    <p class="card-text text-muted mb-4">
                        Lihat dan ekspor data inventori dengan nilai harga terkini.
                    </p>
                    <a href="{{ route('gudang.reports.stock-values') }}" class="btn btn-success px-4">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>Buka Laporan
                    </a>
                </div>
            </div>
        </div>

        <!-- Laporan Ringkasan Stok Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-clipboard-data fs-1 text-info"></i>
                    </div>
                    <h5 class="card-title mb-3">Laporan Ringkasan Stok</h5>
                    <p class="card-text text-muted mb-4">
                        Ringkasan barang masuk, keluar, dan sisa stok di unit Anda.
                    </p>
                    <a href="{{ route('gudang.reports.stock-summary') }}" class="btn btn-info px-4 text-white">
                        <i class="bi bi-card-list me-2"></i>Buka Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi</h6>
                    <ul class="mb-0">
                        <li class="mb-2">Anda dapat mengekspor semua laporan ke format Excel (.xlsx)</li>
                        <li class="mb-2">Laporan menampilkan data sesuai unit yang Anda kelola</li>
                        <li class="mb-2">Gunakan filter untuk menyaring data sesuai kebutuhan</li>
                        <li>Data dapat diurutkan dan dipaginate untuk kemudahan akses</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
