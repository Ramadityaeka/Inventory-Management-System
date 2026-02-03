@extends('layouts.app')

@section('page-title', 'Manajemen Unit')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Manajemen Unit</h4>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar Unit</h6>
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Unit
        </a>
    </div>

    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.warehouses.index') }}" class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="search" class="form-label">Cari Unit</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nama atau lokasi unit">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>

        <!-- Units Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Lokasi</th>
                        <th>PIC</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Total Barang</th>
                        <th>Total Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td>
                                <code>{{ $warehouse->code }}</code>
                            </td>
                            <td>{{ $warehouse->name }}</td>
                            <td>{{ $warehouse->location }}</td>
                            <td>{{ $warehouse->pic_name ?: '-' }}</td>
                            <td>{{ $warehouse->pic_phone ?: '-' }}</td>
                            <td>
                                @if($warehouse->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ number_format($warehouse->getTotalItemsAttribute()) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ number_format($warehouse->getTotalStockAttribute()) }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.warehouses.edit', $warehouse) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-danger"
                                            onclick="deleteWarehouse({{ $warehouse->id }}, '{{ $warehouse->name }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-building text-muted fs-1 mb-3"></i>
                                <p class="text-muted mb-0">Tidak ada unit ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($warehouses->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $warehouses->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
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
                <p>Apakah Anda yakin ingin menonaktifkan gudang <strong id="warehouseName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Gudang yang memiliki item tidak dapat dihapus.
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
    function deleteWarehouse(warehouseId, warehouseName) {
        document.getElementById('warehouseName').textContent = warehouseName;
        document.getElementById('deleteForm').action = `/admin/warehouses/${warehouseId}`;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
</script>
@endpush