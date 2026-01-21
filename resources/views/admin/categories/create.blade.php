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
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Code Prefix -->
                <div class="col-md-6 mb-3">
                    <label for="code_prefix" class="form-label">Kode Prefix <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code_prefix') is-invalid @enderror"
                           id="code_prefix" name="code_prefix" value="{{ old('code_prefix') }}" 
                           maxlength="5" placeholder="Contoh: MSE, KRT" required>
                    <div class="form-text">3-5 karakter untuk kode barang (contoh: MSE untuk Mouse, KRT untuk Kertas)</div>
                    @error('code_prefix')
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
@endsection