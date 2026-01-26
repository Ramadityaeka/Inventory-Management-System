@extends('layouts.app')

@section('page-title', 'Tambah Kategori')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Tambah Kategori Baru</h4>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Form Kategori</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST" id="categoryForm">
            @csrf

            <div class="row">
                <!-- Parent Category -->
                <div class="col-md-6 mb-3">
                    <label for="parent_id" class="form-label">Kategori Induk</label>
                    <select class="form-select @error('parent_id') is-invalid @enderror"
                            id="parent_id" name="parent_id" onchange="generateCode()">
                        <option value="">-- Kategori Utama (Root) --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    data-code="{{ $category->code }}"
                                    data-level="{{ $category->level }}"
                                    {{ old('parent_id', $parentId) == $category->id ? 'selected' : '' }}>
                                {{ str_repeat('  ', $category->level) }}{{ $category->code }} - {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Kosongkan jika ini adalah kategori utama</div>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Code -->
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                               id="code" name="code" value="{{ old('code') }}" 
                               placeholder="Contoh: 1.01.03" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="generateCode()">
                            <i class="bi bi-arrow-clockwise"></i> Auto
                        </button>
                    </div>
                    <div class="form-text">Kode otomatis dibuat jika memilih kategori induk</div>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Name -->
                <div class="col-md-12 mb-3">
                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function generateCode() {
    const parentSelect = document.getElementById('parent_id');
    const codeInput = document.getElementById('code');
    const parentId = parentSelect.value;
    
    if (!parentId) {
        codeInput.value = '';
        codeInput.readOnly = false;
        return;
    }
    
    // Get parent code from selected option
    const selectedOption = parentSelect.options[parentSelect.selectedIndex];
    const parentCode = selectedOption.getAttribute('data-code');
    
    // Call API to generate next code
    fetch(`{{ route('admin.categories.generate-code') }}?parent_id=${parentId}`)
        .then(response => response.json())
        .then(data => {
            codeInput.value = data.code;
            codeInput.readOnly = true;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal generate kode otomatis');
        });
}

// Auto-generate on load if parent is selected
document.addEventListener('DOMContentLoaded', function() {
    const parentId = document.getElementById('parent_id').value;
    if (parentId) {
        generateCode();
    }
});
</script>
@endsection