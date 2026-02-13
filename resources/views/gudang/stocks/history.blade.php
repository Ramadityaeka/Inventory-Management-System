@extends('layouts.app')

@section('page-title', 'Riwayat Pergerakan Stok - ' . $item->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <a href="{{ route('gudang.stocks.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-arrow-left"></i>
                </a>
                Riwayat Pergerakan Stok
            </h4>
        </div>
    </div>
        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detail Offcanvas Handler
            const detailBtns = document.querySelectorAll('.btn-detail-offcanvas');
            const offcanvasElement = document.getElementById('detailOffcanvas');
            
            if (offcanvasElement) {
                const detailOffcanvas = new bootstrap.Offcanvas(offcanvasElement);

                detailBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const data = {
                            itemName: this.dataset.itemName,
                            itemCode: this.dataset.itemCode,
                            date: this.dataset.date,
                            warehouse: this.dataset.warehouse,
                            type: this.dataset.type,
                            quantity: this.dataset.quantity,
                            unit: this.dataset.unit,
                            supplierName: this.dataset.supplierName,
                            supplierPhone: this.dataset.supplierPhone,
                            supplierEmail: this.dataset.supplierEmail,
                            supplierAddress: this.dataset.supplierAddress,
                            staffName: this.dataset.staffName,
                            staffEmail: this.dataset.staffEmail,
                            staffRole: this.dataset.staffRole,
                            creatorName: this.dataset.creatorName,
                            creatorRole: this.dataset.creatorRole,
                            notes: this.dataset.notes,
                            invoicePhoto: this.dataset.invoicePhoto,
                            itemPhoto: this.dataset.itemPhoto
                        };

                        // Populate offcanvas - Item info
                        document.getElementById('detail-item-name').textContent = data.itemName;
                        document.getElementById('detail-item-code').textContent = data.itemCode;
                        document.getElementById('detail-date').textContent = data.date;
                        document.getElementById('detail-warehouse').textContent = data.warehouse;
                        document.getElementById('detail-notes').textContent = data.notes || 'Tidak ada catatan';

                        // Type badge
                        let typeBadge = '';
                        let quantityClass = '';
                        let prefix = '';
                        switch(data.type) {
                            case 'in':
                                typeBadge = '<span class="badge bg-success"><i class="bi bi-arrow-up-circle me-1"></i>Barang Masuk</span>';
                                quantityClass = 'text-success';
                                prefix = '+';
                                break;
                            case 'out':
                                typeBadge = '<span class="badge bg-danger"><i class="bi bi-arrow-down-circle me-1"></i>Barang Keluar</span>';
                                quantityClass = 'text-danger';
                                prefix = '-';
                                break;
                            case 'adjustment':
                                typeBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-gear me-1"></i>Penyesuaian</span>';
                                quantityClass = parseFloat(data.quantity) > 0 ? 'text-success' : 'text-danger';
                                prefix = parseFloat(data.quantity) > 0 ? '+' : '';
                                break;
                        }
                        document.getElementById('detail-type-badge').innerHTML = typeBadge;
                        document.getElementById('detail-quantity').innerHTML = `<span class="${quantityClass}">${prefix}${new Intl.NumberFormat().format(Math.abs(data.quantity))}</span> <small class="text-muted">${data.unit}</small>`;

                        // Supplier
                        if (data.supplierName && data.supplierName !== '-') {
                            document.getElementById('detail-supplier-name').textContent = data.supplierName;
                            let supplierInfo = '';
                            if (data.supplierPhone && data.supplierPhone !== '-') {
                                supplierInfo += `<div><i class="bi bi-telephone-fill text-success me-2"></i>${data.supplierPhone}</div>`;
                            }
                            if (data.supplierEmail && data.supplierEmail !== '-') {
                                supplierInfo += `<div><i class="bi bi-envelope-fill text-primary me-2"></i>${data.supplierEmail}</div>`;
                            }
                            if (data.supplierAddress && data.supplierAddress !== '-') {
                                supplierInfo += `<div class="mt-2"><i class="bi bi-geo-alt-fill text-danger me-2"></i>${data.supplierAddress}</div>`;
                            }
                            document.getElementById('detail-supplier-info').innerHTML = supplierInfo || '<small class="text-muted">Tidak ada info kontak</small>';
                            document.getElementById('detail-supplier-section').style.display = 'block';
                        } else {
                            document.getElementById('detail-supplier-section').style.display = 'none';
                        }

                        // Staff
                        if (data.staffName && data.staffName !== '-') {
                            const staffInitial = data.staffName.charAt(0).toUpperCase();
                            document.getElementById('detail-staff-avatar').textContent = staffInitial;
                            document.getElementById('detail-staff-name').textContent = data.staffName;
                            document.getElementById('detail-staff-email').textContent = data.staffEmail && data.staffEmail !== '-' ? data.staffEmail : 'Tidak ada email';
                            
                            // Display staff role
                            const roleMap = {
                                'super_admin': 'Super Admin',
                                'admin_gudang': 'Admin Unit',
                                'staff_gudang': 'Staff Unit'
                            };
                            const roleDisplay = roleMap[data.staffRole] || data.staffRole || 'Staff Unit';
                            document.getElementById('detail-staff-role').textContent = roleDisplay;
                            
                            document.getElementById('detail-staff-section').style.display = 'block';
                        } else {
                            document.getElementById('detail-staff-section').style.display = 'none';
                        }

                        // Creator
                        if (data.creatorName && data.creatorName !== '-') {
                            const creatorInitial = data.creatorName.charAt(0).toUpperCase();
                            document.getElementById('detail-creator-avatar').textContent = creatorInitial;
                            document.getElementById('detail-creator-name').textContent = data.creatorName;
                            const roleMap = {
                                'super_admin': 'Super Admin',
                                'admin_gudang': 'Admin Unit',
                                'staff_gudang': 'Staff Unit'
                            };
                            document.getElementById('detail-creator-role').textContent = roleMap[data.creatorRole] || data.creatorRole;
                            document.getElementById('detail-creator-section').style.display = 'block';
                        } else {
                            document.getElementById('detail-creator-section').style.display = 'none';
                        }

                        // Photos
                        const hasInvoice = data.invoicePhoto && data.invoicePhoto !== '';
                        const hasItemPhoto = data.itemPhoto && data.itemPhoto !== '';
                        
                        if (hasInvoice) {
                            document.getElementById('detail-invoice-img').src = data.invoicePhoto;
                            document.getElementById('detail-invoice-wrapper').style.display = 'block';
                        } else {
                            document.getElementById('detail-invoice-wrapper').style.display = 'none';
                        }

                        if (hasItemPhoto) {
                            document.getElementById('detail-item-photo-img').src = data.itemPhoto;
                            document.getElementById('detail-item-photo-wrapper').style.display = 'block';
                        } else {
                            document.getElementById('detail-item-photo-wrapper').style.display = 'none';
                        }

                        document.getElementById('detail-photos-section').style.display = (hasInvoice || hasItemPhoto) ? 'block' : 'none';

                        // Show offcanvas
                        detailOffcanvas.show();
                    });
                });
            }

            // Handle Stock Adjustment Modal with Validation
            const adjustmentModal = document.getElementById('adjustmentModal');
            const adjustmentTypeSelect = document.getElementById('adjustment_type');
            const quantityInput = document.getElementById('quantity');
            const warningReduce = document.getElementById('warning_reduce');
            const errorInsufficient = document.getElementById('error_insufficient');
            const stockPreview = document.getElementById('stock_preview');
            let currentStock = 0;
            
            if (adjustmentModal) {
                const submitBtn = adjustmentModal.querySelector('button[type="submit"]');
                
                adjustmentModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const stockId = button.getAttribute('data-stock-id');
                    const itemName = button.getAttribute('data-item-name');
                    currentStock = parseInt(button.getAttribute('data-current-stock')) || 0;
                    const itemUnit = button.getAttribute('data-item-unit');
                    const warehouseName = button.getAttribute('data-warehouse-name');
                    
                    // Set modal data
                    document.getElementById('modal_stock_id').value = stockId;
                    document.getElementById('modal_item_name').textContent = itemName;
                    document.getElementById('modal_warehouse_name').textContent = warehouseName;
                    document.getElementById('modal_current_stock').textContent = new Intl.NumberFormat().format(currentStock);
                    document.getElementById('modal_item_unit').textContent = itemUnit;
                    
                    // Reset form
                    document.getElementById('adjustmentForm').reset();
                    document.getElementById('modal_stock_id').value = stockId;
                    warningReduce.classList.add('d-none');
                    errorInsufficient.classList.add('d-none');
                    stockPreview.classList.add('d-none');
                    submitBtn.disabled = false;
                    quantityInput.classList.remove('is-invalid');
                });
                
                // Real-time validation
                function validateAndPreview() {
                    const type = adjustmentTypeSelect.value;
                    const quantity = parseInt(quantityInput.value) || 0;
                    
                    if (!type || quantity <= 0) {
                        stockPreview.classList.add('d-none');
                        errorInsufficient.classList.add('d-none');
                        warningReduce.classList.add('d-none');
                        return;
                    }

                    if (type === 'reduce') {
                        warningReduce.classList.remove('d-none');
                        
                        if (quantity > currentStock) {
                            errorInsufficient.classList.remove('d-none');
                            document.getElementById('error_insufficient_text').innerHTML = 
                                `Anda mencoba mengurangi <strong>${new Intl.NumberFormat().format(quantity)}</strong> item, ` +
                                `tetapi stok saat ini hanya <strong>${new Intl.NumberFormat().format(currentStock)}</strong> item. ` +
                                `Maksimal pengurangan: <strong>${new Intl.NumberFormat().format(currentStock)}</strong> item.`;
                            quantityInput.classList.add('is-invalid');
                            submitBtn.disabled = true;
                            stockPreview.classList.add('d-none');
                            return;
                        } else {
                            errorInsufficient.classList.add('d-none');
                            quantityInput.classList.remove('is-invalid');
                            submitBtn.disabled = false;
                        }
                    } else {
                        warningReduce.classList.add('d-none');
                        errorInsufficient.classList.add('d-none');
                        quantityInput.classList.remove('is-invalid');
                        submitBtn.disabled = false;
                    }

                    // Show preview
                    const newStock = type === 'add' ? currentStock + quantity : currentStock - quantity;
                    const previewClass = type === 'add' ? 'text-success' : 'text-danger';
                    const operator = type === 'add' ? '+' : '-';
                    
                    document.getElementById('stock_calculation').innerHTML = 
                        `Stok saat ini: <strong>${new Intl.NumberFormat().format(currentStock)}</strong> ` +
                        `<span class="${previewClass}">${operator} ${new Intl.NumberFormat().format(quantity)}</span> = ` +
                        `<strong class="${previewClass}">${new Intl.NumberFormat().format(newStock)}</strong> ` +
                        `<span class="text-muted">${document.getElementById('modal_item_unit').textContent}</span>`;
                    stockPreview.classList.remove('d-none');
                }

                adjustmentTypeSelect.addEventListener('change', validateAndPreview);
                quantityInput.addEventListener('input', validateAndPreview);
                
                // Reset modal when hidden
                adjustmentModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('adjustmentForm').reset();
                    warningReduce.classList.add('d-none');
                    errorInsufficient.classList.add('d-none');
                    stockPreview.classList.add('d-none');
                    quantityInput.classList.remove('is-invalid');
                    submitBtn.disabled = false;
                });
            }

            // Image preview modal
            const imageModal = document.getElementById('imageModal');
            if (imageModal) {
                document.body.addEventListener('click', function(e) {
                    if (e.target.classList.contains('preview-image')) {
                        const imgSrc = e.target.src;
                        const imgTitle = e.target.alt || 'Preview';
                        document.getElementById('imageModalLabel').textContent = imgTitle;
                        document.getElementById('modalImage').src = imgSrc;
                        const bsImageModal = bootstrap.Modal.getInstance(imageModal) || new bootstrap.Modal(imageModal);
                        bsImageModal.show();
                    }
                });
            }
        });
        </script>
        @endpush
