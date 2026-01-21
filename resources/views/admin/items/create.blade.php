@extends('layouts.app')

@section('page-title', 'Tambah Item')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Tambah Item Baru</h4>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Form Item</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.items.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Code (Auto-generated) -->
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" class="form-control" id="code" name="code"
                           value="{{ old('code', 'INV-' . date('Y') . '-001') }}" readonly>
                    <div class="form-text">Code akan di-generate otomatis</div>
                    @error('code')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Item <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Category -->
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror"
                            id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Supplier -->
                <div class="col-md-6 mb-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select class="form-select @error('supplier_id') is-invalid @enderror"
                            id="supplier_id" name="supplier_id">
                        <option value="">Pilih Supplier</option>
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
                    <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror"
                            id="unit" name="unit" required>
                        <option value="">Pilih Unit</option>
                        <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs</option>
                        <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>box</option>
                        <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>unit</option>
                        <option value="rim" {{ old('unit') == 'rim' ? 'selected' : '' }}>rim</option>
                    </select>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Min Threshold -->
                <div class="col-md-6 mb-3">
                    <label for="min_threshold" class="form-label">Minimum Threshold <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('min_threshold') is-invalid @enderror"
                           id="min_threshold" name="min_threshold" value="{{ old('min_threshold', 10) }}"
                           min="0" required>
                    <div class="form-text">Jumlah minimum sebelum dianggap low stock</div>
                    @error('min_threshold')
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

            <!-- Is Active -->
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Item Aktif
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
@endsection