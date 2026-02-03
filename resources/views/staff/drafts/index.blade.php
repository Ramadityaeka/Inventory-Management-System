@extends('layouts.app')

@section('page-title', 'Draft Submissions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Draft Submissions</h4>
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
                @if($drafts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Supplier</th>
                                    <th>Gudang</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($drafts as $draft)
                                    <tr>
                                        <td>#{{ $draft->id }}</td>
                                        <td>{{ formatDateIndo($draft->created_at) }} WIB</td>
                                        <td>{{ $draft->item->name }}</td>
                                        <td>{{ number_format($draft->quantity) }}</td>
                                        <td>{{ $draft->supplier->name }}</td>
                                        <td>{{ $draft->warehouse->name }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('staff.receive-items.edit', $draft) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <form method="POST" action="{{ route('staff.drafts.destroy', $draft) }}" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Yakin ingin menghapus draft ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $drafts->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada draft.</p>
                        <a href="{{ route('staff.receive-items.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Buat Submission Baru
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