</div>

<!-- Item Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">{{ $item->name }}</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="150">Kode Item:</th>
                        <td><code>{{ $item->code }}</code></td>
                    </tr>
                    <tr>
                        <th>Kategori:</th>
                        <td>{{ $item->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Satuan:</th>
                        <td>{{ $item->unit }}</td>
                    </tr>
                    <tr>
                        <th>Supplier:</th>
                        <td>
                            @php
                                // Get latest supplier from submissions
                                $latestSubmission = $item->submissions()
                                    ->with('supplier')
                                    ->where('status', 'approved')
                                    ->latest('submitted_at')
                                    ->first();
                            @endphp
                            @if($latestSubmission && $latestSubmission->supplier)
                                <strong>{{ $latestSubmission->supplier->name }}</strong>
                                @if($latestSubmission->supplier->phone)
                                    <br><small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $latestSubmission->supplier->phone }}</small>
                                @endif
                                @if($latestSubmission->supplier->email)
                                    <br><small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $latestSubmission->supplier->email }}</small>
                                @endif
                            @else
                                <span class="text-muted">Belum ada data supplier</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Image Preview Modal -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title text-white" id="imageModalLabel">Preview</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img id="modalImage" src="" alt="Preview" class="img-fluid" style="max-height: 80vh;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h6 class="mb-3">Stok Saat Ini per Unit</h6>
                @if($currentStocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th class="text-end">Stok</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentStocks as $stock)
                                    <tr>
                                        <td>{{ $stock->warehouse->name }}</td>
                                        <td class="text-end">
                                            {{ number_format($stock->quantity) }} {{ $stock->item->unit }}
                                        </td>
                                        <td class="text-end">
                                            @if($stock->quantity == 0)
                                                <span class="badge bg-danger">Habis</span>
                                            @else
                                                <span class="badge bg-success">Tersedia</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Tidak ada informasi stok</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('gudang.stocks.history', $item) }}" class="row g-3">
            <div class="col-md-3">
                <label for="warehouse_id" class="form-label">Unit</label>
                <select class="form-select" id="warehouse_id" name="warehouse_id">
                    <option value="">Semua Unit</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Tipe</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Barang Masuk</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Barang Keluar</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('gudang.stocks.history', $item) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Movement History Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Riwayat Pergerakan</h6>
        <span class="badge bg-secondary">{{ $movements->total() }} pergerakan</span>
    </div>
    <div class="card-body">
        @if($movements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal/Waktu</th>
                            <th>Unit</th>
                            <th>Tipe</th>
                            <th class="text-end">Jumlah</th>
                            <th>Supplier</th>
                            <th>Diajukan Oleh</th>
                            <th>Catatan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            @php
                                // Get current stock for this warehouse
                                $currentStock = $currentStocks->firstWhere('warehouse_id', $movement->warehouse_id);
                                $currentQuantity = $currentStock ? $currentStock->quantity : 0;
                                $stockId = $currentStock ? $currentStock->id : null;
                            @endphp
                            <tr>
                                <td>
                                    <div>{{ $movement->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $movement->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $movement->warehouse->name }}</span>
                                </td>
                                <td>
                                    @switch($movement->movement_type)
                                        @case('in')
                                            <span class="badge bg-success">
                                                <i class="bi bi-arrow-up-circle me-1"></i>Barang Masuk
                                            </span>
                                            @break
                                        @case('out')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-arrow-down-circle me-1"></i>Barang Keluar
                                            </span>
                                            @break
                                        @case('adjustment')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-gear me-1"></i>Penyesuaian
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($movement->movement_type) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold fs-5 {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($movement->quantity) }}
                                    </span>
                                    <br><small class="text-muted">{{ $movement->item->unit }}</small>
                                </td>
                                <td>
                                    @if($movement->submission && $movement->submission->supplier)
                                        <small>
                                            <strong>{{ $movement->submission->supplier->name }}</strong>
                                            @if($movement->submission->supplier->phone)
                                                <br><span class="text-muted">{{ $movement->submission->supplier->phone }}</span>
                                            @endif
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->submission && $movement->submission->staff)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 28px; height: 28px; background-color: #28a745; color: white; font-size: 0.7rem; font-weight: bold;">
                                                {{ strtoupper(substr($movement->submission->staff->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <small><strong>{{ $movement->submission->staff->name }}</strong></small>
                                                <br><small class="text-muted">
                                                    @switch($movement->submission->staff->role)
                                                        @case('super_admin')
                                                            Super Admin
                                                            @break
                                                        @case('admin_gudang')
                                                            Admin Unit
                                                            @break
                                                        @case('staff_gudang')
                                                            Staff Unit
                                                            @break
                                                        @default
                                                            {{ $movement->submission->staff->role }}
                                                    @endswitch
                                                </small>
                                            </div>
                                        </div>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->notes)
                                        <small class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $movement->notes }}">
                                            {{ Str::limit($movement->notes, 50) }}
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" 
                                                class="btn btn-outline-info btn-detail-offcanvas" 
                                                data-movement-id="{{ $movement->id }}"
                                                data-item-name="{{ $movement->item->name }}"
                                                data-item-code="{{ $movement->item->code }}"
                                                data-date="{{ $movement->created_at->format('d M Y H:i') }}"
                                                data-warehouse="{{ $movement->warehouse->name }}"
                                                data-type="{{ $movement->movement_type }}"
                                                data-quantity="{{ $movement->quantity }}"
                                                data-unit="{{ $movement->item->unit }}"
                                                data-supplier-name="{{ $movement->submission && $movement->submission->supplier ? $movement->submission->supplier->name : '-' }}"
                                                data-supplier-phone="{{ $movement->submission && $movement->submission->supplier ? $movement->submission->supplier->phone : '-' }}"
                                                data-supplier-email="{{ $movement->submission && $movement->submission->supplier ? $movement->submission->supplier->email : '-' }}"
                                                data-supplier-address="{{ $movement->submission && $movement->submission->supplier ? $movement->submission->supplier->address : '-' }}"
                                                data-staff-name="{{ $movement->submission && $movement->submission->staff ? $movement->submission->staff->name : '-' }}"
                                                data-staff-email="{{ $movement->submission && $movement->submission->staff ? $movement->submission->staff->email : '-' }}"
                                                data-creator-name="{{ $movement->creator ? $movement->creator->name : '-' }}"
                                                data-creator-role="{{ $movement->creator ? $movement->creator->role : '-' }}"
                                                data-notes="{{ $movement->notes ?? 'Tidak ada catatan' }}"
                                                data-invoice-photo="{{ $movement->submission && $movement->submission->invoice_photo ? asset('storage/' . $movement->submission->invoice_photo) : '' }}"
                                                data-item-photo="{{ $movement->submission && $movement->submission->item_photo ? asset('storage/' . $movement->submission->item_photo) : '' }}"
                                                title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#adjustmentModal"
                                                data-stock-id="{{ $stockId }}"
                                                data-item-name="{{ $movement->item->name }}"
                                                data-current-stock="{{ $currentQuantity }}"
                                                data-item-unit="{{ $movement->item->unit }}"
                                                data-warehouse-name="{{ $movement->warehouse->name }}"
                                                title="Adjust Stock">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($movements->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $movements->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-0">Tidak ada riwayat pergerakan untuk barang ini</p>
                @if(request()->hasAny(['warehouse_id', 'type', 'start_date', 'end_date']))
                    <p class="text-muted small">Coba sesuaikan filter Anda</p>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Summary Statistics -->
@if($movements->count() > 0)
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Total Barang Masuk</h6>
                    <h3 class="text-success mb-0">
                        +{{ number_format($movements->where('movement_type', 'in')->sum('quantity')) }}
                    </h3>
                    <small class="text-muted">Barang Masuk</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Total Barang Keluar</h6>
                    <h3 class="text-danger mb-0">
                        {{ number_format(abs($movements->where('movement_type', 'out')->sum('quantity'))) }}
                    </h3>
                    <small class="text-muted">Barang Keluar</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Total Penyesuaian</h6>
                    <h3 class="text-warning mb-0">
                        {{ number_format($movements->where('movement_type', 'adjustment')->count()) }}
                    </h3>
                    <small class="text-muted">Penyesuaian Stok</small>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-labelledby="adjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="adjustmentForm" method="POST" action="{{ route('gudang.stocks.adjust') }}">
                @csrf
                <input type="hidden" name="stock_id" id="modal_stock_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="adjustmentModalLabel">Stock Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong id="modal_item_name"></strong><br>
                                <small>Unit: <span id="modal_warehouse_name" class="fw-bold"></span></small><br>
                                <small>Current Stock: <span id="modal_current_stock" class="fw-bold"></span> <span id="modal_item_unit"></span></small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="adjustment_type" class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select class="form-select" id="adjustment_type" name="adjustment_type" required>
                            <option value="">Pilih Tipe</option>
                            <option value="add">Tambah Stok (Barang Masuk)</option>
                            <option value="reduce">Kurangi Stok (Barang Keluar)</option>
                        </select>
                        <small class="text-muted">Pilih "Kurangi Stok" jika barang sudah digunakan/keluar</small>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               min="1" step="1" required>
                        <small class="text-muted" id="quantity_help">Masukkan jumlah yang akan ditambah/dikurangi</small>
                        <div class="invalid-feedback" id="quantity_error"></div>
                    </div>
                    
                    <div class="alert alert-success d-none" id="stock_preview">
                        <strong>Preview:</strong>
                        <div id="stock_calculation"></div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan/Alasan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Contoh: Barang rusak, Barang digunakan untuk kegiatan X, Stock opname, dll." required></textarea>
                        <small class="text-muted">Jelaskan alasan adjustment untuk audit trail</small>
                    </div>

                    <div class="alert alert-warning d-none" id="warning_reduce">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>Pengurangan stock akan mengurangi jumlah barang yang tersedia di unit.</small>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="error_insufficient">
                        <i class="bi bi-x-circle me-2"></i>
                        <strong>Stok tidak mencukupi!</strong><br>
                        <small id="error_insufficient_text"></small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Adjust Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail Offcanvas (Slide from Right) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="detailOffcanvas" aria-labelledby="detailOffcanvasLabel" style="width: 500px;">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title" id="detailOffcanvasLabel">
            <i class="bi bi-info-circle-fill me-2"></i>Detail Pergerakan Stok
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- Item Info Header -->
        <div class="bg-light border-bottom p-3">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-box-seam fs-4 text-primary me-2"></i>
                <div>
                    <h6 class="mb-0" id="detail-item-name">-</h6>
                    <small class="text-muted" id="detail-item-code">-</small>
                </div>
            </div>
            <div class="mt-2" id="detail-type-badge"></div>
            <h3 class="mb-0 mt-2" id="detail-quantity">-</h3>
        </div>

        <!-- Details Sections -->
        <div class="p-3">
            <!-- Waktu & Lokasi -->
            <div class="mb-4">
                <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-calendar-event me-2"></i>Waktu & Lokasi
                </h6>
                <div class="row g-2">
                    <div class="col-12">
                        <div class="d-flex">
                            <i class="bi bi-calendar3 text-muted me-2 mt-1"></i>
                            <div>
                                <small class="text-muted d-block">Tanggal & Waktu</small>
                                <strong id="detail-date">-</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="d-flex">
                            <i class="bi bi-building text-muted me-2 mt-1"></i>
                            <div>
                                <small class="text-muted d-block">Unit/Gudang</small>
                                <strong id="detail-warehouse">-</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Info -->
            <div class="mb-4" id="detail-supplier-section">
                <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-truck me-2"></i>Informasi Supplier
                </h6>
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6 class="mb-2" id="detail-supplier-name">-</h6>
                        <div class="small" id="detail-supplier-info"></div>
                    </div>
                </div>
            </div>

            <!-- Staff Info -->
            <div class="mb-4" id="detail-staff-section">
                <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class=\"bi bi-person-check me-2\"></i>Diajukan Oleh
                </h6>
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold;" id="detail-staff-avatar">
                        S
                    </div>
                    <div>
                        <strong id="detail-staff-name">-</strong>
                        <div class="small text-muted" id="detail-staff-email">-</div>
                        <small class="badge bg-success" id="detail-staff-role">Staff Unit</small>
                    </div>
                </div>
            </div>

            <!-- Approved By -->
            <div class="mb-4" id="detail-creator-section">
                <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-check-circle me-2"></i>Disetujui Oleh
                </h6>
                <div class="d-flex align-items-center">
                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 45px; height: 45px; font-size: 1.2rem; font-weight: bold;" id="detail-creator-avatar">
                        A
                    </div>
                    <div>
                        <strong id="detail-creator-name">-</strong><br>
                        <small class="text-muted" id="detail-creator-role">-</small>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-journal-text me-2"></i>Catatan
                </h6>
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <p class="mb-0 small" id="detail-notes">-</p>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            <div class="mb-3" id="detail-photos-section">
                <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-images me-2"></i>Dokumentasi
                </h6>
                <div class="row g-2">
                    <div class="col-12" id="detail-invoice-wrapper">
                        <label class="small text-muted">Foto Invoice</label>
                        <div class="border rounded p-2 text-center bg-light">
                            <img id="detail-invoice-img" src="" alt="Invoice" class="img-fluid rounded preview-image" 
                                 style="max-height: 150px; cursor: pointer;">
                        </div>
                    </div>
                    <div class="col-12 mt-2" id="detail-item-photo-wrapper">
                        <label class="small text-muted">Foto Barang</label>
                        <div class="border rounded p-2 text-center bg-light">
                            <img id="detail-item-photo-img" src="" alt="Item" class="img-fluid rounded preview-image" 
                                 style="max-height: 150px; cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white" id="imageModalLabel">Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" alt="Preview" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>
@endsection
