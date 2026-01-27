@extends('layouts.app')

@section('title', 'Laporan Stok & Nilai Barang')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">📦 Laporan Daftar Stok Barang & Nilai</h1>
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
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.stock-values') }}" id="filterForm">
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
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-search"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('admin.reports.stock-values') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Jenis Barang</h5>
                    <h2>{{ number_format($totalItems) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total Jumlah Barang</h5>
                    <h2>{{ number_format($totalQuantity) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Nilai Keseluruhan</h5>
                    <h2>Rp {{ number_format($totalStockValue, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Values Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Data Stok & Nilai Barang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Gudang</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Harga/Satuan</th>
                            <th>Harga Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocksData as $index => $data)
                            <tr>
                                <td>{{ $stocks->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $data['warehouse']->name }}</span>
                                </td>
                                <td>
                                    <code>{{ $data['item']->code }}</code>
                                </td>
                                <td>
                                    <strong>{{ $data['item']->name }}</strong>
                                    @if($data['quantity'] <= 0)
                                        <br><span class="badge bg-danger">Habis</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $data['item']->category->name }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format($data['quantity']) }}</strong>
                                </td>
                                <td>{{ $data['item']->unit }}</td>
                                <td>
                                    @if($data['unit_price'] > 0)
                                        Rp {{ number_format($data['unit_price'], 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($data['total_value'] > 0)
                                        <strong>Rp {{ number_format($data['total_value'], 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data stok</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($stocksData->count() > 0)
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="5" class="text-end"><strong>TOTAL KESELURUHAN:</strong></td>
                                <td><strong>{{ number_format($totalQuantity) }}</strong></td>
                                <td>item</td>
                                <td></td>
                                <td><strong>Rp {{ number_format($totalStockValue, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $stocks->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function exportPdf() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.reports.stock-values.pdf") }}?' + params.toString();
}

function exportExcel() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.reports.stock-values.excel") }}?' + params.toString();
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
            fetch('{{ route("admin.reports.stock-values.search-items") }}?q=' + encodeURIComponent(query))
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
