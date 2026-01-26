@extends('layouts.app')

@section('page-title', 'Edit Item')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Edit Item</h4>
    </div>
</div>

<!-- Info Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="card-title text-primary">{{ number_format($item->stocks->sum('quantity')) }}</h5>
                <p class="card-text text-muted mb-0">Total Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="card-title text-info">{{ $item->stocks->count() }}</h5>
                <p class="card-text text-muted mb-0">Warehouses</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="card-title text-warning">-</h5>
                <p class="card-text text-muted mb-0">-</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Form Item</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.items.update', $item) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Code (Readonly) -->
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" class="form-control" id="code" name="code"
                           value="{{ old('code', $item->code) }}" readonly>
                    <div class="form-text">Code tidak dapat diubah</div>
                    @error('code')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Item <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $item->name) }}" required>
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
                            <option value="{{ $category->id }}"
                                    {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
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
                            <option value="{{ $supplier->id }}"
                                    {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
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
                <div class="col-md-12 mb-3">
                    <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror"
                            id="unit" name="unit" required>
                        <option value="">Pilih Unit</option>
                        <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
                        <option value="box" {{ old('unit', $item->unit) == 'box' ? 'selected' : '' }}>box</option>
                        <option value="unit" {{ old('unit', $item->unit) == 'unit' ? 'selected' : '' }}>unit</option>
                        <option value="rim" {{ old('unit', $item->unit) == 'rim' ? 'selected' : '' }}>rim</option>
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
                          id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Is Active -->
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $item->is_active) ? 'checked' : '' }}
                           onchange="toggleInactiveFields()">
                    <label class="form-check-label" for="is_active">
                        Item Aktif
                    </label>
                </div>
            </div>

            <!-- Inactive Reason Fields (shown when inactive) -->
            <div id="inactive-fields" style="{{ old('is_active', $item->is_active) ? 'display:none;' : '' }}">
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h6 class="mb-0">Alasan Nonaktif</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="inactive_reason" class="form-label">Tipe Nonaktif</label>
                            <select class="form-select" id="inactive_reason" name="inactive_reason" onchange="handleInactiveReasonChange()">
                                <option value="">Pilih Tipe</option>
                                <option value="discontinued" {{ old('inactive_reason', $item->inactive_reason) == 'discontinued' ? 'selected' : '' }}>
                                    Tidak Diproduksi Lagi
                                </option>
                                <option value="wrong_input" {{ old('inactive_reason', $item->inactive_reason) == 'wrong_input' ? 'selected' : '' }}>
                                    Salah Input
                                </option>
                                <option value="seasonal" {{ old('inactive_reason', $item->inactive_reason) == 'seasonal' ? 'selected' : '' }}>
                                    Barang Musiman
                                </option>
                            </select>
                            <small class="text-muted">
                                <strong>Discontinued:</strong> Stok akan dikosongkan otomatis via adjustment.<br>
                                <strong>Salah Input:</strong> Buat item baru dengan data benar, lalu mapping di sini.<br>
                                <strong>Musiman:</strong> Bisa diaktifkan/nonaktifkan sesuai musim.
                            </small>
                        </div>

                        <!-- Replacement Item (for wrong_input) -->
                        <div id="replacement-field" style="{{ old('inactive_reason', $item->inactive_reason) == 'wrong_input' ? '' : 'display:none;' }}">
                            <div class="mb-3">
                                <label for="replaced_by_item_id" class="form-label">Item Pengganti (Opsional)</label>
                                <select class="form-select" id="replaced_by_item_id" name="replaced_by_item_id">
                                    <option value="">Pilih Item Pengganti</option>
                                    @foreach(\App\Models\Item::where('is_active', true)->orderBy('name')->get() as $activeItem)
                                        @if($activeItem->id != $item->id)
                                            <option value="{{ $activeItem->id }}" {{ old('replaced_by_item_id', $item->replaced_by_item_id) == $activeItem->id ? 'selected' : '' }}>
                                                {{ $activeItem->code }} - {{ $activeItem->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih item baru yang menggantikan item salah ini</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="inactive_notes" class="form-label">Catatan</label>
                            <textarea class="form-control" id="inactive_notes" name="inactive_notes" rows="3" placeholder="Tambahkan catatan mengapa item dinonaktifkan...">{{ old('inactive_notes', $item->inactive_notes) }}</textarea>
                        </div>

                        @if($item->deactivated_at)
                            <div class="alert alert-info mb-0">
                                <small>
                                    <strong>Dinonaktifkan:</strong> {{ $item->deactivated_at->format('d/m/Y H:i') }}<br>
                                    @if($item->deactivatedBy)
                                        <strong>Oleh:</strong> {{ $item->deactivatedBy->name }}
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleInactiveFields() {
    const isActive = document.getElementById('is_active').checked;
    const inactiveFields = document.getElementById('inactive-fields');
    inactiveFields.style.display = isActive ? 'none' : 'block';
    
    if (isActive) {
        // Clear inactive reason when activating
        document.getElementById('inactive_reason').value = '';
        document.getElementById('inactive_notes').value = '';
        document.getElementById('replaced_by_item_id').value = '';
    }
}

function handleInactiveReasonChange() {
    const reason = document.getElementById('inactive_reason').value;
    const replacementField = document.getElementById('replacement-field');
    
    if (reason === 'wrong_input') {
        replacementField.style.display = 'block';
    } else {
        replacementField.style.display = 'none';
        document.getElementById('replaced_by_item_id').value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    handleInactiveReasonChange();
});
</script>
@endpush
@endsection