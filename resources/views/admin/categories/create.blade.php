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
                               placeholder="Contoh: 1.01.03" required oninput="checkCode()">
                        <button class="btn btn-outline-secondary" type="button" onclick="generateCode()">
                            <i class="bi bi-arrow-clockwise"></i> Auto
                        </button>
                    </div>
                    <div id="code_feedback" class="form-text">Kode bisa diubah secara manual. Klik Auto untuk saran kode berikutnya</div>
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
let _createCodeTimer = null;
let _createCodeAvailable = true;

function checkCode() {
    const code     = document.getElementById('code').value.trim();
    const codeEl   = document.getElementById('code');
    const feedback = document.getElementById('code_feedback');

    clearTimeout(_createCodeTimer);

    if (!code) {
        codeEl.classList.remove('is-valid', 'is-invalid');
        feedback.className = 'form-text';
        feedback.textContent = 'Kode bisa diubah secara manual. Klik Auto untuk saran kode berikutnya';
        _createCodeAvailable = true;
        return;
    }

    feedback.className = 'form-text text-muted';
    feedback.textContent = 'Memeriksa ketersediaan kode...';

    _createCodeTimer = setTimeout(() => {
        fetch(`{{ route('admin.categories.check-code') }}?code=${encodeURIComponent(code)}`)
            .then(r => r.json())
            .then(data => {
                _createCodeAvailable = data.available;
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
                _createCodeAvailable = true;
                codeEl.classList.remove('is-valid', 'is-invalid');
                feedback.className = 'form-text text-warning';
                feedback.textContent = 'Tidak bisa memeriksa kode. Lanjutkan dengan hati-hati.';
            });
    }, 400);
}

function generateCode() {
    const parentSelect = document.getElementById('parent_id');
    const codeInput = document.getElementById('code');
    const parentId = parentSelect.value;
    
    if (!parentId) {
        codeInput.value = '';
        codeInput.classList.remove('is-valid', 'is-invalid');
        return;
    }
    
    // Call API to generate next code
    fetch(`{{ route('admin.categories.generate-code') }}?parent_id=${parentId}`)
        .then(response => response.json())
        .then(data => {
            codeInput.value = data.code;
            checkCode(); // auto-check generated code
            const existing = document.getElementById('overflowWarning');
            if (existing) existing.remove();
            if (data.overflow) {
                const warn = document.createElement('div');
                warn.id = 'overflowWarning';
                warn.className = 'alert alert-warning mt-2';
                warn.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i><strong>Perhatian:</strong> Kode yang di-generate (<code>${data.code}</code>) melebihi batas nomor urut 999. Silakan isi kode secara manual dengan nomor yang masih tersedia.`;
                codeInput.closest('.col-md-6').appendChild(warn);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal generate kode otomatis');
        });
}

// Client-side validation before submit
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    const codeInput = document.getElementById('code');
    const parentId = document.getElementById('parent_id').value;

    // Block if code already taken
    if (!_createCodeAvailable) {
        e.preventDefault();
        const existing = document.getElementById('overflowWarning');
        if (existing) existing.remove();
        const warn = document.createElement('div');
        warn.id = 'overflowWarning';
        warn.className = 'alert alert-danger mt-2';
        warn.innerHTML = `<i class="bi bi-x-circle me-2"></i><strong>Tidak dapat disimpan.</strong> Kode "<strong>${codeInput.value}</strong>" sudah digunakan. Gunakan kode lain.`;
        codeInput.closest('.col-md-6').appendChild(warn);
        codeInput.focus();
        return;
    }

    if (parentId && codeInput.value) {
        const parts = codeInput.value.split('.');
        const lastSegment = parseInt(parts[parts.length - 1], 10);
        if (!isNaN(lastSegment) && lastSegment > 999) {
            e.preventDefault();
            const existing = document.getElementById('overflowWarning');
            if (existing) existing.remove();
            const warn = document.createElement('div');
            warn.id = 'overflowWarning';
            warn.className = 'alert alert-danger mt-2';
            warn.innerHTML = `<i class="bi bi-x-circle me-2"></i><strong>Tidak dapat disimpan.</strong> Nomor urut kode tidak boleh melebihi 999. Silakan ubah ke kode yang tersedia.`;
            codeInput.closest('.col-md-6').appendChild(warn);
            codeInput.focus();
        }
    }
});

// Auto-generate on load if parent is selected
document.addEventListener('DOMContentLoaded', function() {
    const parentId = document.getElementById('parent_id').value;
    if (parentId) {
        generateCode();
    }
    // Check old value if any (after validation error redirect)
    const codeVal = document.getElementById('code').value;
    if (codeVal) checkCode();
});
</script>
@endsection