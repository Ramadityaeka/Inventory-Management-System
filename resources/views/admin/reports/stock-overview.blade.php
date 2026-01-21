@extends('layouts.app')

@section('page-title', 'Laporan Stok Semua Gudang')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Laporan Stok Semua Gudang</h4>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Data</h6>
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Gudang</label>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="select_all_warehouses">
                                    <label class="form-check-label fw-bold" for="select_all_warehouses">
                                        Pilih Semua
                                    </label>
                                </div>
                                <hr class="my-2">
                                @foreach($warehouses as $warehouse)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input warehouse-checkbox" 
                                               type="checkbox" 
                                               name="warehouse_ids[]" 
                                               value="{{ $warehouse->id }}"
                                               id="warehouse_{{ $warehouse->id }}"
                                               {{ in_array($warehouse->id, request('warehouse_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="warehouse_{{ $warehouse->id }}">
                                            {{ $warehouse->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Pilih satu atau beberapa gudang</div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="stock_status" class="form-label">Status Stok</label>
                            <select name="stock_status" id="stock_status" class="form-select">
                                <option value="all" {{ request('stock_status', 'all') === 'all' ? 'selected' : '' }}>All</option>
                                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Export Buttons Row -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="exportExcel()">
                                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                                </button>
                                <button type="button" class="btn btn-danger" onclick="exportPdf()">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Items</h6>
                        <h3 class="mb-0">{{ number_format($stocks->totalItems) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Stock</h6>
                        <h3 class="mb-0">{{ number_format($stocks->totalStock) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Low Stock Items</h6>
                        <h3 class="mb-0">{{ number_format($stocks->lowStockItems) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Out of Stock Items</h6>
                        <h3 class="mb-0">{{ number_format($stocks->outOfStockItems) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-table me-2"></i>Data Stok Barang</h6>
            </div>
            <div class="card-body">
                @if($stocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Warehouse</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Harga Terakhir</th>
                                    <th>Threshold</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                    @php
                                        // Get last purchase price for this item
                                        $lastPurchase = \App\Models\Submission::where('item_id', $stock->item_id)
                                            ->where('status', 'approved')
                                            ->whereNotNull('unit_price')
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                                    @endphp
                                    <tr>
                                        <td><code class="fs-6">{{ $stock->item_code }}</code></td>
                                        <td class="fw-medium">{{ $stock->item_name }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $stock->category_name }}</span>
                                        </td>
                                        <td>{{ $stock->warehouse_name }}</td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $stock->quantity <= $stock->min_threshold && $stock->quantity > 0 ? 'text-warning' : ($stock->quantity == 0 ? 'text-danger' : 'text-success') }}">
                                                {{ number_format($stock->quantity) }}
                                            </span>
                                        </td>
                                        <td>{{ $stock->item_unit }}</td>
                                        <td class="text-end">
                                            @if($lastPurchase)
                                                <small class="text-muted d-block">Rp {{ number_format($lastPurchase->unit_price, 0, ',', '.') }}</small>
                                                <small class="text-muted" style="font-size: 0.7rem;">{{ $lastPurchase->created_at->format('d/m/Y') }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($stock->min_threshold) }}</td>
                                        <td>
                                            @if($stock->quantity == 0)
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Out of Stock
                                                </span>
                                            @elseif($stock->quantity <= $stock->min_threshold)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Low Stock
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Normal
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $stocks->firstItem() }} - {{ $stocks->lastItem() }} dari {{ $stocks->total() }} data
                        </div>
                        <div>
                            {{ $stocks->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">Tidak Ada Data</h5>
                        <p class="text-muted mb-0">Tidak ditemukan data stok dengan filter yang dipilih.</p>
                        <a href="{{ route('admin.reports.stock-overview') }}" class="btn btn-outline-primary mt-3">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Filter
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all warehouses functionality
document.getElementById('select_all_warehouses').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.warehouse-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Update "select all" state when individual checkboxes change
document.querySelectorAll('.warehouse-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.warehouse-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.warehouse-checkbox:checked');
        document.getElementById('select_all_warehouses').checked = allCheckboxes.length === checkedCheckboxes.length;
    });
});

function exportExcel() {
    const form = document.getElementById('filterForm');
    const url = new URL('{{ route("admin.reports.stock-overview.export-excel") }}', window.location.origin);
    const formData = new FormData(form);
    
    for (let [key, value] of formData.entries()) {
        url.searchParams.append(key, value);
    }
    
    window.location.href = url.toString();
}

function exportPdf() {
    const form = document.getElementById('filterForm');
    const url = new URL('{{ route("admin.reports.stock-overview.export-pdf") }}', window.location.origin);
    const formData = new FormData(form);
    
    for (let [key, value] of formData.entries()) {
        url.searchParams.append(key, value);
    }
    
    window.location.href = url.toString();
}
</script>
@endpush

