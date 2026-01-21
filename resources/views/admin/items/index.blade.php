@extends('layouts.app')

@section('page-title', 'Manajemen Item')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Manajemen Item</h4>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar Item</h6>
        <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Item
        </a>
    </div>

    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('admin.items.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Item</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nama atau kode">
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label for="supplier_id" class="form-label">Supplier</label>
                <select class="form-select" id="supplier_id" name="supplier_id">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    <option value="discontinued" {{ request('status') === 'discontinued' ? 'selected' : '' }}>Tidak Diproduksi</option>
                    <option value="wrong_input" {{ request('status') === 'wrong_input' ? 'selected' : '' }}>Salah Input</option>
                    <option value="seasonal" {{ request('status') === 'seasonal' ? 'selected' : '' }}>Musiman</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>

        <!-- Items Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Unit</th>
                        <th>Min Threshold</th>
                        <th>Total Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <code>{{ $item->code }}</code>
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>
                                @if($item->category)
                                    <span class="badge bg-secondary">{{ $item->category->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->supplier->name ?? '-' }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ number_format($item->min_threshold) }}</td>
                            <td>
                                @if($item->total_stock == 0)
                                    <span class="badge bg-danger">{{ number_format($item->total_stock) }}</span>
                                @elseif($item->total_stock <= $item->min_threshold)
                                    <span class="badge bg-warning text-dark">{{ number_format($item->total_stock) }}</span>
                                @else
                                    <span class="badge bg-success">{{ number_format($item->total_stock) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                    @if($item->inactive_reason === 'seasonal')
                                        <br><small class="text-muted">Musiman</small>
                                    @endif
                                @else
                                    @if($item->inactive_reason === 'discontinued')
                                        <span class="badge bg-danger">Tidak Diproduksi</span>
                                    @elseif($item->inactive_reason === 'wrong_input')
                                        <span class="badge bg-warning text-dark">Salah Input</span>
                                        @if($item->replaced_by_item_id)
                                            <br><small class="text-muted">→ Item #{{ $item->replaced_by_item_id }}</small>
                                        @endif
                                    @elseif($item->inactive_reason === 'seasonal')
                                        <span class="badge bg-info">Musiman (Off)</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.items.show', $item) }}"
                                       class="btn btn-outline-info"
                                       title="View Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.items.edit', $item) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-warning"
                                            onclick="setThreshold({{ $item->id }}, '{{ $item->name }}', {{ $item->min_threshold }})"
                                            title="Set Threshold">
                                        <i class="bi bi-graph-up"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-danger"
                                            onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-box text-muted fs-1 mb-3"></i>
                                <p class="text-muted mb-0">Tidak ada item ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $items->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Set Threshold Modal -->
<div class="modal fade" id="thresholdModal" tabindex="-1" aria-labelledby="thresholdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="thresholdModalLabel">Set Minimum Threshold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Item</label>
                    <p class="form-control-plaintext" id="thresholdItemName"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Current Threshold</label>
                    <p class="form-control-plaintext" id="currentThreshold"></p>
                </div>
                <div class="mb-3">
                    <label for="newThreshold" class="form-label">New Threshold</label>
                    <input type="number" class="form-control" id="newThreshold" min="0" required>
                    <div class="form-text">Jumlah minimum sebelum dianggap low stock</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveThresholdBtn" onclick="saveThreshold()">
                    <i class="bi bi-check-circle me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menonaktifkan item <strong id="itemName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Item yang memiliki stock tidak dapat dihapus.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Nonaktifkan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentItemId = null;

    function setThreshold(itemId, itemName, currentThreshold) {
        currentItemId = itemId;
        document.getElementById('thresholdItemName').textContent = itemName;
        document.getElementById('currentThreshold').textContent = currentThreshold.toLocaleString();
        document.getElementById('newThreshold').value = currentThreshold;

        const modal = new bootstrap.Modal(document.getElementById('thresholdModal'));
        modal.show();
    }

    function saveThreshold() {
        const newThreshold = document.getElementById('newThreshold').value;

        if (!newThreshold || newThreshold < 0) {
            alert('Please enter a valid threshold value (0 or greater)');
            return;
        }

        const saveBtn = document.getElementById('saveThresholdBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';
        saveBtn.disabled = true;

        fetch(`/admin/items/${currentItemId}/set-threshold`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                min_threshold: parseInt(newThreshold)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('thresholdModal'));
                modal.hide();

                // Show success message
                showToast('Threshold updated successfully!', 'success');

                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error('Failed to update threshold');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to update threshold. Please try again.', 'error');
        })
        .finally(() => {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }

    function deleteItem(itemId, itemName) {
        document.getElementById('itemName').textContent = itemName;
        document.getElementById('deleteForm').action = `/admin/items/${itemId}`;

        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function showToast(message, type) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
</script>
@endpush