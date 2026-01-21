@extends('layouts.app')

@section('page-title', 'Review Stock Request')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Review Stock Request #{{ $stockRequest->id }}</h4>
            <a href="{{ route('gudang.stock-requests.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to List
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

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Request Information</h5>
                    @if($stockRequest->status === 'pending')
                        <span class="badge bg-warning">
                            <i class="bi bi-clock me-1"></i>Pending
                        </span>
                    @elseif($stockRequest->status === 'approved')
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>Approved
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle me-1"></i>Rejected
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Request Date</label>
                        <p class="mb-0">{{ $stockRequest->created_at->format('d F Y, H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Requested By</label>
                        <p class="mb-0">{{ $stockRequest->staff->name }}</p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Item Name</label>
                        <p class="mb-0"><strong>{{ $stockRequest->item->name }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Item Code</label>
                        <p class="mb-0">{{ $stockRequest->item->code }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Category</label>
                        <p class="mb-0">
                            <span class="badge bg-secondary">{{ $stockRequest->item->category->name }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Warehouse</label>
                        <p class="mb-0">{{ $stockRequest->warehouse->name }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Requested Quantity</label>
                        <p class="mb-0"><strong class="fs-5">{{ $stockRequest->quantity }} {{ $stockRequest->item->unit }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Current Stock</label>
                        <p class="mb-0">
                            @php
                                $stockQty = $currentStock ? $currentStock->quantity : 0;
                            @endphp
                            <strong class="fs-5 {{ $stockQty >= $stockRequest->quantity ? 'text-success' : 'text-danger' }}">
                                {{ $stockQty }} {{ $stockRequest->item->unit }}
                            </strong>
                            @if($stockQty < $stockRequest->quantity)
                                <br><small class="text-danger">⚠️ Insufficient stock</small>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="text-muted small">Purpose/Reason</label>
                    <p class="mb-0">{{ $stockRequest->purpose }}</p>
                </div>

                @if($stockRequest->notes)
                    <div class="mb-3">
                        <label class="text-muted small">Additional Notes</label>
                        <p class="mb-0">{{ $stockRequest->notes }}</p>
                    </div>
                @endif

                @if($stockRequest->status !== 'pending')
                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Processed By</label>
                            <p class="mb-0">{{ $stockRequest->approver->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Processed Date</label>
                            <p class="mb-0">{{ $stockRequest->approved_at->format('d F Y, H:i') }}</p>
                        </div>
                    </div>

                    @if($stockRequest->status === 'rejected' && $stockRequest->rejection_reason)
                        <div class="alert alert-danger">
                            <strong>Rejection Reason:</strong><br>
                            {{ $stockRequest->rejection_reason }}
                        </div>
                    @endif
                @endif
            </div>
            
            @if($stockRequest->status === 'pending')
                <div class="card-footer bg-light">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <form action="{{ route('gudang.stock-requests.approve', $stockRequest) }}" 
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to approve this request? Stock will be deducted.');">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" 
                                        {{ ($currentStock && $currentStock->quantity >= $stockRequest->quantity) ? '' : 'disabled' }}>
                                    <i class="bi bi-check-circle me-1"></i>Approve Request
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-1"></i>Reject Request
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Stock Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Current Stock</label>
                    <h3 class="mb-0 {{ ($currentStock && $currentStock->quantity >= $stockRequest->quantity) ? 'text-success' : 'text-danger' }}">
                        {{ $currentStock ? $currentStock->quantity : 0 }} {{ $stockRequest->item->unit }}
                    </h3>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">After Approval</label>
                    <h3 class="mb-0">
                        {{ ($currentStock ? $currentStock->quantity : 0) - $stockRequest->quantity }} {{ $stockRequest->item->unit }}
                    </h3>
                </div>
                @if($currentStock && $currentStock->quantity < $stockRequest->quantity)
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <small>Stock tidak mencukupi untuk approve request ini.</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Request Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <p class="mb-0 small text-muted">{{ $stockRequest->created_at->format('d M Y, H:i') }}</p>
                            <p class="mb-0"><strong>Request Created</strong></p>
                            <p class="mb-0 small">By {{ $stockRequest->staff->name }}</p>
                        </div>
                    </div>

                    @if($stockRequest->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker {{ $stockRequest->status === 'approved' ? 'bg-success' : 'bg-danger' }}"></div>
                            <div class="timeline-content">
                                <p class="mb-0 small text-muted">{{ $stockRequest->approved_at->format('d M Y, H:i') }}</p>
                                <p class="mb-0"><strong>Request {{ ucfirst($stockRequest->status) }}</strong></p>
                                <p class="mb-0 small">By {{ $stockRequest->approver->name }}</p>
                            </div>
                        </div>
                    @else
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Waiting for Review</strong></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('gudang.stock-requests.reject', $stockRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        You are about to reject this stock request. Please provide a reason.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" 
                                  id="rejection_reason" 
                                  class="form-control" 
                                  rows="4" 
                                  required 
                                  placeholder="Explain why this request is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 20px;
    width: 2px;
    height: calc(100% - 10px);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -28px;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    padding: 0;
}
</style>
@endsection
