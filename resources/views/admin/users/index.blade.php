@extends('layouts.app')

@section('page-title', 'Manajemen User')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Manajemen User</h4>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar User</h6>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah User
        </a>
    </div>

    <div class="card-body">
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="search" class="form-label">Cari</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nama atau email">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">Semua Role</option>
                    <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin_gudang" {{ request('role') === 'admin_gudang' ? 'selected' : '' }}>Admin Gudang</option>
                    <option value="staff_gudang" {{ request('role') === 'staff_gudang' ? 'selected' : '' }}>Staff Gudang</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>
        </form>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Gudang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'super_admin')
                                    <span class="badge bg-danger">Super Admin</span>
                                @elseif($user->role === 'admin_gudang')
                                    <span class="badge bg-primary">Admin Gudang</span>
                                @elseif($user->role === 'staff_gudang')
                                    <span class="badge bg-success">Staff Gudang</span>
                                @else
                                    <span class="badge bg-secondary">{{ $user->role }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->warehouses->count() > 0)
                                    {{ $user->warehouses->pluck('name')->join(', ') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           id="status-{{ $user->id }}"
                                           data-user-id="{{ $user->id }}"
                                           {{ $user->is_active ? 'checked' : '' }}
                                           onchange="toggleStatus(this)">
                                    <label class="form-check-label" for="status-{{ $user->id }}">
                                        {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-danger"
                                            onclick="openDeleteModal({{ $user->id }}, {{ json_encode($user->name) }})"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-people text-muted fs-1 mb-3"></i>
                                <p class="text-muted mb-0">Tidak ada user ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleStatus(checkbox) {
        const userId = checkbox.dataset.userId;
        const isActive = checkbox.checked;
        const originalState = !isActive; // Store original state for reverting

        // Disable checkbox during request
        checkbox.disabled = true;

        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_active: isActive })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Re-enable checkbox
            checkbox.disabled = false;

            if (data.success) {
                // Ensure checkbox state matches server response
                checkbox.checked = data.is_active;
                
                // Update the label text
                const label = checkbox.nextElementSibling;
                label.textContent = data.is_active ? 'Aktif' : 'Tidak Aktif';

                // Show success message
                showAlert('success', data.message);
            } else {
                // Revert checkbox if error
                checkbox.checked = originalState;
                showAlert('error', data.message || 'Failed to update user status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Re-enable and revert checkbox on error
            checkbox.disabled = false;
            checkbox.checked = originalState;
            showAlert('error', 'An error occurred while updating user status.');
        });
    }

    function resetPassword(userId) {
        if (confirm('Apakah Anda yakin ingin mereset password user ini? Password baru akan dikirim melalui notifikasi.')) {
            fetch(`/admin/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    showAlert('error', 'Failed to reset password.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while resetting password.');
            });
        }
    }

    // Delete with Bootstrap modal confirmation
    function openDeleteModal(userId, userName) {
        const modalEl = document.getElementById('confirmDeleteModal');
        const deleteForm = document.getElementById('deleteUserForm');
        const userNameEl = document.getElementById('deleteUserName');

        if (!deleteForm) return;

        // set form action
        deleteForm.action = `/admin/users/${userId}`;
        // set name in modal
        if (userNameEl) userNameEl.textContent = userName;

        // show modal
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    // submit delete form when confirm clicked
    document.addEventListener('DOMContentLoaded', function() {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                const deleteForm = document.getElementById('deleteUserForm');
                if (deleteForm) deleteForm.submit();
            });
        }
    });

    function showAlert(type, message) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Add to page
        document.body.appendChild(alertDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
</script>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menghapus user: <strong id="deleteUserName"></strong></p>
                    <p class="text-danger">Tindakan ini akan menghapus user dari database dan tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Hapus User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush