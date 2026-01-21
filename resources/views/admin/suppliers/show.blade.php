@extends('layouts.app')

@section('page-title', 'Detail Supplier')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Detail Supplier</h4>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Informasi Supplier</h6>
        <div>
            @if($supplier->is_active)
                <span class="badge bg-success me-2"><i class="bi bi-check-circle me-1"></i>Aktif</span>
            @else
                <span class="badge bg-secondary me-2"><i class="bi bi-x-circle me-1"></i>Tidak Aktif</span>
            @endif
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Kode Supplier</th>
                        <td><code>{{ $supplier->code }}</code></td>
                    </tr>
                    <tr>
                        <th>Nama Supplier</th>
                        <td><strong>{{ $supplier->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Kontak Person</th>
                        <td>{{ $supplier->contact_person ?: '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Telepon</th>
                        <td>
                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}">
                                    <i class="bi bi-telephone me-1"></i>{{ $supplier->phone }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}">
                                    <i class="bi bi-envelope me-1"></i>{{ $supplier->email }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $supplier->address ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">{{ $supplier->items->count() }}</h5>
                            <small class="text-muted">Total Item</small>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-0">{{ $supplier->submissions->count() }}</h5>
                            <small class="text-muted">Total Pengajuan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Items dari Supplier -->
@if($supplier->items->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Daftar Item dari Supplier Ini</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Kode Item</th>
                        <th>Nama Item</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplier->items as $item)
                        <tr>
                            <td><code>{{ $item->code }}</code></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->name ?? '-' }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Pengajuan dari Supplier -->
@if($supplier->submissions->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Riwayat Pengajuan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Kode Pengajuan</th>
                        <th>Tanggal</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplier->submissions->take(10) as $submission)
                        <tr>
                            <td><code>{{ $submission->code ?? '-' }}</code></td>
                            <td>{{ $submission->created_at ? $submission->created_at->format('d/m/Y') : '-' }}</td>
                            <td>{{ $submission->item_name ?? ($submission->item ? $submission->item->name : '-') }}</td>
                            <td>{{ $submission->quantity }} {{ $submission->unit }}</td>
                            <td>
                                @if($submission->status === 'pending')
                                    <span class="badge bg-warning">Menunggu</span>
                                @elseif($submission->status === 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @elseif($submission->status === 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                @else
                                    <span class="badge bg-secondary">{{ $submission->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($supplier->submissions->count() > 10)
            <p class="text-muted text-center mb-0 mt-2">
                <small>Menampilkan 10 pengajuan terakhir dari {{ $supplier->submissions->count() }} total pengajuan</small>
            </p>
        @endif
    </div>
</div>
@endif

<div class="d-flex justify-content-between">
    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
    </a>
</div>
@endsection
