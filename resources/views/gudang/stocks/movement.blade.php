@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-arrow-left-right me-2"></i>Riwayat Pergerakan Stok
                    </h4>
                    <a href="{{ route('gudang.stocks.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Stok
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="warehouse_id" class="form-label">Gudang</label>
                                <select class="form-select" id="warehouse_id" name="warehouse_id">
                                    <option value="">Semua Gudang</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" 
                                                {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="item_id" class="form-label">Barang</label>
                                <select class="form-select" id="item_id" name="item_id">
                                    <option value="">Semua Barang</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" 
                                                {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="movement_type" class="form-label">Tipe</label>
                                <select class="form-select" id="movement_type" name="movement_type">
                                    <option value="">Semua Tipe</option>
                                    @foreach($movementTypes as $key => $label)
                                        <option value="{{ $key }}" 
                                                {{ request('movement_type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('gudang.stock.movement') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                    
                    <!-- Movements Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Barang</th>
                                    <th>Unit</th>
                                    <th>Supplier</th>
                                    <th>Diajukan Oleh</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                    <th>Disetujui Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                    <tr>
                                        <td>
                                            <div>{{ $movement->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $movement->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $movement->item->name }}</strong><br>
                                            <small class="text-muted">{{ $movement->item->code }}</small>
                                            @if($movement->item->category)
                                                <br><span class="badge bg-info bg-opacity-10 text-info">{{ $movement->item->category->name }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $movement->warehouse->name }}</span>
                                        </td>
                                        <td>
                                            @if($movement->submission && $movement->submission->supplier)
                                                <small>
                                                    <strong>{{ $movement->submission->supplier->name }}</strong>
                                                    @if($movement->submission->supplier->phone)
                                                        <br><span class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $movement->submission->supplier->phone }}</span>
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
                                                         style="width: 28px; height: 28px; background-color: #28a745; color: white; font-size: 0.75rem; font-weight: bold;">
                                                        {{ strtoupper(substr($movement->submission->staff->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <small><strong>{{ $movement->submission->staff->name }}</strong></small>
                                                        <br><small class="text-muted" style="font-size: 0.7rem;">Staff Unit</small>
                                                    </div>
                                                </div>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($movement->movement_type == \App\Models\StockMovement::MOVEMENT_TYPE_IN)
                                                <span class="badge bg-success">Masuk</span>
                                            @elseif($movement->movement_type == \App\Models\StockMovement::MOVEMENT_TYPE_OUT)
                                                <span class="badge bg-danger">Keluar</span>
                                            @elseif($movement->movement_type == \App\Models\StockMovement::MOVEMENT_TYPE_ADJUSTMENT)
                                                <span class="badge bg-warning">Penyesuaian</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($movement->movement_type) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($movement->notes ?? '-', 50) }}</small>
                                        </td>
                                        <td>
                                            @if($movement->creator)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-initial rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 28px; height: 28px; background-color: #007bff; color: white; font-size: 0.75rem; font-weight: bold;">
                                                        {{ strtoupper(substr($movement->creator->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <small><strong>{{ $movement->creator->name }}</strong></small>
                                                        <br><small class="text-muted" style="font-size: 0.7rem;">
                                                            @if($movement->creator->role === 'super_admin')
                                                                Super Admin
                                                            @elseif($movement->creator->role === 'admin_gudang')
                                                                Admin Unit
                                                            @elseif($movement->creator->role === 'staff_gudang')
                                                                Staff Unit
                                                            @else
                                                                {{ ucfirst($movement->creator->role) }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <small class="text-muted">System</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="bi bi-info-circle fs-1 text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Tidak ada data pergerakan stok</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($movements->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $movements->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
