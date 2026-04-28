@extends('layouts.app')

@section('page-title', 'Kelola Stok')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Kelola Stok</h4>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-box-seam fs-4 text-primary"></i>
                    </div>
                </div>
                <h3 class="mb-1">{{ number_format($statistics['total_items']) }}</h3>
                <p class="text-muted mb-0 small">Total Items</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-boxes fs-4 text-success"></i>
                    </div>
                </div>
                <h3 class="mb-1">{{ number_format($statistics['total_stock']) }}</h3>
                <p class="text-muted mb-0 small">Total Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-x-circle fs-4 text-danger"></i>
                    </div>
                </div>
                <h3 class="mb-1">{{ number_format($statistics['out_stock_count']) }}</h3>
                <p class="text-muted mb-0 small">stock habis</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('gudang.stocks.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Cari Barang</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Nama barang...">
                    </div>
                    <div class="col-md-3">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="stock_status" class="form-label">Status Stock</label>
                        <select class="form-select" id="stock_status" name="stock_status">
                            <option value="">Semua Stock</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stock Sedikit</option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Stock Habis</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('gudang.stocks.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Stocks Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Daftar Stok
                </h6>
            </div>
            <div class="card-body">
                @if($stocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Stock Saat ini</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Aktivitas Sebelumnya</th>
                                    <th>Terakhir Di Ubah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $stock->item->code }}</span></td>
                                        <td><strong>{{ $stock->item->name }}</strong></td>
                                        <td>{{ $stock->item->category->name }}</td>
                                        <td>
                                            <strong class="fs-5">{{ number_format($stock->quantity) }}</strong>
                                        </td>
                                        <td>{{ $stock->item->unit }}</td>
                                        <td>
                                            @if($stock->quantity == 0)
                                                <span class="badge bg-danger">Habis</span>
                                            @else
                                                <span class="badge bg-success">Tersedia</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($stock->recent_movements && $stock->recent_movements->count() > 0)
                                                <div class="small">
                                                    @foreach($stock->recent_movements->take(2) as $movement)
                                                        <div class="mb-1">
                                                            @switch($movement->movement_type)
                                                                @case('in')
                                                                    <span class="badge badge-sm bg-success">
                                                                        <i class="bi bi-arrow-up"></i> IN
                                                                    </span>
                                                                    <span class="text-success fw-bold">+{{ number_format(abs($movement->quantity)) }}</span>
                                                                    @break
                                                                @case('out')
                                                                    <span class="badge badge-sm bg-danger">
                                                                        <i class="bi bi-arrow-down"></i> OUT
                                                                    </span>
                                                                    <span class="text-danger fw-bold">-{{ number_format(abs($movement->quantity)) }}</span>
                                                                    @break
                                                                @case('adjustment')
                                                                    <span class="badge badge-sm bg-warning text-dark">
                                                                        <i class="bi bi-gear"></i> ADJ
                                                                    </span>
                                                                    <span class="fw-bold">{{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}</span>
                                                                    @break
                                                            @endswitch
                                                            <span class="text-muted">oleh {{ $movement->creator ? $movement->creator->name : 'System' }}</span>
                                                            <br><small class="text-muted">{{ $movement->created_at->diffForHumans() }}</small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <small class="text-muted">Belum ada aktivitas</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $stock->last_updated ? $stock->last_updated->format('d/m/Y H:i') : '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('gudang.stocks.history', $stock->item) }}" 
                                                   class="btn btn-outline-info" title="View History">
                                                    <i class="bi bi-clock-history"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#adjustmentModal"
                                                        data-stock-id="{{ $stock->id }}"
                                                        data-item-name="{{ $stock->item->name }}"
                                                        data-current-stock="{{ $stock->quantity }}"
                                                        data-item-unit="{{ $stock->item->unit }}"
                                                        title="Adjust Stock">
                                                    <i class="bi bi-plus-slash-minus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $stocks->firstItem() }} - {{ $stocks->lastItem() }} of {{ $stocks->total() }} stocks
                        </div>
                        <div>
                            {{ $stocks->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">Tidak ada data stok</h5>
                        <p class="text-muted mb-0">
                            @if(request()->hasAny(['search', 'category_id', 'stock_status']))
                                Tidak ada hasil dengan filter yang dipilih.
                            @else
                                Belum ada data stok untuk unit ini.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
                        <small class="text-muted">Masukkan jumlah yang akan ditambah/dikurangi</small>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan/Alasan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Contoh: Barang rusak, Barang digunakan untuk kegiatan X, Stock opname, dll." required></textarea>
                        <small class="text-muted">Jelaskan alasan adjustment untuk audit trail</small>
                    </div>

                    <div class="mb-3">
                        <label for="adjustment_date" class="form-label">Tanggal Adjustment</label>
                        <input type="datetime-local" class="form-control" id="adjustment_date" name="adjustment_date">
                        <small class="text-muted">Kosongkan untuk menggunakan tanggal saat ini</small>
                    </div>

                    <div class="alert alert-success d-none" id="stock_preview">
                        <strong>Preview:</strong>
                        <div id="stock_calculation"></div>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const adjustmentModal = document.getElementById('adjustmentModal');
        const adjustmentTypeSelect = document.getElementById('adjustment_type');
        const quantityInput = document.getElementById('quantity');
        const warningReduce = document.getElementById('warning_reduce');
        const errorInsufficient = document.getElementById('error_insufficient');
        const stockPreview = document.getElementById('stock_preview');
        let currentStock = 0;
        
        const submitBtn = adjustmentModal.querySelector('button[type="submit"]');
        
        // Set default date
        const dateInput = document.getElementById('adjustment_date');
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        dateInput.value = now.toISOString().slice(0, 16);
        
        // Handle adjustment modal
        adjustmentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const stockId = button.getAttribute('data-stock-id');
            const itemName = button.getAttribute('data-item-name');
            currentStock = parseInt(button.getAttribute('data-current-stock')) || 0;
            const itemUnit = button.getAttribute('data-item-unit');
            
            // Set modal data
            document.getElementById('modal_stock_id').value = stockId;
            document.getElementById('modal_item_name').textContent = itemName;
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
            
            // Re-set default date
            const nowDate = new Date();
            nowDate.setMinutes(nowDate.getMinutes() - nowDate.getTimezoneOffset());
            dateInput.value = nowDate.toISOString().slice(0, 16);
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
    });
</script>
@endpush
