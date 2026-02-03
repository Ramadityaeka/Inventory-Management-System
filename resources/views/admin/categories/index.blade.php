@extends('layouts.app')

@section('page-title', 'Manajemen Kategori')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Manajemen Kategori</h4>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar Kategori</h6>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Kategori
        </a>
    </div>

    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="search" class="form-label">Cari Kategori</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nama atau deskripsi kategori">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>

        <!-- Categories Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Induk</th>
                        <th>Total Barang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><code class="text-primary">{{ $category->code }}</code></td>
                            <td>
                                <div style="padding-left: {{ $category->level * 20 }}px;">
                                    @if($category->level > 0)
                                        <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                    @endif
                                    <strong>{{ $category->name }}</strong>
                                </div>
                            </td>
                            <td>
                                @if($category->parent)
                                    <small class="text-muted">{{ $category->parent->code }}</small><br>
                                    {{ $category->parent->name }}
                                @else
                                    <span class="badge bg-primary">Root</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info" title="Jumlah barang aktif dalam kategori ini">
                                    <i class="bi bi-box me-1"></i>{{ $category->items_count }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}"
                                       class="btn btn-outline-success"
                                       title="Tambah Sub-Kategori">
                                        <i class="bi bi-plus"></i>
                                    </a>
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-danger"
                                            onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-tag text-muted fs-1 mb-3"></i>
                                <p class="text-muted mb-0">Tidak ada kategori ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($categories->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $categories->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
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
                <p>Apakah Anda yakin ingin menonaktifkan kategori <strong id="categoryName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Kategori yang memiliki item tidak dapat dihapus.
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
    function deleteCategory(categoryId, categoryName) {
        document.getElementById('categoryName').textContent = categoryName;
        document.getElementById('deleteForm').action = `/admin/categories/${categoryId}`;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
</script>
@endpush