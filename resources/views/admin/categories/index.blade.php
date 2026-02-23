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
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle me-2"></i>Tambah Kategori
        </button>
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
                        <th>Total Stock</th>
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
                                @if($category->total_stock > 0)
                                    <span class="badge bg-success">{{ number_format($category->total_stock) }}</span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
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
                                    @if($category->total_stock > 0 || $category->children_count > 0)
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                disabled
                                                title="{{ $category->total_stock > 0 ? 'Tidak bisa dihapus — masih ada stock (' . number_format($category->total_stock) . ')' : 'Tidak bisa dihapus — masih ada sub-kategori' }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                onclick="deleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->items_count }})"
                                                title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
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

<!-- Create Category Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCategoryForm" action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori Induk</label>
                            <select class="form-select" id="modal_parent_id" name="parent_id" onchange="modalGenerateCode()">
                                <option value="">-- Kategori Utama (Root) --</option>
                                @foreach($allCategories as $cat)
                                    <option value="{{ $cat->id }}" data-code="{{ $cat->code }}">
                                        {{ str_repeat('　', $cat->level) }}{{ $cat->code }} - {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Kosongkan jika ini kategori utama</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="modal_code" name="code"
                                       placeholder="Contoh: 1.01.03" required oninput="checkModalCode()">
                                <button class="btn btn-outline-secondary" type="button" onclick="modalGenerateCode()" title="Saran kode otomatis">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div id="modal_code_feedback" class="form-text">Kode bisa diubah secara manual. Klik <i class="bi bi-arrow-clockwise"></i> untuk saran kode berikutnya.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="Nama kategori">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Opsional"></textarea>
                    </div>
                    <div id="createAlertArea"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus kategori <strong id="categoryName"></strong>?</p>
                <div id="deleteItemsInfo" class="alert alert-info d-none">
                    <i class="bi bi-info-circle me-2"></i>
                    Kategori ini memiliki <strong id="deleteItemCount"></strong> barang dengan stock kosong.
                    Barang-barang tersebut akan menjadi tanpa kategori setelah dihapus.
                </div>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Tindakan ini tidak dapat dibatalkan. Kategori akan dihapus secara permanen.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteCategory(categoryId, categoryName, itemCount) {
        document.getElementById('categoryName').textContent = categoryName;
        document.getElementById('deleteForm').action = `/admin/categories/${categoryId}`;

        const infoBox = document.getElementById('deleteItemsInfo');
        if (itemCount > 0) {
            document.getElementById('deleteItemCount').textContent = itemCount;
            infoBox.classList.remove('d-none');
        } else {
            infoBox.classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function modalGenerateCode() {
        const parentSelect = document.getElementById('modal_parent_id');
        const codeInput = document.getElementById('modal_code');
        const alertArea = document.getElementById('createAlertArea');
        const parentId = parentSelect.value;

        alertArea.innerHTML = '';

        if (!parentId) {
            codeInput.value = '';
            codeInput.classList.remove('is-valid', 'is-invalid');
            document.getElementById('modal_code_feedback').className = 'form-text';
            document.getElementById('modal_code_feedback').textContent = 'Kode bisa diubah secara manual. Klik ↻ untuk saran kode berikutnya.';
            return;
        }

        fetch(`{{ route('admin.categories.generate-code') }}?parent_id=${parentId}`)
            .then(response => response.json())
            .then(data => {
                codeInput.value = data.code;
                checkModalCode(); // auto-check the generated code
                if (data.overflow) {
                    alertArea.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Kode yang di-generate (<code>${data.code}</code>) melebihi batas nomor urut 999.
                            Silakan isi kode secara manual dengan nomor yang masih tersedia (contoh: kode yang sudah dihapus).
                        </div>`;
                } else {
                    alertArea.innerHTML = `
                        <div class="alert alert-success alert-sm py-2">
                            <i class="bi bi-check-circle me-2"></i>
                            Kode <code>${data.code}</code> disarankan. Anda dapat menggantinya secara manual.
                        </div>`;
                }
            })
            .catch(() => {
                alertArea.innerHTML =
                    '<div class="alert alert-warning">Gagal generate kode otomatis. Isi manual.</div>';
            });
    }

    let _codeCheckTimer = null;
    let _codeAvailable  = true;

    function checkModalCode() {
        const code     = document.getElementById('modal_code').value.trim();
        const codeEl   = document.getElementById('modal_code');
        const feedback = document.getElementById('modal_code_feedback');

        clearTimeout(_codeCheckTimer);

        if (!code) {
            codeEl.classList.remove('is-valid', 'is-invalid');
            feedback.className = 'form-text';
            feedback.textContent = 'Kode bisa diubah secara manual. Klik ↻ untuk saran kode berikutnya.';
            _codeAvailable = true;
            return;
        }

        feedback.className = 'form-text text-muted';
        feedback.textContent = 'Memeriksa ketersediaan kode...';

        _codeCheckTimer = setTimeout(() => {
            fetch(`{{ route('admin.categories.check-code') }}?code=${encodeURIComponent(code)}`)
                .then(r => r.json())
                .then(data => {
                    _codeAvailable = data.available;
                    if (data.available) {
                        codeEl.classList.remove('is-invalid');
                        codeEl.classList.add('is-valid');
                        feedback.className = 'form-text text-success';
                        feedback.textContent = '✓ Kode tersedia dan bisa digunakan.';
                    } else {
                        codeEl.classList.remove('is-valid');
                        codeEl.classList.add('is-invalid');
                        feedback.className = 'form-text text-danger';
                        feedback.textContent = '✗ Kode "' + code + '" sudah digunakan. Pilih kode lain.';
                    }
                })
                .catch(() => {
                    _codeAvailable = true;
                    codeEl.classList.remove('is-valid', 'is-invalid');
                    feedback.className = 'form-text text-warning';
                    feedback.textContent = 'Tidak bisa memeriksa kode. Lanjutkan dengan hati-hati.';
                });
        }, 400);
    }

    // Client-side validation before submit
    document.getElementById('createCategoryForm').addEventListener('submit', function (e) {
        const codeInput = document.getElementById('modal_code');
        const parentId = document.getElementById('modal_parent_id').value;
        const alertArea = document.getElementById('createAlertArea');

        // Block if code already taken
        if (!_codeAvailable) {
            e.preventDefault();
            alertArea.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    <strong>Tidak dapat disimpan.</strong> Kode "<strong>${codeInput.value}</strong>" sudah digunakan oleh kategori lain.
                    Gunakan kode yang berbeda.
                </div>`;
            codeInput.focus();
            return;
        }

        if (parentId && codeInput.value) {
            const parts = codeInput.value.split('.');
            const lastSegment = parseInt(parts[parts.length - 1], 10);
            if (!isNaN(lastSegment) && lastSegment > 999) {
                e.preventDefault();
                alertArea.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle me-2"></i>
                        <strong>Tidak dapat disimpan.</strong> Nomor urut kode tidak boleh melebihi 999.
                        Silakan ubah kode ke nomor yang tersedia.
                    </div>`;
                codeInput.focus();
                return;
            }
        }
    });

    // Reset modal when closed
    document.getElementById('createModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('createCategoryForm').reset();
        document.getElementById('modal_code').classList.remove('is-valid', 'is-invalid');
        document.getElementById('modal_code_feedback').className = 'form-text';
        document.getElementById('modal_code_feedback').textContent = 'Kode bisa diubah secara manual. Klik ↻ untuk saran kode berikutnya.';
        document.getElementById('createAlertArea').innerHTML = '';
        _codeAvailable = true;
    });
</script>
@endpush