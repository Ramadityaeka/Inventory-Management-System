@extends('layouts.app')

@section('page-title', 'Tambah User')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Tambah User</h4>
    </div>
</div>

<!-- Validation Errors -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Terjadi kesalahan validasi:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('admin.users.store') }}" id="userForm">
    @csrf

    <!-- Informasi User Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Informasi User</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" required minlength="8">
                    <div class="form-text">Minimal 8 karakter</div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telepon</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                           id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror"
                            id="role" name="role" required onchange="toggleWarehouseSection()">
                        <option value="">Pilih Role</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin_unit" {{ old('role') === 'admin_unit' ? 'selected' : '' }}>Admin Unit</option>
                        <option value="staff_gudang" {{ old('role') === 'staff_gudang' ? 'selected' : '' }}>Staff Gudang</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Gudang Card -->
    <div class="card mb-4" id="warehouseCard" style="display: none;">
        <div class="card-header">
            <h6 class="mb-0">Assign Gudang</h6>
        </div>
        <div class="card-body">
            <div id="adminGudangSection" style="display: none;">
                <p class="text-muted mb-3">Pilih <strong>satu</strong> gudang untuk admin gudang:</p>
                @if($warehouses->count() > 0)
                    <div class="mb-3">
                        <label for="warehouse_select" class="form-label">Gudang <span class="text-danger">*</span></label>
                        <select class="form-select @error('warehouse') is-invalid @enderror"
                                id="warehouse_select" name="warehouse">
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }} - {{ $warehouse->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-building text-muted fs-1 mb-3"></i>
                        <p class="text-muted mb-0">Tidak ada gudang yang tersedia.</p>
                    </div>
                @endif
            </div>
            
            <div id="staffGudangSection" style="display: none;">
                <p class="text-muted mb-3">Pilih gudang yang akan di-assign ke staff gudang:</p>
                @if($warehouses->count() > 0)
                    <div class="row">
                        @foreach($warehouses as $warehouse)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input warehouse-checkbox @error('warehouses') is-invalid @enderror"
                                           type="checkbox" id="warehouse-staff-{{ $warehouse->id }}"
                                           name="warehouses[]" value="{{ $warehouse->id }}"
                                           {{ in_array($warehouse->id, old('warehouses', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="warehouse-staff-{{ $warehouse->id }}">
                                        {{ $warehouse->name }}
                                        <small class="text-muted d-block">{{ $warehouse->location }}</small>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @error('warehouses')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            @else
                <div class="text-center py-4">
                    <i class="bi bi-building text-muted fs-1 mb-3"></i>
                    <p class="text-muted mb-0">Tidak ada gudang yang tersedia.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Status Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Status</h6>
        </div>
        <div class="card-body">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">
                    User aktif
                </label>
                <div class="form-text">User yang aktif dapat login ke sistem</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize warehouse section visibility on page load
        toggleWarehouseSection();

        // Handle form submission validation
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const role = document.getElementById('role').value;
            
            if (role === 'admin_unit') {
                const warehouseSelect = document.getElementById('warehouse_select');
                if (warehouseSelect && !warehouseSelect.value) {
                    e.preventDefault();
                    alert('Pilih satu gudang untuk admin gudang.');
                    return false;
                }
            } else if (role === 'staff_gudang') {
                const warehouseCheckboxes = document.querySelectorAll('.warehouse-checkbox:checked');
                if (warehouseCheckboxes.length === 0) {
                    e.preventDefault();
                    alert('Pilih setidaknya satu gudang untuk staff gudang.');
                    return false;
                }
            }
        });
    });

    function toggleWarehouseSection() {
        const role = document.getElementById('role').value;
        const warehouseCard = document.getElementById('warehouseCard');
        const adminSection = document.getElementById('adminGudangSection');
        const staffSection = document.getElementById('staffGudangSection');
        const warehouseSelect = document.getElementById('warehouse_select');
        const warehouseCheckboxes = document.querySelectorAll('.warehouse-checkbox');

        if (role === 'super_admin') {
            // Hide warehouse section completely
            warehouseCard.style.display = 'none';
            // Clear all selections
            if (warehouseSelect) {
                warehouseSelect.value = '';
                warehouseSelect.required = false;
            }
            warehouseCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.required = false;
            });
        } else if (role === 'admin_unit') {
            // Show warehouse section with select dropdown for single selection
            warehouseCard.style.display = 'block';
            adminSection.style.display = 'block';
            staffSection.style.display = 'none';
            // Clear staff selections
            warehouseCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.required = false;
            });
            // Make select required
            if (warehouseSelect) {
                warehouseSelect.required = true;
            }
        } else if (role === 'staff_gudang') {
            // Show warehouse section with checkboxes for multiple selection
            warehouseCard.style.display = 'block';
            adminSection.style.display = 'none';
            staffSection.style.display = 'block';
            // Clear admin selections
            if (warehouseSelect) {
                warehouseSelect.value = '';
                warehouseSelect.required = false;
            }
            // Make checkboxes required (at least one)
            warehouseCheckboxes.forEach(checkbox => {
                checkbox.required = false; // Will validate manually
            });
        } else {
            // Hide for unknown roles
            warehouseCard.style.display = 'none';
        }
    }
</script>
@endpush