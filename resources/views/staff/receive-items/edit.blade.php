@extends('layouts.app')

@section('page-title', 'Edit Draft Submission')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Edit Draft Submission #{{ $submission->id }}</h4>
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

<form method="POST" action="{{ route('staff.receive-items.update', $submission) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Informasi Barang</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="item_name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('item_name') is-invalid @enderror" 
                           id="item_name" name="item_name" value="{{ old('item_name', $submission->item_name ?? $submission->item->name) }}" 
                           placeholder="Ketik nama barang..." 
                           autocomplete="off" required>
                    <input type="hidden" id="item_id" name="item_id" value="{{ old('item_id', $submission->item_id) }}">
                    <div id="item-suggestions" class="list-group position-absolute" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                    <div class="form-text">Ketik untuk mencari dari database atau masukkan nama barang baru</div>
                    @error('item_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                           id="quantity" name="quantity" 
                           value="{{ old('quantity', $submission->quantity) }}" 
                           min="1" required>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror" 
                            id="unit" name="unit" required>
                        <option value="">Pilih Satuan</option>
                        <!-- Options will be dynamically loaded based on selected item -->
                    </select>
                    <input type="hidden" id="conversion_factor" name="conversion_factor" value="{{ old('conversion_factor', $submission->conversion_factor ?? 1) }}">
                    <div class="form-text" id="unit-help-text">
                        <i class="bi bi-info-circle"></i> Loading satuan...
                    </div>
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
                               value="{{ old('unit_price', $submission->unit_price) ? number_format(old('unit_price', $submission->unit_price), 0, ',', '.') : '' }}" 
                               placeholder="0">
                        <input type="hidden" id="unit_price" name="unit_price" value="{{ old('unit_price', $submission->unit_price) }}">
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
                               placeholder="Akan dihitung otomatis" 
                               value="{{ $submission->total_price ? number_format($submission->total_price, 0, ',', '.') : '' }}">
                    </div>
                    <div class="form-text">
                        <i class="bi bi-calculator-fill"></i> Total = Quantity × Harga per Satuan
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select class="form-select @error('supplier_id') is-invalid @enderror" 
                            id="supplier_id" name="supplier_id" required>
                        <option value="">Pilih Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" 
                                {{ old('supplier_id', $submission->supplier_id) == $supplier->id ? 'selected' : '' }}>
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
                            <option value="{{ $warehouse->id }}" 
                                {{ old('warehouse_id', $submission->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
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
                              id="notes" name="notes" rows="3">{{ old('notes', $submission->notes) }}</textarea>
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
            @if($submission->invoice_photo)
                <div class="alert alert-info mb-3">
                    Foto nota saat ini: 
                    <a href="{{ asset('storage/' . $submission->invoice_photo) }}" target="_blank">
                        {{ basename($submission->invoice_photo) }}
                    </a>
                </div>
            @endif
            <div class="mb-3">
                <label for="invoice_photo" class="form-label">Foto Nota/Invoice</label>
                <input type="file" class="form-control @error('invoice_photo') is-invalid @enderror" 
                       id="invoice_photo" name="invoice_photo" accept="image/*,application/pdf">
                <div class="form-text">Format: JPG, PNG, PDF. Max 5MB. Upload baru akan mengganti foto lama.</div>
                @error('invoice_photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div id="invoice-preview-container"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Foto Barang</h6>
        </div>
        <div class="card-body">
            @if($submission->photos->count() > 0)
                <div class="mb-3">
                    <label class="form-label">Foto yang sudah diupload:</label>
                    <div class="row g-2">
                        @foreach($submission->photos as $photo)
                            <div class="col-md-3">
                                <div class="card">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                         class="card-img-top" 
                                         style="height: 150px; object-fit: cover;">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-3">
                <label for="photos" class="form-label">Tambah Foto Baru (Opsional)</label>
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
                <a href="{{ route('staff.drafts') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <div>
                    <button type="submit" name="is_draft" value="1" class="btn btn-outline-primary me-2">
                        <i class="bi bi-save me-1"></i>Update Draft
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

    // Auto-calculate total price
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
        const file = this.files[0];
        const previewContainer = document.getElementById('invoice-preview-container');
        previewContainer.innerHTML = '';

        if (file) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <div class="card" style="max-width: 300px;">
                            <img src="${e.target.result}" class="card-img-top">
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
            resetUnitDropdown();
        }
    });

    // Load units for selected item
    function loadItemUnits(itemId) {
        if (!itemId) {
            resetUnitDropdown();
            return;
        }

        fetch(`/staff/api/item-units?item_id=${itemId}`)
            .then(response => response.json())
            .then(data => {
                const unitSelect = document.getElementById('unit');
                const unitHelpText = document.getElementById('unit-help-text');
                const conversionFactorInput = document.getElementById('conversion_factor');
                const currentUnit = '{{ old("unit", $submission->unit ?? "") }}';
                
                // Clear existing options
                unitSelect.innerHTML = '<option value="">Pilih Satuan</option>';
                
                if (data.units && data.units.length > 0) {
                    // Add units to dropdown
                    data.units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.name;
                        option.textContent = unit.name;
                        option.dataset.conversionFactor = unit.conversion_factor;
                        
                        // Select current unit if it matches
                        if (unit.name === currentUnit) {
                            option.selected = true;
                            conversionFactorInput.value = unit.conversion_factor;
                        }
                        
                        unitSelect.appendChild(option);
                    });
                    
                    // If no unit selected, select base unit
                    if (!unitSelect.value && data.units.length > 0) {
                        const baseUnit = data.units.find(u => u.is_base);
                        if (baseUnit) {
                            unitSelect.value = baseUnit.name;
                            conversionFactorInput.value = baseUnit.conversion_factor;
                        }
                    }
                    
                    // Update help text
                    unitHelpText.innerHTML = `
                        <i class="bi bi-info-circle"></i> 
                        Satuan yang tersedia untuk barang ini. 
                        Satuan dasar: <strong>${data.base_unit}</strong>
                    `;
                } else {
                    resetUnitDropdown();
                    unitHelpText.innerHTML = `
                        <i class="bi bi-info-circle"></i> 
                        Barang ini belum memiliki satuan khusus. 
                        Gunakan satuan dasar: <strong>${data.base_unit || 'Pcs'}</strong>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading units:', error);
                resetUnitDropdown();
            });
    }

    // Reset unit dropdown to default
    function resetUnitDropdown() {
        const unitSelect = document.getElementById('unit');
        const unitHelpText = document.getElementById('unit-help-text');
        const conversionFactorInput = document.getElementById('conversion_factor');
        
        unitSelect.innerHTML = `
            <option value="">Pilih Satuan</option>
            <option value="Botol">Botol</option>
            <option value="Buah">Buah</option>
            <option value="Box">Box</option>
            <option value="Dus">Dus</option>
            <option value="Dus Besar">Dus Besar</option>
            <option value="Karton">Karton</option>
            <option value="Kg">Kg</option>
            <option value="Liter">Liter</option>
            <option value="Lusin">Lusin</option>
            <option value="Meter">Meter</option>
            <option value="Pack">Pack</option>
            <option value="Pad">Pad</option>
            <option value="Pasang">Pasang</option>
            <option value="Pcs" selected>Pcs</option>
            <option value="Rim">Rim</option>
            <option value="Roll">Roll</option>
            <option value="Sak">Sak</option>
            <option value="Set">Set</option>
            <option value="Unit">Unit</option>
        `;
        conversionFactorInput.value = 1;
        unitHelpText.innerHTML = '<i class="bi bi-info-circle"></i> Pilih barang terlebih dahulu untuk melihat satuan yang tersedia';
    }

    // Update conversion factor when unit changes
    document.getElementById('unit').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const conversionFactor = selectedOption.dataset.conversionFactor || 1;
        document.getElementById('conversion_factor').value = conversionFactor;
    });

    // Load units on page load if item_id exists
    @if($submission->item_id)
        loadItemUnits({{ $submission->item_id }});
    @elseif(old('item_id'))
        loadItemUnits({{ old('item_id') }});
    @endif

    // Preview new photos
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
</script>
@endpush
