@extends('layouts.app')

@section('page-title', 'Detail Submission - #' . $submission->id)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Detail Submission - #{{ $submission->id }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('staff.receive-items.index') }}">My Submissions</a></li>
                        <li class="breadcrumb-item active">Detail #{{ $submission->id }}</li>
                    </ol>
                </nav>
            </div>
            
            <div>
                @switch($submission->status)
                    @case('pending')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="bi bi-clock me-2"></i>Menunggu Verifikasi
                        </span>
                        @break
                    @case('approved')
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle me-2"></i>Telah Disetujui
                        </span>
                        @break
                    @case('rejected')
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-x-circle me-2"></i>Ditolak
                        </span>
                        @break
                @endswitch
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Informasi Barang</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Item</th>
                        <td>{{ $submission->item ? $submission->item->name : $submission->item_name }}</td>
                    </tr>
                    @if($submission->item)
                    <tr>
                        <th>Kategori</th>
                        <td>{{ $submission->item->category->name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Quantity</th>
                        <td><strong>{{ number_format($submission->quantity) }} {{ $submission->unit }}</strong></td>
                    </tr>
                    @if($submission->unit_price)
                    <tr>
                        <th><i class="bi bi-currency-dollar text-primary"></i> Harga per Satuan</th>
                        <td>
                            <span class="badge bg-info bg-opacity-25 text-info fs-6 fw-normal">
                                Rp {{ number_format($submission->unit_price, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @endif
                    @if($submission->total_price)
                    <tr>
                        <th><i class="bi bi-calculator text-success"></i> Total Harga</th>
                        <td>
                            <span class="badge bg-success bg-opacity-25 text-success fs-5 fw-bold">
                                Rp {{ number_format($submission->total_price, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @endif
                    @if($submission->supplier)
                    <tr>
                        <th>Supplier</th>
                        <td>{{ $submission->supplier->name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Gudang</th>
                        <td>{{ $submission->warehouse->name }}</td>
                    </tr>
                    <tr>
                        <th>Staff</th>
                        <td>{{ $submission->staff->name }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Submit</th>
                        <td>{{ $submission->submitted_at ? $submission->submitted_at->format('d F Y H:i') : '-' }}</td>
                    </tr>
                    @if($submission->notes)
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $submission->notes }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($submission->photos->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Foto Barang</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($submission->photos as $photo)
                            <div class="col-md-4">
                                <a href="{{ asset('storage/' . $photo->file_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" 
                                         class="img-fluid rounded" 
                                         style="width: 100%; height: 200px; object-fit: cover;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($submission->invoice_photo)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Foto Nota/Invoice</h6>
                </div>
                <div class="card-body">
                    @if(str_ends_with($submission->invoice_photo, '.pdf'))
                        <div class="alert alert-info">
                            <i class="bi bi-file-pdf"></i> 
                            <a href="{{ asset('storage/' . $submission->invoice_photo) }}" target="_blank">
                                Lihat PDF Invoice
                            </a>
                        </div>
                    @else
                        <a href="{{ asset('storage/' . $submission->invoice_photo) }}" target="_blank">
                            <img src="{{ asset('storage/' . $submission->invoice_photo) }}" 
                                 class="img-fluid rounded" 
                                 style="width: 100%;">
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Status Timeline</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @if($submission->submitted_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Submitted</h6>
                                    <small class="text-muted">{{ $submission->submitted_at->format('d M Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($submission->status === 'approved' && $submission->verified_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-success"></div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Approved</h6>
                                    <small class="text-muted">{{ $submission->verified_at->format('d M Y H:i') }}</small>
                                    @if($submission->admin_notes)
                                        <p class="mb-0 mt-2"><small>{{ $submission->admin_notes }}</small></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($submission->status === 'rejected' && $submission->verified_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Rejected</h6>
                                    <small class="text-muted">{{ $submission->verified_at->format('d M Y H:i') }}</small>
                                    @if($submission->rejection_reason)
                                        <div class="alert alert-danger mt-2 p-2">
                                            <small><strong>Alasan:</strong> {{ $submission->rejection_reason }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <a href="{{ route('staff.receive-items.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke List
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 5px;
}
</style>
@endsection
