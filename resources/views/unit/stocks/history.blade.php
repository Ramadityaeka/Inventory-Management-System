@extends('layouts.app')

@section('page-title', 'Stock Movement History - ' . $item->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <a href="{{ route('unit.stocks.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-arrow-left"></i>
                </a>
                Stock Movement History
            </h4>
        </div>
    </div>
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
            <div class="col-md-6">
                <h6 class="mb-3">Current Stock by Warehouse</h6>
                @if($currentStocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th class="text-end">Stock</th>
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
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @else
                                                <span class="badge bg-success">Available</span>
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
        <form method="GET" action="{{ route('unit.stocks.history', $item) }}" class="row g-3">
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
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">All Types</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Barang Masuk</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Barang Keluar</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>perubahan</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Waktu </label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('unit.stocks.history', $item) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Movement History Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Movement History</h6>
        <span class="badge bg-secondary">{{ $movements->total() }} pergerakan</span>
    </div>
    <div class="card-body">
        @if($movements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Warehouse</th>
                            <th>Type</th>
                            <th class="text-end">Quantity</th>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr>
                                <td>
                                    <div>{{ $movement->created_at->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted">{{ $movement->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $movement->warehouse->name }}</span>
                                </td>
                                <td>
                                    @switch($movement->movement_type)
                                        @case('in')
                                            <span class="badge bg-success">
                                                <i class="bi bi-arrow-up-circle me-1"></i>Stock In
                                            </span>
                                            <br><small class="text-muted">Barang Masuk</small>
                                            @break
                                        @case('out')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-arrow-down-circle me-1"></i>Stock Out
                                            </span>
                                            <br><small class="text-muted">Barang Keluar</small>
                                            @break
                                        @case('adjustment')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-gear me-1"></i>Adjustment
                                            </span>
                                            <br><small class="text-muted">Penyesuaian Stock</small>
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
                                    @if($movement->reference_type && $movement->reference_id)
                                        <small>
                                            @if(str_contains($movement->reference_type, 'App\\Models\\'))
                                                {{ ucfirst(str_replace('App\\Models\\', '', $movement->reference_type)) }}
                                            @else
                                                {{ $movement->reference_type }}
                                            @endif
                                            @if(is_numeric($movement->reference_id))
                                                #{{ $movement->reference_id }}
                                            @endif
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->creator)
                                        <div>
                                            <strong>{{ $movement->creator->name }}</strong>
                                        </div>
                                        <small class="text-muted">
                                            @if($movement->creator->role === 'super_admin')
                                                Super Admin
                                            @elseif($movement->creator->role === 'admin_unit')
                                                Admin Gudang
                                            @elseif($movement->creator->role === 'staff_gudang')
                                                Staff Gudang
                                            @else
                                                {{ ucfirst($movement->creator->role) }}
                                            @endif
                                        </small>
                                    @else
                                        <small class="text-muted">System</small>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->notes)
                                        <small>{{ $movement->notes }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($movements->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $movements->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-0">No movement history found for this item</p>
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
                    <h6 class="text-muted mb-3">Total Stock In</h6>
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
                    <h6 class="text-muted mb-3">Total Stock Out</h6>
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
                    <h6 class="text-muted mb-3">Adjustments</h6>
                    <h3 class="text-warning mb-0">
                        {{ number_format($movements->where('movement_type', 'adjustment')->count()) }}
                    </h3>
                    <small class="text-muted">Penyesuaian Stock</small>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
