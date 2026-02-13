@extends('layouts.app')

@section('page-title', 'Notifikasi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-bell me-2"></i>Notifikasi
            </h4>
            <div class="btn-group">
                @if($notifications->where('is_read', false)->count() > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-all me-1"></i>Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
                @if($notifications->count() > 0)
                    <div class="btn-group ms-2" role="group">
                        <button type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($notifications->where('is_read', true)->count() > 0)
                                <li>
                                    <button type="button" 
                                            class="dropdown-item" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteReadModal">
                                        <i class="bi bi-check-circle me-2"></i>Hapus Yang Sudah Dibaca
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <button type="button" 
                                        class="dropdown-item text-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteAllModal">
                                    <i class="bi bi-trash me-2"></i>Hapus Semua Notifikasi
                                </button>
                            </li>
                        </ul>
                    </div>
                @endif
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

<div class="card">
    <div class="card-body">
        @if($notifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item {{ $notification->is_read ? '' : 'bg-light border-start border-primary border-4' }}">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    @if(!$notification->is_read)
                                        <span class="badge bg-primary me-2">Baru</span>
                                    @endif
                                    <h6 class="mb-0">
                                        <i class="bi bi-{{ $notification->type === 'warning' ? 'exclamation-triangle text-warning' : ($notification->type === 'info' ? 'info-circle text-info' : 'bell') }} me-1"></i>
                                        {{ $notification->title }}
                                    </h6>
                                </div>
                                <p class="mb-2 text-muted">{{ $notification->message }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                    @if($notification->is_read)
                                        <span class="ms-3">
                                            <i class="bi bi-check-circle text-success me-1"></i>Dibaca {{ $notification->read_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </small>
                            </div>
                            <div class="ms-3 d-flex gap-2">
                                @if(!$notification->is_read)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary mark-read-btn" 
                                            data-notification-id="{{ $notification->id }}"
                                            title="Tandai dibaca">
                                        <i class="bi bi-check"></i>
                                    </button>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i>
                                    </span>
                                @endif
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger delete-btn" 
                                        data-notification-id="{{ $notification->id }}"
                                        title="Hapus notifikasi">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Tidak Ada Notifikasi</h5>
                <p class="text-muted">Anda belum memiliki notifikasi.</p>
            </div>
        @endif
    </div>
</div>

<!-- Statistics -->
@if($notifications->count() > 0)
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center">
                                <i class="bi bi-envelope-fill text-white fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Notifikasi</p>
                            <h4 class="mb-0">{{ $notifications->total() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning d-flex align-items-center justify-content-center">
                                <i class="bi bi-envelope-exclamation-fill text-white fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Belum Dibaca</p>
                            <h4 class="mb-0">{{ $notifications->where('is_read', false)->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 3rem;
        height: 3rem;
    }
    
    .list-group-item {
        transition: all 0.3s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa !important;
    }
    
    .border-start {
        border-left-width: 4px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mark single notification as read
        document.querySelectorAll('.mark-read-btn').forEach(button => {
            button.addEventListener('click', function() {
                const notificationId = this.dataset.notificationId;
                const listItem = this.closest('.list-group-item');
                
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove "Baru" badge
                        const badge = listItem.querySelector('.badge.bg-primary');
                        if (badge) badge.remove();
                        
                        // Remove border highlight
                        listItem.classList.remove('bg-light', 'border-start', 'border-primary', 'border-4');
                        
                        // Replace button with success badge
                        this.parentElement.innerHTML = `
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i>
                            </span>
                        `;
                        
                        // Update unread count in statistics
                        const unreadCard = document.querySelector('.col-md-6:last-child h4');
                        if (unreadCard) {
                            const currentCount = parseInt(unreadCard.textContent);
                            if (currentCount > 0) {
                                unreadCard.textContent = currentCount - 1;
                            }
                        }
                        
                        // Hide "Mark All as Read" button if no unread left
                        const unreadCount = document.querySelectorAll('.mark-read-btn').length;
                        if (unreadCount === 1) { // This was the last one
                            const markAllBtn = document.querySelector('form[action*="read-all"]');
                            if (markAllBtn) {
                                markAllBtn.style.display = 'none';
                            }
                        }
                        
                        // Show toast notification (optional)
                        showToast('Notifikasi ditandai sebagai dibaca');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan', 'error');
                });
            });
        });

        // Delete single notification
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!confirm('Hapus notifikasi ini?')) {
                    return;
                }

                const notificationId = this.dataset.notificationId;
                const listItem = this.closest('.list-group-item');
                
                fetch(`/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animate and remove the notification
                        listItem.style.transition = 'opacity 0.3s ease';
                        listItem.style.opacity = '0';
                        
                        setTimeout(() => {
                            listItem.remove();
                            
                            // Update total count
                            const totalCard = document.querySelector('.col-md-6:first-child h4');
                            if (totalCard) {
                                const currentTotal = parseInt(totalCard.textContent);
                                if (currentTotal > 0) {
                                    totalCard.textContent = currentTotal - 1;
                                }
                            }
                            
                            // Check if no notifications left
                            if (document.querySelectorAll('.list-group-item').length === 0) {
                                location.reload(); // Reload to show "no notifications" message
                            }
                        }, 300);
                        
                        showToast('Notifikasi berhasil dihapus');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan', 'error');
                });
            });
        });
    });
    
    function showToast(message, type = 'success') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
@endpush

<!-- Delete Read Notifications Modal -->
<div class="modal fade" id="deleteReadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Konfirmasi Hapus Notifikasi Terbaca
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-question-circle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Hapus semua notifikasi yang sudah dibaca?</h6>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Notifikasi yang belum dibaca tidak akan terhapus.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Batal
                </button>
                <form action="{{ route('notifications.delete-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>Ya, Hapus Yang Terbaca
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete All Notifications Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trash me-2"></i>Konfirmasi Hapus Semua Notifikasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">Hapus SEMUA notifikasi?</h6>
                
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan! Semua notifikasi (yang dibaca dan belum dibaca) akan dihapus permanen.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Batal
                </button>
                <form action="{{ route('notifications.delete-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Ya, Hapus Semua
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
