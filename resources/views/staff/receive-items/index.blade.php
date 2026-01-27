@extends('layouts.app')

@section('page-title', 'My Submissions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">My Submissions</h4>
            <a href="{{ route('staff.receive-items.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Input Barang Baru
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($submissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Harga Satuan</th>
                                    <th>Total Harga</th>
                                    <th>Supplier</th>
                                    <th>Gudang</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td>#{{ $submission->id }}</td>
                                        <td>{{ $submission->submitted_at ? $submission->submitted_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $submission->item ? $submission->item->name : $submission->item_name }}</td>
                                        <td>{{ number_format($submission->quantity) }} {{ $submission->unit }}</td>
                                        <td>
                                            @if($submission->unit_price)
                                                <span class="text-muted">Rp</span> {{ number_format($submission->unit_price, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($submission->total_price)
                                                <strong class="text-success">Rp {{ number_format($submission->total_price, 0, ',', '.') }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $submission->supplier ? $submission->supplier->name : '-' }}</td>
                                        <td>{{ $submission->warehouse->name }}</td>
                                        <td>
                                            @switch($submission->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Menunggu</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">Diterima</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('staff.receive-items.show', $submission) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $submissions->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada submission.</p>
                        <a href="{{ route('staff.receive-items.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Buat Submission Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
