@extends('layouts.app')

@section('page-title', 'Stock Alerts')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Stock Alerts</h4>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-bell fs-4 text-primary"></i>
                    </div>
                </div>
                <h3 class="mb-1">{{ number_format($statistics['total']) }}</h3>
                <p class="text-muted mb-0 small">Total Alerts</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-bell-fill fs-4 text-danger"></i>
                    </div>
                </div>
                <h3 class="mb-1">{{ number_format($statistics['unread']) }}</h3>
                <p class="text-muted mb-0 small">Unread</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                    </div>
                </div>
                <h3 class="mb-1">{{ number_format($statistics['low_stock']) }}</h3>
                <p class="text-muted mb-0 small">Low Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-x-circle fs-4 text-danger"></i>
                    </div>
                </div>
                <h3 class="mb-1">{{ number_format($statistics['out_of_stock']) }}</h3>
                <p class="text-muted mb-0 small">Out of Stock</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('gudang.alerts') }}" class="row g-3">
            <div class="col-md-4">
                <label for="alert_type" class="form-label">Alert Type</label>
                <select class="form-select" id="alert_type" name="alert_type">
                    <option value="">All Types</option>
                    <option value="low_stock" {{ request('alert_type') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('alert_type') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('gudang.alerts') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Alerts List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Alert List</h6>
        @if($statistics['unread'] > 0)
            <span class="badge bg-danger">{{ $statistics['unread'] }} unread</span>
        @endif
    </div>
    <div class="card-body">
        @if($alerts->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($alerts as $alert)
                    <div class="list-group-item {{ !$alert->is_read ? 'bg-light' : '' }}">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    @if($alert->alert_type === 'out_of_stock')
                                        <span class="badge bg-danger me-2">
                                            <i class="bi bi-x-circle me-1"></i>Out of Stock
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark me-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Low Stock
                                        </span>
                                    @endif
                                    
                                    @if(!$alert->is_read)
                                        <span class="badge bg-primary">New</span>
                                    @endif
                                </div>
                                
                                <h6 class="mb-1">{{ $alert->item->name }}</h6>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-building me-1"></i>{{ $alert->warehouse->name ?? '-' }}
                                    </small>
                                </div>
                                
                                <div class="row g-2">
                                    <div class="col-auto">
                                        <small class="text-muted">Current Stock:</small>
                                        <strong class="{{ $alert->current_stock == 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($alert->current_stock) }}
                                        </strong>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted">Unit:</small>
                                        <strong>{{ $alert->item->unit }}</strong>
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $alert->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                            
                            <div class="ms-3">
                                @if(!$alert->is_read)
                                    <form action="{{ route('gudang.alerts.markAsRead', $alert->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as read">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary">Read</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($alerts->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $alerts->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">No Alerts</h5>
                <p class="text-muted mb-0">
                    @if(request()->hasAny(['alert_type', 'status']))
                        Tidak ada alert dengan filter yang dipilih.
                    @else
                        Tidak ada stock alert saat ini. Semua stok dalam kondisi baik!
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Info Card -->
<div class="card mt-4">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Tentang Stock Alerts</h6>
        <ul class="mb-0">
            <li><strong>Out of Stock Alert:</strong> Muncul ketika stock mencapai 0 (habis).</li>
            <li><strong>Action:</strong> Segera lakukan stock adjustment atau hubungi supplier untuk restock barang.</li>
            <li>Alert akan otomatis dibuat oleh sistem saat stock habis.</li>
        </ul>
    </div>
</div>
@endsection
