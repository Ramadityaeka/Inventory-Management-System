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
                                        <td>{{ $draft->warehouse->name ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('staff.receive-items.edit', $draft) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal"
                                                        data-draft-id="{{ $draft->id }}"
                                                        data-item-name="{{ $draft->item_name }}"
                                                        onclick="setDeleteModalData(this)">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
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

<!-- Delete Draft Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trash me-2"></i>Konfirmasi Hapus Draft
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Apakah Anda yakin ingin menghapus draft ini?</h6>
                
                <div class="alert alert-info">
                    <strong>Barang:</strong> <span id="modalDraftItemName"></span>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Data draft akan dihapus permanen dan tidak dapat dikembalikan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Batal
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Ya, Hapus Draft
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function setDeleteModalData(button) {
    const draftId = button.getAttribute('data-draft-id');
    const itemName = button.getAttribute('data-item-name');
    
    // Update modal content
    document.getElementById('modalDraftItemName').textContent = itemName;
    
    // Update form action
    const form = document.getElementById('deleteForm');
    form.action = `/staff/drafts/${draftId}`;
}
</script>
@endpush
@endsection
