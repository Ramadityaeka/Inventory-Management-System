@extends('layouts.app')

@section('page-title', 'Tambah Barang')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Tambah Barang Baru</h4>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Form Barang</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.items.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Code (Auto-generated) -->
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Kode Barang</label>
                    <input type="text" class="form-control" id="code" name="code"
                           value="{{ old('code', 'INV-' . date('Y') . '-001') }}" readonly>
                    <div class="form-text">Kode akan di-generate otomatis</div>
                    @error('code')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Pulpen Snowman Hitam">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Category -->
                <div class="col-md-6 mb-3">
                    <label for="category_search" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="category_search" 
                               placeholder="Ketik untuk mencari kategori..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="bi bi-plus"></i> Baru
                        </button>
                    </div>
                    <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id') }}">
                    <div id="category_results" class="list-group mt-1" style="position: absolute; z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                    <small class="form-text text-muted">Pilih kategori yang ada atau buat sub-kategori baru</small>
                    @error('category_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                    <div id="selected_category" class="mt-2"></div>
                </div>

                <!-- Supplier -->
                <div class="col-md-6 mb-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select class="form-select @error('supplier_id') is-invalid @enderror"
                            id="supplier_id" name="supplier_id">
                        <option value="">Pilih Supplier (Opsional)</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Unit -->
                <div class="col-md-6 mb-3">
                    <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror"
                            id="unit" name="unit" required>
                        <option value="">Pilih Satuan</option>
                        <option value="Botol" {{ old('unit') == 'Botol' ? 'selected' : '' }}>Botol</option>
                        <option value="Buah" {{ old('unit') == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Box" {{ old('unit') == 'Box' ? 'selected' : '' }}>Box</option>
                        <option value="Dus" {{ old('unit') == 'Dus' ? 'selected' : '' }}>Dus</option>
                        <option value="Dus Besar" {{ old('unit') == 'Dus Besar' ? 'selected' : '' }}>Dus Besar</option>
                        <option value="Karton" {{ old('unit') == 'Karton' ? 'selected' : '' }}>Karton</option>
                        <option value="Kg" {{ old('unit') == 'Kg' ? 'selected' : '' }}>Kg</option>
                        <option value="Liter" {{ old('unit') == 'Liter' ? 'selected' : '' }}>Liter</option>
                        <option value="Lusin" {{ old('unit') == 'Lusin' ? 'selected' : '' }}>Lusin</option>
                        <option value="Meter" {{ old('unit') == 'Meter' ? 'selected' : '' }}>Meter</option>
                        <option value="Pack" {{ old('unit') == 'Pack' ? 'selected' : '' }}>Pack</option>
                        <option value="Pad" {{ old('unit') == 'Pad' ? 'selected' : '' }}>Pad</option>
                        <option value="Pasang" {{ old('unit') == 'Pasang' ? 'selected' : '' }}>Pasang</option>
                        <option value="Pcs" {{ old('unit', 'Pcs') == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="Rim" {{ old('unit') == 'Rim' ? 'selected' : '' }}>Rim</option>
                        <option value="Roll" {{ old('unit') == 'Roll' ? 'selected' : '' }}>Roll</option>
                        <option value="Sak" {{ old('unit') == 'Sak' ? 'selected' : '' }}>Sak</option>
                        <option value="Set" {{ old('unit') == 'Set' ? 'selected' : '' }}>Set</option>
                        <option value="Unit" {{ old('unit') == 'Unit' ? 'selected' : '' }}>Unit</option>
                    </select>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          id="description" name="description" rows="3" placeholder="Spesifikasi, merk, atau keterangan tambahan">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Is Active -->
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Barang Aktif
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for adding new category -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Sub-Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newCategoryForm">
                    <input type="hidden" id="modal_parent_id">
                    <div class="mb-3">
                        <label class="form-label">Kategori Induk</label>
                        <input type="text" class="form-control" id="modal_parent_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Baru (Auto)</label>
                        <input type="text" class="form-control" id="modal_new_code" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Sub-Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_category_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveNewCategory()">Simpan & Gunakan</button>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;
let selectedCategoryId = null;

document.getElementById('category_search').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const search = e.target.value;
    
    if (search.length < 2) {
        document.getElementById('category_results').style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.categories.search') }}?search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById('category_results');
                resultsDiv.innerHTML = '';
                
                if (data.length === 0) {
                    resultsDiv.innerHTML = '<div class="list-group-item">Kategori tidak ditemukan</div>';
                } else {
                    data.forEach(category => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        const indent = '　'.repeat(category.level); // Gunakan space karakter Jepang untuk indentasi visual
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div style="padding-left: ${category.level * 15}px;">
                                    ${category.level > 0 ? '<i class="bi bi-arrow-return-right text-muted me-1"></i>' : ''}
                                    <code class="text-primary">${category.code}</code> - <strong>${category.name}</strong>
                                    <br><small class="text-muted">Level ${category.level}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAsParent(${category.id}, '${category.code}', '${escapeHtml(category.name)}', event)">
                                    <i class="bi bi-plus"></i> Tambah Sub
                                </button>
                            </div>
                        `;
                        item.onclick = (e) => {
                            if (!e.target.closest('.btn')) {
                                selectCategory(category.id, category.full_name);
                                e.preventDefault();
                            }
                        };
                        resultsDiv.appendChild(item);
                    });
                }
                
                resultsDiv.style.display = 'block';
            });
    }, 300);
});

function selectCategory(id, name) {
    document.getElementById('category_id').value = id;
    document.getElementById('category_search').value = name;
    document.getElementById('selected_category').innerHTML = `
        <div class="alert alert-success py-2 mb-0">
            <i class="bi bi-check-circle"></i> Dipilih: <strong>${name}</strong>
        </div>
    `;
    document.getElementById('category_results').style.display = 'none';
    selectedCategoryId = id;
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function selectAsParent(parentId, parentCode, parentName, event) {
    event.preventDefault();
    event.stopPropagation();
    
    // Load modal with parent info
    document.getElementById('modal_parent_id').value = parentId;
    document.getElementById('modal_parent_name').value = `${parentCode} - ${parentName}`;
    
    // Generate new code
    fetch(`{{ route('admin.categories.generate-code') }}?parent_id=${parentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modal_new_code').value = data.code;
        });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    modal.show();
    
    document.getElementById('category_results').style.display = 'none';
}

function saveNewCategory() {
    const parentId = document.getElementById('modal_parent_id').value;
    const code = document.getElementById('modal_new_code').value;
    const name = document.getElementById('modal_category_name').value;
    
    if (!name) {
        alert('Nama kategori harus diisi');
        return;
    }
    
    // Save via AJAX
    fetch('{{ route('admin.categories.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            parent_id: parentId,
            code: code,
            name: name
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Select the newly created category
            selectCategory(data.category.id, `${data.category.code} - ${data.category.name}`);
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
            
            // Reset form
            document.getElementById('newCategoryForm').reset();
            
            alert('Sub-kategori berhasil dibuat dan dipilih!');
        } else {
            alert('Gagal membuat kategori: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan kategori');
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#category_search') && !e.target.closest('#category_results')) {
        document.getElementById('category_results').style.display = 'none';
    }
});
</script>
@endsection