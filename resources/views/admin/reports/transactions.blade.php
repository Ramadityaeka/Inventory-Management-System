@extends('layouts.app')

@section('title', 'Laporan Transaksi Barang')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">📊 Laporan Transaksi Barang Masuk & Keluar</h1>
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

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.transactions') }}" id="filterForm">
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
                               id="item_name_input" 
                               class="form-control" 
                               placeholder="Ketik untuk mencari barang..." 
                               value="{{ request('item_name') }}"
                               autocomplete="off">
                        <div id="item_suggestions" class="autocomplete-suggestions"></div>
                    </div>

                    <!-- Item Code Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="item_code" class="form-control" placeholder="Cari kode..." value="{{ request('item_code') }}">
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

                    <!-- Processed By Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Diproses Oleh</label>
                        <select name="processed_by" class="form-select">
                            <option value="">Semua Admin</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ request('processed_by') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('admin.reports.transactions') }}" class="btn btn-secondary">
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
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Transaksi</h5>
                    <h2>{{ number_format($transactions->total()) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Disetujui</h5>
                    <h2>{{ number_format($transactions->where('status', 'approved')->count()) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Menunggu</h5>
                    <h2>{{ number_format($transactions->where('status', 'pending')->count()) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Ditolak</h5>
                    <h2>{{ number_format($transactions->where('status', 'rejected')->count()) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Data Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Gudang</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Sisa Stok</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Diproses Oleh</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $index => $transaction)
                            @php
                                $currentStock = \App\Models\Stock::where('warehouse_id', $transaction->warehouse_id)
                                    ->where('item_id', $transaction->item_id)
                                    ->first();
                                $remainingStock = $currentStock ? $currentStock->quantity : 0;
                                $approval = $transaction->approvals->first();
                            @endphp
                            <tr>
                                <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $transaction->warehouse->name }}</span>
                                </td>
                                <td>
                                    <strong>{{ $transaction->item->name }}</strong><br>
                                    <small class="text-muted">{{ $transaction->item->code }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ number_format($transaction->quantity) }}</span>
                                </td>
                                <td>{{ $transaction->item->unit }}</td>
                                <td>
                                    <strong>{{ number_format($remainingStock) }}</strong>
                                </td>
                                <td>
                                    @if($transaction->notes)
                                        {{ Str::limit($transaction->notes, 50) }}
                                    @else
                                        <small class="text-muted">Penerimaan dari {{ $transaction->supplier->name ?? '-' }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->status == 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif($transaction->status == 'rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning">Menunggu</span>
                                    @endif
                                </td>
                                <td>
                                    @if($approval)
                                        <strong>{{ $approval->admin->name }}</strong><br>
                                        <small class="text-muted">{{ $approval->admin->role }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ formatDateIndoLong($transaction->submitted_at) }} WIB
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data transaksi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function exportPdf() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.reports.transactions.pdf") }}?' + params.toString();
}

function exportExcel() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.reports.transactions.excel") }}?' + params.toString();
}

// Autocomplete untuk Nama Barang
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('item_name_input');
    const suggestionsDiv = document.getElementById('item_suggestions');
    let debounceTimer;

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            suggestionsDiv.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch('{{ route("admin.reports.transactions.search-items") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        suggestionsDiv.innerHTML = data.map(item => 
                            `<div class="autocomplete-item" data-name="${item.name}">
                                <strong>${item.name}</strong>
                                <small class="text-muted"> - ${item.code}</small>
                            </div>`
                        ).join('');
                        suggestionsDiv.style.display = 'block';

                        // Event listener untuk setiap item
                        document.querySelectorAll('.autocomplete-item').forEach(item => {
                            item.addEventListener('click', function() {
                                input.value = this.getAttribute('data-name');
                                suggestionsDiv.style.display = 'none';
                            });
                        });
                    } else {
                        suggestionsDiv.innerHTML = '<div class="autocomplete-item text-muted">Tidak ada hasil</div>';
                        suggestionsDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                    suggestionsDiv.style.display = 'none';
                });
        }, 300);
    });

    // Hide suggestions ketika klik di luar
    document.addEventListener('click', function(e) {
        if (e.target !== input) {
            suggestionsDiv.style.display = 'none';
        }
    });
});
</script>

<style>
.autocomplete-suggestions {
    position: absolute;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.25rem 0.25rem;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    width: calc(100% - 24px);
}

.autocomplete-item {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.2s;
}

.autocomplete-item:hover {
    background-color: #f8f9fa;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.col-md-3 {
    position: relative;
}
</style>
@endsection
