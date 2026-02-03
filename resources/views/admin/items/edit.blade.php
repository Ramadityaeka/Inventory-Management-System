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
                </div>n

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
                <!-- Satuan Dasar -->
                <div class="col-md-6 mb-3">
                    <label for="unit" class="form-label">Satuan Dasar <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror"
                            id="unit" name="unit" required>
                        <option value="">Pilih Satuan</option>
                        <option value="Botol" {{ old('unit', $item->unit) == 'Botol' ? 'selected' : '' }}>Botol</option>
                        <option value="Buah" {{ old('unit', $item->unit) == 'Buah' ? 'selected' : '' }}>Buah</option>
                        <option value="Box" {{ old('unit', $item->unit) == 'Box' ? 'selected' : '' }}>Box</option>
                        <option value="Dus" {{ old('unit', $item->unit) == 'Dus' ? 'selected' : '' }}>Dus</option>
                        <option value="Dus Besar" {{ old('unit', $item->unit) == 'Dus Besar' ? 'selected' : '' }}>Dus Besar</option>
                        <option value="Karton" {{ old('unit', $item->unit) == 'Karton' ? 'selected' : '' }}>Karton</option>
                        <option value="Kg" {{ old('unit', $item->unit) == 'Kg' ? 'selected' : '' }}>Kg</option>
                        <option value="Liter" {{ old('unit', $item->unit) == 'Liter' ? 'selected' : '' }}>Liter</option>
                        <option value="Lusin" {{ old('unit', $item->unit) == 'Lusin' ? 'selected' : '' }}>Lusin</option>
                        <option value="Meter" {{ old('unit', $item->unit) == 'Meter' ? 'selected' : '' }}>Meter</option>
                        <option value="Pack" {{ old('unit', $item->unit) == 'Pack' ? 'selected' : '' }}>Pack</option>
                        <option value="Pad" {{ old('unit', $item->unit) == 'Pad' ? 'selected' : '' }}>Pad</option>
                        <option value="Pasang" {{ old('unit', $item->unit) == 'Pasang' ? 'selected' : '' }}>Pasang</option>
                        <option value="Pcs" {{ old('unit', $item->unit) == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="Rim" {{ old('unit', $item->unit) == 'Rim' ? 'selected' : '' }}>Rim</option>
                        <option value="Roll" {{ old('unit', $item->unit) == 'Roll' ? 'selected' : '' }}>Roll</option>
                        <option value="Sak" {{ old('unit', $item->unit) == 'Sak' ? 'selected' : '' }}>Sak</option>
                        <option value="Set" {{ old('unit', $item->unit) == 'Set' ? 'selected' : '' }}>Set</option>
                        <option value="Unit" {{ old('unit', $item->unit) == 'Unit' ? 'selected' : '' }}>Unit</option>
                    </select>
                    <div class="form-text">Satuan terkecil untuk menghitung stok. Semua stok akan dikonversi ke satuan ini.</div>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Satuan Tambahan (Konversi)</label>
                    @if($item->itemUnits && $item->itemUnits->count() > 0)
                        <div class="mb-2">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Satuan</th>
                                        <th>Isi (konversi ke {{ $item->unit }})</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->itemUnits as $iu)
                                        <tr>
                                            <td>{{ $iu->name }}</td>
                                            <td>{{ $iu->conversion_factor }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted mb-2">Belum ada satuan tambahan untuk item ini.</div>
                    @endif

                    <form action="{{ route('admin.items.units.store', $item) }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-auto">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Nama satuan (mis. Box)" required>
                        </div>
                        <div class="col-auto" style="width:160px;">
                            <input type="number" name="conversion_factor" class="form-control form-control-sm" placeholder="Isi (angka)" min="1" required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-outline-primary">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Unit Management Section -->
            <div class="card mb-3 border-info">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="mb-0">
                        <i class="bi bi-box-seam me-1"></i>Pengaturan Satuan Alternatif
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle"></i> 
                        Tambahkan satuan alternatif yang dapat digunakan saat input barang masuk. 
                        Sistem akan otomatis mengkonversi ke satuan dasar saat menghitung stok.
                    </p>

                    <!-- Existing Units -->
                    <div class="mb-3">
                        <h6 class="mb-2">Satuan yang Tersedia:</h6>
                        @if($item->units->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nama Satuan</th>
                                            <th>Faktor Konversi</th>
                                            <th>Keterangan</th>
                                            <th width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($item->units as $unit)
                                            <tr id="unit-row-{{ $unit->id }}">
                                                <td>
                                                    <strong>{{ $unit->name }}</strong>
                                                </td>
                                                <td>
                                                    <code>{{ $unit->conversion_factor }}</code>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        1 {{ $unit->name }} = {{ $unit->conversion_factor }} {{ $item->unit }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editUnit({{ $unit->id }}, '{{ $unit->name }}', {{ $unit->conversion_factor }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('admin.items.units.destroy', [$item, $unit]) }}" 
                                                          method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Yakin ingin menghapus satuan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <small><i class="bi bi-info-circle"></i> Belum ada satuan alternatif. Tambahkan satuan di bawah.</small>
                            </div>
                        @endif
                    </div>

                    <!-- Add/Edit Unit Form -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3" id="unit-form-title">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Satuan Baru
                            </h6>
                            <form id="unit-form" method="POST" action="{{ route('admin.items.units.store', $item) }}">
                                @csrf
                                <input type="hidden" id="unit-id" name="unit_id">
                                <input type="hidden" id="unit-method" name="_method" value="POST">
                                
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label for="unit-name" class="form-label small">Nama Satuan</label>
                                        <input type="text" class="form-control form-control-sm" 
                                               id="unit-name" name="name" 
                                               placeholder="Contoh: Box, Pack, Dus" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="unit-factor" class="form-label small">Faktor Konversi</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               id="unit-factor" name="conversion_factor" 
                                               min="1" placeholder="Contoh: 12" required>
                                        <small class="text-muted">Berapa {{ $item->unit }} dalam 1 satuan ini?</small>
                                    </div>
                                    <div class="col-md-4 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-sm me-2" id="unit-submit-btn">
                                            <i class="bi bi-check"></i> Simpan
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" id="unit-cancel-btn" 
                                                onclick="resetUnitForm()" style="display: none;">
                                            <i class="bi bi-x"></i> Batal
                                        </button>
                                    </div>
                                </div>
                                @if($errors->has('name') || $errors->has('conversion_factor'))
                                    <div class="alert alert-danger mt-2 mb-0">
                                        @foreach($errors->get('name') as $error)
                                            <small>{{ $error }}</small><br>
                                        @endforeach
                                        @foreach($errors->get('conversion_factor') as $error)
                                            <small>{{ $error }}</small><br>
                                        @endforeach
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
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
                                    <strong>Dinonaktifkan:</strong> {{ formatDateIndoLong($item->deactivated_at) }} WIB<br>
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

// Unit Management Functions
function editUnit(id, name, factor) {
    document.getElementById('unit-id').value = id;
    document.getElementById('unit-name').value = name;
    document.getElementById('unit-factor').value = factor;
    document.getElementById('unit-form-title').innerHTML = '<i class="bi bi-pencil me-1"></i>Edit Satuan';
    document.getElementById('unit-submit-btn').innerHTML = '<i class="bi bi-check"></i> Update';
    document.getElementById('unit-cancel-btn').style.display = 'inline-block';
    
    // Update form action
    const form = document.getElementById('unit-form');
    form.action = '{{ route("admin.items.units.update", [$item, ":id"]) }}'.replace(':id', id);
    form.querySelector('[name="_method"]').value = 'PUT';
    
    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function resetUnitForm() {
    document.getElementById('unit-id').value = '';
    document.getElementById('unit-name').value = '';
    document.getElementById('unit-factor').value = '';
    document.getElementById('unit-form-title').innerHTML = '<i class="bi bi-plus-circle me-1"></i>Tambah Satuan Baru';
    document.getElementById('unit-submit-btn').innerHTML = '<i class="bi bi-check"></i> Simpan';
    document.getElementById('unit-cancel-btn').style.display = 'none';
    
    // Reset form action
    const form = document.getElementById('unit-form');
    form.action = '{{ route("admin.items.units.store", $item) }}';
    form.querySelector('[name="_method"]').value = 'POST';
}
</script>
@endpush
@endsection