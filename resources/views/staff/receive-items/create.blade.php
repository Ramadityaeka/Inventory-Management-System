@extends('layouts.app')

@section('page-title', 'Input Barang Masuk')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Input Barang Masuk</h4>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border: 3px solid #dc3545; background-color: #f8d7da;">
        <strong style="font-size: 1.2em;">⚠️ Terjadi kesalahan:</strong>
        <ul class="mb-0 mt-2" style="font-size: 1.1em;">
            @foreach($errors->all() as $error)
                <li><strong>{{ $error }}</strong></li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>
        // Scroll to error message
        window.scrollTo({ top: 0, behavior: 'smooth' });
        // Log errors to console for debugging
        console.error('Form validation errors:', @json($errors->all()));
    </script>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>✓ {{ session('success') }}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border: 3px solid #dc3545;">
        <strong style="font-size: 1.2em;">⚠️ {{ session('error') }}</strong>
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

                <div class="col-md-6 mb-3" id="item-code-field" style="display: none;">
                    <label for="item_code" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('item_code') is-invalid @enderror" 
                           id="item_code" name="item_code" value="{{ old('item_code') }}" 
                           placeholder="Contoh: 1.01.03.01.001" 
                           autocomplete="off" readonly>
                    <div class="form-text">Kode barang akan dibuat otomatis dari kategori yang dipilih</div>
                    @error('item_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row" id="category-field" style="display: none;">
                <div class="col-md-12 mb-3">
                    <label for="category_search" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" class="form-control @error('category_id') is-invalid @enderror" 
                               id="category_search" 
                               placeholder="Ketik untuk mencari kategori..." 
                               autocomplete="off">
                        <input type="hidden" id="category_id" name="category_id" value="{{ old('category_id') }}">
                        <div id="category-results" class="list-group position-absolute w-100" 
                             style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div id="selected-category" class="mt-2"></div>
                    @error('category_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">

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
                            id="unit" name="unit_select" required>
                        <option value="">Pilih Satuan</option>
                        <option value="Pcs" selected>Pcs</option>
                        <!-- Options will be dynamically loaded -->
                    </select>
                    <input type="text" class="form-control mt-2 @error('unit') is-invalid @enderror" 
                           id="custom_unit" name="unit" 
                           value="{{ old('unit', 'Pcs') }}"
                           placeholder="Ketik satuan custom..." 
                           style="display: none;"
                           maxlength="50">
                    <div id="unit-warning" class="alert alert-warning mt-2" style="display: none; padding: 8px;">
                        <i class="bi bi-exclamation-triangle"></i> <span id="unit-warning-text"></span>
                    </div>
                    <input type="hidden" id="conversion_factor" name="conversion_factor" value="1">
                    <div class="form-text" id="unit-help-text">
                        <i class="bi bi-info-circle"></i> Pilih dari daftar atau pilih "Lainnya" untuk input manual
                    </div>
                    @error('unit')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
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
                        <span class="text-muted small">(Opsional)</span>
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
                    <label for="warehouse_id" class="form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                            id="warehouse_id" name="warehouse_id" required>
                        <option value="">Pilih Unit</option>
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
                <div class="col-md-6 mb-3">
                    <label for="nota_number" class="form-label">
                        <i class="bi bi-receipt text-primary"></i> Nomor Nota
                    </label>
                    <input type="text" class="form-control @error('nota_number') is-invalid @enderror" 
                           id="nota_number" name="nota_number" value="{{ old('nota_number') }}" 
                           placeholder="Contoh: INV-2026-001">
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i> Nomor nota/invoice dari supplier (opsional)
                    </div>
                    @error('nota_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="receive_date" class="form-label">
                        <i class="bi bi-calendar-check text-success"></i> Tanggal Terima
                    </label>
                    <input type="date" class="form-control @error('receive_date') is-invalid @enderror" 
                           id="receive_date" name="receive_date" 
                           value="{{ old('receive_date', date('Y-m-d')) }}" 
                           max="{{ date('Y-m-d') }}">
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i> Tanggal barang diterima (default: hari ini)
                    </div>
                    @error('receive_date')
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
            const reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    // Preview for images
                    previewContainer.innerHTML = `
                        <div class="card mt-3" style="max-width: 400px;">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-image"></i> Preview Gambar</span>
                                <button type="button" class="btn btn-sm btn-outline-light" onclick="clearInvoicePreview()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <img src="${e.target.result}" class="card-img-top" style="max-height: 400px; object-fit: contain; background: #f8f9fa;">
                            <div class="card-body p-2 text-center">
                                <small class="text-muted"><i class="bi bi-file-earmark-image"></i> ${file.name}</small>
                                <br>
                                <small class="text-muted">${(file.size / 1024).toFixed(2)} KB</small>
                            </div>
                        </div>
                    `;
                } else if (file.type === 'application/pdf') {
                    // Preview for PDF
                    previewContainer.innerHTML = `
                        <div class="card mt-3">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-file-pdf"></i> Preview PDF</span>
                                <button type="button" class="btn btn-sm btn-outline-light" onclick="clearInvoicePreview()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <embed src="${e.target.result}" type="application/pdf" width="100%" height="500px" 
                                       style="border: none;">
                            </div>
                            <div class="card-footer text-center">
                                <small class="text-muted"><i class="bi bi-file-earmark-pdf"></i> ${file.name}</small>
                                <br>
                                <small class="text-muted">${(file.size / 1024).toFixed(2)} KB</small>
                            </div>
                        </div>
                    `;
                } else {
                    // Unsupported file type
                    previewContainer.innerHTML = `
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Format file tidak didukung untuk preview: ${file.name}
                        </div>
                    `;
                }
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Function to clear invoice preview
    window.clearInvoicePreview = function() {
        document.getElementById('invoice-preview-container').innerHTML = '';
        document.getElementById('invoice_photo').value = '';
    };

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
    const itemCodeField = document.getElementById('item-code-field');
    const categoryField = document.getElementById('category-field');
    const itemCodeInput = document.getElementById('item_code');
    const categoryIdInput = document.getElementById('category_id');
    let debounceTimer;

    itemNameInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            suggestionsList.style.display = 'none';
            itemIdInput.value = '';
            hideNewItemFields();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/staff/api/search-items?q=${encodeURIComponent(query)}`)
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
                                    <small class="text-muted">${item.category_name || 'Tanpa Kategori'}</small>
                                </div>
                                <small>Kode: ${item.code}</small>
                            `;
                            button.addEventListener('click', function() {
                                itemNameInput.value = item.name;
                                itemIdInput.value = item.id;
                                suggestionsList.style.display = 'none';
                                hideNewItemFields();
                                // Load units for selected item
                                loadItemUnits(item.id);
                            });
                            suggestionsList.appendChild(button);
                        });
                        suggestionsList.style.display = 'block';
                    } else {
                        suggestionsList.innerHTML = '<div class="list-group-item text-muted">Barang tidak ditemukan. Silakan pilih kategori untuk barang baru.</div>';
                        suggestionsList.style.display = 'block';
                        itemIdInput.value = '';
                        showNewItemFields();
                        resetUnitDropdown();
                    }
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                    suggestionsList.style.display = 'none';
                });
        }, 300);
    });

    function showNewItemFields() {
        itemCodeField.style.display = 'block';
        categoryField.style.display = 'block';
        itemCodeInput.required = true;
        categoryIdInput.required = true;
        resetUnitDropdown();
    }

    function hideNewItemFields() {
        itemCodeField.style.display = 'none';
        categoryField.style.display = 'none';
        itemCodeInput.required = false;
        categoryIdInput.required = false;
        itemCodeInput.value = '';
        categoryIdInput.value = '';
        document.getElementById('category_search').value = '';
        document.getElementById('selected-category').innerHTML = '';
        // Reset custom unit input if it was shown
        document.getElementById('custom_unit').style.display = 'none';
        document.getElementById('custom_unit').required = false;
        document.getElementById('unit').required = true;
    }

    // Category search
    const categorySearchInput = document.getElementById('category_search');
    const categoryResultsDiv = document.getElementById('category-results');
    const selectedCategoryDiv = document.getElementById('selected-category');
    let categoryDebounceTimer;

    categorySearchInput.addEventListener('input', function() {
        clearTimeout(categoryDebounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            categoryResultsDiv.style.display = 'none';
            return;
        }

        categoryDebounceTimer = setTimeout(() => {
            fetch(`/staff/api/search-categories?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    categoryResultsDiv.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach(category => {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'list-group-item list-group-item-action';
                            
                            const indent = '&nbsp;'.repeat(category.level * 4);
                            const arrow = category.level > 0 ? '<i class="bi bi-arrow-return-right text-muted me-1"></i>' : '';
                            
                            button.innerHTML = `
                                <div style="padding-left: ${category.level * 15}px;">
                                    ${arrow}
                                    <code class="text-primary">${category.code}</code> - <strong>${category.name}</strong>
                                </div>
                            `;
                            
                            button.addEventListener('click', function() {
                                selectCategory(category);
                            });
                            categoryResultsDiv.appendChild(button);
                        });
                        categoryResultsDiv.style.display = 'block';
                    } else {
                        categoryResultsDiv.innerHTML = '<div class="list-group-item text-muted">Kategori tidak ditemukan</div>';
                        categoryResultsDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching categories:', error);
                    categoryResultsDiv.style.display = 'none';
                });
        }, 300);
    });

    function selectCategory(category) {
        categoryIdInput.value = category.id;
        categorySearchInput.value = '';
        categoryResultsDiv.style.display = 'none';
        
        // Display selected category
        selectedCategoryDiv.innerHTML = `
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <strong>Kategori Terpilih:</strong><br>
                    <code class="text-primary">${category.code}</code> - ${category.name}
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCategory()">
                    <i class="bi bi-x"></i> Hapus
                </button>
            </div>
        `;
        
        // Auto-generate item code
        generateItemCode(category.id);
    }

    function clearCategory() {
        categoryIdInput.value = '';
        categorySearchInput.value = '';
        selectedCategoryDiv.innerHTML = '';
        itemCodeInput.value = '';
    }

    function generateItemCode(categoryId) {
        fetch(`/staff/api/generate-item-code?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.code) {
                    itemCodeInput.value = data.code;
                }
            })
            .catch(error => {
                console.error('Error generating item code:', error);
            });
    }

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
            // Also reset custom unit input
            document.getElementById('custom_unit').style.display = 'none';
            document.getElementById('custom_unit').required = false;
            document.getElementById('unit').required = true;
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
                const customUnitInput = document.getElementById('custom_unit');
                
                // Clear existing options
                unitSelect.innerHTML = '<option value="">Pilih Satuan</option>';
                
                // Reset custom input
                customUnitInput.style.display = 'none';
                customUnitInput.required = false;
                unitSelect.required = true;
                
                if (data.units && data.units.length > 0) {
                    // Add units to dropdown
                    let defaultUnitSet = false;
                    let defaultUnitValue = '';
                    
                    data.units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.name;
                        option.textContent = unit.name;
                        option.dataset.conversionFactor = unit.conversion_factor;
                        
                        // Select base unit by default if no old value
                        if (unit.is_base && !defaultUnitSet) {
                            option.selected = true;
                            defaultUnitSet = true;
                            defaultUnitValue = unit.name;
                            conversionFactorInput.value = unit.conversion_factor;
                        }
                        
                        unitSelect.appendChild(option);
                    });
                    
                    // IMPORTANT: Sync the default selected value to hidden input
                    if (defaultUnitValue) {
                        customUnitInput.value = defaultUnitValue;
                        console.log('After loading units, synced default unit to hidden input:', defaultUnitValue);
                    }
                    
                    // Add custom option at the end
                    const customOption = document.createElement('option');
                    customOption.value = '__custom__';
                    customOption.textContent = '➕ Lainnya (Input Manual)';
                    unitSelect.appendChild(customOption);
                    
                    // Update help text
                    unitHelpText.innerHTML = `
                        <i class="bi bi-info-circle"></i> 
                        Satuan yang tersedia untuk barang ini. 
                        Satuan dasar: <strong>${data.base_unit}</strong>. Pilih "Lainnya" untuk input manual.
                    `;
                } else {
                    // Fallback: show all units if no custom units defined
                    resetUnitDropdown();
                    unitHelpText.innerHTML = `
                        <i class="bi bi-info-circle"></i> 
                        Barang ini belum memiliki satuan khusus. 
                        Gunakan satuan dasar: <strong>${data.base_unit || 'Pcs'}</strong> atau pilih "Lainnya" untuk input manual.
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
        const customUnitInput = document.getElementById('custom_unit');
        
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
            <option value="__custom__">➕ Lainnya (Input Manual)</option>
        `;
        conversionFactorInput.value = 1;
        customUnitInput.value = 'Pcs'; // Set default value
        customUnitInput.style.display = 'none';
        customUnitInput.required = false;
        unitSelect.required = true;
        unitHelpText.innerHTML = '<i class="bi bi-info-circle"></i> Pilih dari daftar atau pilih "Lainnya" untuk input manual';
    }

    // Handle unit selection - toggle between dropdown and custom input
    document.getElementById('unit').addEventListener('change', function() {
        const customUnitInput = document.getElementById('custom_unit');
        const unitWarning = document.getElementById('unit-warning');
        const selectedOption = this.options[this.selectedIndex];
        const conversionFactor = selectedOption.dataset.conversionFactor || 1;
        document.getElementById('conversion_factor').value = conversionFactor;
        
        console.log('Unit dropdown changed:', this.value); // Debug log
        
        if (this.value === '__custom__') {
            // Show custom input field
            customUnitInput.style.display = 'block';
            customUnitInput.required = true;
            customUnitInput.value = ''; // Clear previous value
            customUnitInput.focus();
            unitWarning.style.display = 'none'; // Hide warning initially
            this.required = false;
        } else if (this.value !== '') {
            // Hide custom input and sync dropdown value
            customUnitInput.style.display = 'none';
            customUnitInput.required = false;
            customUnitInput.value = this.value; // Sync the value from dropdown
            unitWarning.style.display = 'none';
            this.required = true;
            console.log('Synced unit to hidden input:', customUnitInput.value); // Debug log
        } else {
            // Empty selection
            customUnitInput.style.display = 'none';
            customUnitInput.required = false;
            customUnitInput.value = '';
            unitWarning.style.display = 'none';
        }
    });

    // Validate and normalize custom unit input
    document.getElementById('custom_unit').addEventListener('input', function() {
        const unitSelect = document.getElementById('unit');
        const unitWarning = document.getElementById('unit-warning');
        const unitWarningText = document.getElementById('unit-warning-text');
        
        // Trim whitespace and normalize
        let inputValue = this.value.trim();
        
        // Auto-capitalize: first letter uppercase, rest lowercase (Title Case untuk kata pertama)
        if (inputValue.length > 0) {
            inputValue = inputValue.charAt(0).toUpperCase() + inputValue.slice(1).toLowerCase();
            // Update the input value with normalized version
            this.value = inputValue;
        }
        
        if (inputValue.length === 0) {
            unitWarning.style.display = 'none';
            return;
        }
        
        // Check if unit already exists in dropdown (case-insensitive)
        let existsInDropdown = false;
        let exactMatch = '';
        
        for (let i = 0; i < unitSelect.options.length; i++) {
            const optionValue = unitSelect.options[i].value;
            if (optionValue !== '__custom__' && optionValue !== '') {
                if (optionValue.toLowerCase() === inputValue.toLowerCase()) {
                    existsInDropdown = true;
                    exactMatch = optionValue;
                    break;
                }
            }
        }
        
        if (existsInDropdown) {
            // Show warning: unit already exists
            unitWarningText.innerHTML = `Satuan "<strong>${exactMatch}</strong>" sudah tersedia di daftar pilihan. Sebaiknya pilih dari dropdown.`;
            unitWarning.style.display = 'block';
            unitWarning.className = 'alert alert-danger mt-2';
        } else {
            // Check for similar units (potential typos)
            let similarUnit = checkSimilarUnit(inputValue, unitSelect);
            
            if (similarUnit) {
                unitWarningText.innerHTML = `Apakah maksud Anda "<strong>${similarUnit}</strong>"? Satuan yang Anda ketik mirip dengan yang ada di daftar.`;
                unitWarning.style.display = 'block';
                unitWarning.className = 'alert alert-warning mt-2';
            } else {
                // Show confirmation: new unit
                unitWarningText.innerHTML = `Satuan baru "<strong>${inputValue}</strong>" akan ditambahkan. Pastikan tidak ada typo.`;
                unitWarning.style.display = 'block';
                unitWarning.className = 'alert alert-info mt-2';
            }
        }
    });

    // Check for similar units (Levenshtein distance / simple similarity)
    function checkSimilarUnit(input, selectElement) {
        const inputLower = input.toLowerCase();
        const threshold = 2; // Max difference in characters
        
        for (let i = 0; i < selectElement.options.length; i++) {
            const optionValue = selectElement.options[i].value;
            if (optionValue !== '__custom__' && optionValue !== '') {
                const optionLower = optionValue.toLowerCase();
                
                // Check if lengths are similar and strings are similar
                if (Math.abs(inputLower.length - optionLower.length) <= threshold) {
                    const distance = levenshteinDistance(inputLower, optionLower);
                    if (distance > 0 && distance <= threshold) {
                        return optionValue;
                    }
                }
            }
        }
        return null;
    }

    // Calculate Levenshtein distance (edit distance)
    function levenshteinDistance(str1, str2) {
        const len1 = str1.length;
        const len2 = str2.length;
        const matrix = [];

        for (let i = 0; i <= len1; i++) {
            matrix[i] = [i];
        }

        for (let j = 0; j <= len2; j++) {
            matrix[0][j] = j;
        }

        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                if (str1.charAt(i - 1) === str2.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1, // substitution
                        matrix[i][j - 1] + 1,     // insertion
                        matrix[i - 1][j] + 1      // deletion
                    );
                }
            }
        }

        return matrix[len1][len2];
    }

    // Form validation before submit
    document.getElementById('receiveItemForm').addEventListener('submit', function(e) {
        const unitSelect = document.getElementById('unit');
        const customUnitInput = document.getElementById('custom_unit');
        
        // If custom unit is shown
        if (customUnitInput.style.display !== 'none' && customUnitInput.required) {
            const inputValue = customUnitInput.value.trim();
            
            // Check if empty
            if (inputValue.length === 0) {
                e.preventDefault();
                alert('Mohon isi satuan atau pilih dari daftar yang tersedia.');
                customUnitInput.focus();
                return false;
            }
            
            // Check if exists in dropdown (case-insensitive)
            for (let i = 0; i < unitSelect.options.length; i++) {
                const optionValue = unitSelect.options[i].value;
                if (optionValue !== '__custom__' && optionValue !== '') {
                    if (optionValue.toLowerCase() === inputValue.toLowerCase()) {
                        e.preventDefault();
                        if (confirm(`Satuan "${optionValue}" sudah ada di daftar pilihan.\n\nKlik OK untuk memilih dari daftar, atau Cancel untuk tetap melanjutkan dengan satuan baru.`)) {
                            // Select the existing unit
                            unitSelect.value = optionValue;
                            customUnitInput.style.display = 'none';
                            customUnitInput.required = false;
                            customUnitInput.value = optionValue;
                            unitSelect.required = true;
                            document.getElementById('unit-warning').style.display = 'none';
                        }
                        return false;
                    }
                }
            }
            
            // Final confirmation for new custom unit
            const similarUnit = checkSimilarUnit(inputValue, unitSelect);
            if (similarUnit) {
                if (!confirm(`Satuan yang Anda ketik mirip dengan "${similarUnit}".\n\nApakah Anda yakin ingin menggunakan "${inputValue}"?\n\nKlik OK untuk melanjutkan, atau Cancel untuk memperbaiki.`)) {
                    e.preventDefault();
                    customUnitInput.focus();
                    return false;
                }
            }
        }
    });

    // Load units if item_id is already set (e.g., from old input)
    @if(old('item_id'))
        loadItemUnits({{ old('item_id') }});
    @else
        // Load default units on page load
        resetUnitDropdown();
    @endif

    // Restore old unit value if exists (for validation errors)
    @if(old('unit'))
        const oldUnit = "{{ old('unit') }}";
        const unitSelect = document.getElementById('unit');
        const customUnitInput = document.getElementById('custom_unit');
        
        // Check if the old unit exists in the dropdown
        let foundInDropdown = false;
        for (let i = 0; i < unitSelect.options.length; i++) {
            if (unitSelect.options[i].value === oldUnit) {
                unitSelect.value = oldUnit;
                customUnitInput.value = oldUnit;
                foundInDropdown = true;
                break;
            }
        }
        
        // If not found, it's a custom unit
        if (!foundInDropdown && oldUnit !== '__custom__') {
            unitSelect.value = '__custom__';
            customUnitInput.value = oldUnit;
            customUnitInput.style.display = 'block';
            customUnitInput.required = true;
            unitSelect.required = false;
        }
    @else
        // Initialize: Sync dropdown default value to hidden input on page load
        const unitSelect = document.getElementById('unit');
        const customUnitInput = document.getElementById('custom_unit');
        
        if (unitSelect.value && unitSelect.value !== '' && unitSelect.value !== '__custom__') {
            customUnitInput.value = unitSelect.value;
            console.log('Page load: Synced default unit to hidden input:', customUnitInput.value);
        }
    @endif
</script>
@endpush
