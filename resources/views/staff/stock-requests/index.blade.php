@extends('layouts.app')

@section('page-title', 'Stok Tersedia')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Stok Tersedia</h4>
            <div>
                <a href="{{ route('staff.stock-requests.my-requests') }}" class="btn btn-info me-2">
                    <i class="bi bi-clock-history me-1"></i>My Requests
                </a>
                <a href="{{ route('staff.stock-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Request Item
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cari Barang</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama atau kode barang" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Warehouse</label>
                        <select name="warehouse" class="form-select">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                @if($stocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Warehouse</th>
                                    <th>Available Quantity</th>
                                    <th>Unit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                    <tr>
                                        <td><strong>{{ $stock->item->code }}</strong></td>
                                        <td>{{ $stock->item->name }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $stock->item->category->name }}</span>
                                        </td>
                                        <td>{{ $stock->warehouse->name }}</td>
                                        <td>
                                            @if($stock->quantity > 0)
                                                <span class="badge bg-success">{{ $stock->quantity }}</span>
                                            @else
                                                <span class="badge bg-danger">0</span>
                                            @endif
                                        </td>
                                        <td>{{ $stock->item->unit }}</td>
                                        <td>
                                            @if($stock->quantity > 0)
                                                <a href="{{ route('staff.stock-requests.create', ['item_id' => $stock->item_id, 'warehouse_id' => $stock->warehouse_id]) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-box-arrow-right me-1"></i>Request
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled>Out of Stock</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $stocks->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">No stock available in your warehouses.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
