@extends('layouts.app')

@section('title', 'Laporan Stok & Nilai Barang')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Laporan Stok & Nilai Barang</h4>
                    <p class="text-muted mb-0">Data inventori dengan nilai harga dan total stok</p>
                </div>
                <div>
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
                    <div class="col-md-2">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" 
                               name="item_name" 
                               id="item_name_input" 
                               class="form-control" 
                               placeholder="Cari barang..." 
                               value="{{ request('item_name') }}"
                               autocomplete="off">
                        <div id="item_suggestions" class="autocomplete-suggestions"></div>
                    </div>

                    <!-- Item Code Filter -->
                    <div class="col-md-2">
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
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('admin.reports.stock-values') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="mb-2">Total Jenis Barang</h6>
                    <h3 class="mb-0">{{ number_format($totalItems) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6 class="mb-2">Total Stok Barang</h6>
                    <h3 class="mb-0">{{ number_format($totalQuantity) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="mb-2">Total Nilai Keseluruhan</h6>
                    <h3 class="mb-0">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Values Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">Data Stok & Nilai Barang ({{ $stocks->total() }} item)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Gudang</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-end">Stok</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga/Satuan</th>
                            <th class="text-end">Harga Total</th>
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
                                <td class="text-end">
                                    <strong>{{ number_format($data['display_quantity']) }}</strong>
                                </td>
                                <td>
                                    {{ $data['item']->unit }}
                                </td>
                                <td class="text-end">
                                    @if($data['unit_price'] > 0)
                                        Rp {{ number_format($data['unit_price'], 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($data['total_value'] > 0)
                                        <strong class="text-success">Rp {{ number_format($data['total_value'], 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Tidak ada data stok
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($stocksData->count() > 0)
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end">TOTAL:</td>
                                <td class="text-end">{{ number_format($totalQuantity) }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-end text-success">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($stocks->hasPages())
            <div class="card-footer">
                {{ $stocks->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
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
@endpush
