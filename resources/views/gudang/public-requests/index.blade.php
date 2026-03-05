@extends('layouts.app')

@section('page-title', 'Permintaan Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-inbox me-2 text-primary"></i>Permintaan Masuk
            @if($pendingCount > 0)
                <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
            @endif
        </h4>
        <p class="text-muted mb-0">Daftar permintaan barang dari publik yang masuk ke unit Anda.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label form-label-sm mb-1 text-muted">Kode</label>
                <input type="text" name="code" class="form-control form-control-sm" placeholder="Cari kode..." value="{{ request('code') }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label form-label-sm mb-1 text-muted">Nama Pemohon</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="Cari nama..." value="{{ request('name') }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label form-label-sm mb-1 text-muted">Unit</label>
                <input type="text" name="unit" class="form-control form-control-sm" placeholder="Cari unit..." value="{{ request('unit') }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label form-label-sm mb-1 text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-sm-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                @if(request()->hasAny(['code','name','unit','status']))
                    <a href="{{ route('gudang.public-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th>Kode</th>
                        <th>Pemohon</th>
                        <th>Unit</th>
                        <th>Barang</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $statusMap = [
                                'pending'   => ['label' => 'Menunggu',  'class' => 'warning'],
                                'approved'  => ['label' => 'Disetujui', 'class' => 'success'],
                                'partial'   => ['label' => 'Sebagian',  'class' => 'info'],
                                'rejected'  => ['label' => 'Ditolak',   'class' => 'danger'],
                                'completed' => ['label' => 'Selesai',   'class' => 'primary'],
                            ];
                            $s = $statusMap[$req->status] ?? ['label' => $req->status, 'class' => 'secondary'];
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary">{{ $req->request_code }}</span>
                            </td>
                            <td>{{ $req->requester_name }}</td>
                            <td>{{ $req->warehouse->name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $req->items->count() }} barang</span>
                            </td>
                            <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-{{ $s['class'] }}">{{ $s['label'] }}</span></td>
                            <td>
                                <a href="{{ route('gudang.public-requests.show', $req->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada permintaan masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
        <div class="card-footer">
            {{ $requests->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
