@extends('layouts.app')

@section('page-title', 'Tambah Gudang')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Tambah Gudang Baru</h4>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Form Gudang</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.warehouses.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Code -->
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code"
                           value="{{ old('code', $nextCode ?? '') }}" readonly>
                    <div class="form-text">Code gudang akan di-generate otomatis</div>
                    @error('code')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Gudang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Location -->
                <div class="col-md-6 mb-3">
                    <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('location') is-invalid @enderror"
                           id="location" name="location" value="{{ old('location') }}" required>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- PIC Name -->
                <div class="col-md-6 mb-3">
                    <label for="pic_name" class="form-label">Nama PIC</label>
                    <input type="text" class="form-control @error('pic_name') is-invalid @enderror"
                           id="pic_name" name="pic_name" value="{{ old('pic_name') }}">
                    @error('pic_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- PIC Phone -->
                <div class="col-md-6 mb-3">
                    <label for="pic_phone" class="form-label">Telepon PIC</label>
                    <input type="tel" class="form-control @error('pic_phone') is-invalid @enderror"
                           id="pic_phone" name="pic_phone" value="{{ old('pic_phone') }}">
                    @error('pic_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Gudang Aktif
                        </label>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="mb-3">
                <label for="address" class="form-label">Alamat</label>
                <textarea class="form-control @error('address') is-invalid @enderror"
                          id="address" name="address" rows="3">{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
    // Auto-generate code preview (optional)
    document.getElementById('name').addEventListener('input', function() {
        // You can add code generation logic here if needed
    });
</script>
@endpush