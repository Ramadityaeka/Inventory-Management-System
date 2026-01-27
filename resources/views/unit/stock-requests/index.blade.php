@extends('layouts.app')

@section('page-title', 'Stock Requests Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Stock Requests Management</h4>
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white">Pending Requests</h6>
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                    <i class="bi bi-clock-history display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white">Approved</h6>
                        <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                    </div>
                    <i class="bi bi-check-circle display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white">Rejected</h6>
                        <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                    </div>
                    <i class="bi bi-x-circle display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Staff</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Warehouse</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td><strong>#{{ $request->id }}</strong></td>
                                        <td>{{ $request->created_at ? $request->created_at->translatedFormat('d M Y') : 'N/A' }}</td>
                                        <td>{{ $request->staff ? $request->staff->name : 'Staff Deleted' }}</td>
                                        <td>
                                            <strong>{{ $request->item ? $request->item->name : 'Item Deleted' }}</strong><br>
                                            <small class="text-muted">{{ $request->item ? $request->item->code : 'N/A' }}</small>
                                        </td>
                                        <td>{{ $request->quantity }} {{ $request->item ? $request->item->unit : 'unit' }}</td>
                                        <td>{{ $request->warehouse ? $request->warehouse->name : 'Warehouse Deleted' }}</td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $request->purpose }}">
                                                {{ $request->purpose }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>Pending
                                                </span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Approved
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('unit.stock-requests.show', $request) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $requests->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">No stock requests found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
