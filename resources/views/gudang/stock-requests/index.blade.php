@extends('layouts.app')

@section('page-title', 'Manajemen Permintaan barang')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">   </h4>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white">Permintaan tertunda</h6>
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
                        <h6 class="text-white">Permintaan Diterima</h6>
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
                        <h6 class="text-white">Permintaan Ditolak</h6>
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
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Permintaan tertunda</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Permintaan Diterima</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Permintaan Ditolak</option>
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
                                    <th>Tanggal</th>
                                    <th>Staff</th>
                                    <th>Barang</th>
                                    <th>Kuantitas Barang</th>
                                    <th>Unit</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td><strong>#{{ $request->id }}</strong></td>
                                        <td>{{ $request->created_at ? $request->created_at->format('d M Y') : 'N/A' }}</td>
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
                                                    <i class="bi bi-clock me-1"></i>Tertunda
                                                </span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Diterima
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Ditolak
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('gudang.stock-requests.show', $request) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $requests->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">Tidak ada permintaan barang.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
