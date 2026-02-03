@extends('layouts.app')

@section('page-title', 'Manajemen Barang')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Manajemen Barang</h4>
    </div>
</div>

@php
    $itemsWithoutSupplier = $items->filter(function($item) {
        return is_null($item->supplier_id);
    })->count();
@endphp

@if($itemsWithoutSupplier > 0)
<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
        <div>
            <strong>Info:</strong> Ada {{ $itemsWithoutSupplier }} item yang belum memiliki supplier. 
            Anda dapat mengedit item dan memilih supplier melalui tombol edit <i class="bi bi-pencil"></i>.
            <br>
            <small>Untuk mengelola supplier, kunjungi <a href="{{ route('admin.suppliers.index') }}" class="alert-link">halaman Supplier</a>.</small>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar Barang</h6>
        <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Barang
        </a>
    </div>

    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('admin.items.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Barang</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nama atau kode">
            </div>
            <div class="col-md-2">
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
                <label for="supplier_id" class="form-label">Supplier</label>
                <select class="form-select" id="supplier_id" name="supplier_id">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    <option value="discontinued" {{ request('status') === 'discontinued' ? 'selected' : '' }}>Tidak Diproduksi</option>
                    <option value="wrong_input" {{ request('status') === 'wrong_input' ? 'selected' : '' }}>Salah Input</option>
                    <option value="seasonal" {{ request('status') === 'seasonal' ? 'selected' : '' }}>Musiman</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>

        <!-- Items Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Satuan</th>
                        <th>Total Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <code>{{ $item->code }}</code>
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>
                                @if($item->category)
                                    <span class="badge bg-secondary">{{ $item->category->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->supplier->name ?? '-' }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>
                                @if($item->total_stock == 0)
                                    <span class="badge bg-danger">{{ number_format($item->total_stock) }}</span>
                                @else
                                    <span class="badge bg-success">{{ number_format($item->total_stock) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                    @if($item->inactive_reason === 'seasonal')
                                        <br><small class="text-muted">Musiman</small>
                                    @endif
                                @else
                                    @if($item->inactive_reason === 'discontinued')
                                        <span class="badge bg-danger">Tidak Diproduksi</span>
                                    @elseif($item->inactive_reason === 'wrong_input')
                                        <span class="badge bg-warning text-dark">Salah Input</span>
                                        @if($item->replaced_by_item_id)
                                            <br><small class="text-muted">→ Item #{{ $item->replaced_by_item_id }}</small>
                                        @endif
                                    @elseif($item->inactive_reason === 'seasonal')
                                        <span class="badge bg-info">Musiman (Off)</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.items.show', $item) }}"
                                       class="btn btn-outline-info"
                                       title="View Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.items.edit', $item) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-danger"
                                            onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-box text-muted fs-1 mb-3"></i>
                                <p class="text-muted mb-0">Tidak ada item ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $items->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menonaktifkan item <strong id="itemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Item yang memiliki stock tidak dapat dihapus.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Nonaktifkan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteItem(itemId, itemName) {
        document.getElementById('itemName').textContent = itemName;
        document.getElementById('deleteForm').action = `/admin/items/${itemId}`;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function showToast(message, type) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
</script>
@endpush