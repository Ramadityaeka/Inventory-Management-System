@extends('layouts.app')

@section('page-title', 'Input Barang Masuk')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Input Barang Masuk</h4>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terjadi kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('staff.receive-items.store') }}" enctype="multipart/form-data" id="receiveItemForm">
    @csrf

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Informasi Barang</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="item_name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('item_name') is-invalid @enderror" 
                           id="item_name" name="item_name" value="{{ old('item_name') }}" 
                           placeholder="Ketik nama barang..." 
                           autocomplete="off" required>
                    <input type="hidden" id="item_id" name="item_id" value="{{ old('item_id') }}">
                    <div id="item-suggestions" class="list-group position-absolute" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                    <div class="form-text">Ketik untuk mencari dari database atau masukkan nama barang baru</div>
                    @error('item_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                           id="quantity" name="quantity" value="{{ old('quantity') }}" 
                           min="1" required>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror" 
                            id="unit" name="unit" required>
                        <option value="pcs" {{ old('unit', 'pcs') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>Unit</option>
                        <option value="pak" {{ old('unit') == 'pak' ? 'selected' : '' }}>Pak</option>
                        <option value="lusin" {{ old('unit') == 'lusin' ? 'selected' : '' }}>Lusin</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kg</option>
                        <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                    </select>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="unit_price_display" class="form-label">
                        <i class="bi bi-currency-dollar text-primary"></i> Harga per Satuan
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control @error('unit_price') is-invalid @enderror"
                               id="unit_price_display" 
                               value="{{ old('unit_price') ? number_format(old('unit_price'), 0, ',', '.') : '' }}" 
                               placeholder="0">
                        <input type="hidden" id="unit_price" name="unit_price" value="{{ old('unit_price') }}">
                    </div>
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i> Harga untuk setiap 1 satuan barang. Contoh: 10.000 atau 1.500.000
                    </div>
                    @error('unit_price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="total_price_display" class="form-label">
                        <i class="bi bi-calculator text-success"></i> Total Harga (Otomatis)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="text" class="form-control bg-light fw-bold" id="total_price_display" readonly 
                               placeholder="Akan dihitung otomatis">
                    </div>
                    <div class="form-text">
                        <i class="bi bi-calculator-fill"></i> Total = Quantity × Harga per Satuan
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="supplier_id" class="form-label">
                        Supplier 
                        <span class="text-danger" id="supplier-required">*</span>
                        <span class="text-muted small" id="supplier-optional" style="display: none;">(Opsional untuk barang keluar)</span>
                    </label>
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

                <div class="col-md-6 mb-3">
                    <label for="warehouse_id" class="form-label">Gudang <span class="text-danger">*</span></label>
                    <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                            id="warehouse_id" name="warehouse_id" required>
                        <option value="">Pilih Gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Upload Foto Nota/Invoice</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="invoice_photo" class="form-label">Foto Nota (Opsional)</label>
                <input type="file" class="form-control @error('invoice_photo') is-invalid @enderror" 
                       id="invoice_photo" name="invoice_photo" accept="image/*,application/pdf">
                <div class="form-text">Format: JPG, JPEG, PNG, PDF. Maksimal 5MB.</div>
                @error('invoice_photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div id="invoice-preview-container"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Upload Foto Barang</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="photos" class="form-label">Foto (Opsional, max 5 foto)</label>
                <input type="file" class="form-control @error('photos.*') is-invalid @enderror" 
                       id="photos" name="photos[]" accept="image/*" multiple>
                <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB per file.</div>
                @error('photos.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div id="preview-container" class="row g-2"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('staff.receive-items.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <div>
                    <button type="submit" name="is_draft" value="1" class="btn btn-outline-primary me-2">
                        <i class="bi bi-save me-1"></i>Simpan sebagai Draft
                    </button>
                    <button type="submit" name="is_draft" value="0" class="btn btn-success">
                        <i class="bi bi-send me-1"></i>Submit untuk Verifikasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Format number to Indonesian Rupiah format
    function formatRupiah(angka) {
        if (!angka) return '';
        
        // Remove all non-digit characters
        let number_string = angka.toString().replace(/[^,\d]/g, '');
        let split = number_string.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        return rupiah;
    }

    // Parse Indonesian format to number
    function parseRupiah(rupiah) {
        return parseInt(rupiah.replace(/\./g, '')) || 0;
    }

    // Unit price input formatting
    const unitPriceDisplay = document.getElementById('unit_price_display');
    const unitPriceHidden = document.getElementById('unit_price');
    
    unitPriceDisplay.addEventListener('input', function(e) {
        // Format the display value
        let formatted = formatRupiah(this.value);
        this.value = formatted;
        
        // Store numeric value in hidden field
        unitPriceHidden.value = parseRupiah(formatted);
        
        // Recalculate total
        calculateTotalPrice();
    });

    // Calculate total price automatically
    function calculateTotalPrice() {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const unitPrice = parseFloat(unitPriceHidden.value) || 0;
        const totalPrice = quantity * unitPrice;
        
        // Format total price
        if (totalPrice > 0) {
            document.getElementById('total_price_display').value = formatRupiah(totalPrice.toString());
        } else {
            document.getElementById('total_price_display').value = '';
        }
    }

    document.getElementById('quantity').addEventListener('input', calculateTotalPrice);

    // Preview invoice photo
    document.getElementById('invoice_photo').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('invoice-preview-container');
        previewContainer.innerHTML = '';
        
        const file = this.files[0];
        if (file) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <div class="card" style="max-width: 300px;">
                            <img src="${e.target.result}" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-file-pdf"></i> ${file.name} (PDF)
                    </div>
                `;
            }
        }
    });

    // Preview item photos
    document.getElementById('photos').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('preview-container');
        previewContainer.innerHTML = '';
        
        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3';
                    col.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Autocomplete for item name
    const itemNameInput = document.getElementById('item_name');
    const itemIdInput = document.getElementById('item_id');
    const suggestionsList = document.getElementById('item-suggestions');
    let debounceTimer;

    itemNameInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            suggestionsList.style.display = 'none';
            itemIdInput.value = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('staff.search-items') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionsList.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach(item => {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'list-group-item list-group-item-action';
                            button.innerHTML = `
                                <div class="d-flex w-100 justify-content-between">
                                    <strong>${item.name}</strong>
                                    <small class="text-muted">${item.category_name}</small>
                                </div>
                                <small>Kode: ${item.code}</small>
                            `;
                            button.addEventListener('click', function() {
                                itemNameInput.value = item.name;
                                itemIdInput.value = item.id;
                                suggestionsList.style.display = 'none';
                            });
                            suggestionsList.appendChild(button);
                        });
                        suggestionsList.style.display = 'block';
                    } else {
                        suggestionsList.innerHTML = '<div class="list-group-item text-muted">Tidak ada hasil. Anda bisa input nama barang baru.</div>';
                        suggestionsList.style.display = 'block';
                        itemIdInput.value = '';
                    }
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                    suggestionsList.style.display = 'none';
                });
        }, 300);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!itemNameInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // Clear item_id when manually typing
    itemNameInput.addEventListener('keydown', function() {
        if (itemIdInput.value) {
            itemIdInput.value = '';
        }
    });
</script>
@endpush
