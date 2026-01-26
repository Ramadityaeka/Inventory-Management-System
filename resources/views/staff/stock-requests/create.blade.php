@extends('layouts.app')

@section('page-title', 'Create Stock Request')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Create Stock Request</h4>
            <a href="{{ route('staff.stock-requests.my-requests') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to My Requests
            </a>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Request Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('staff.stock-requests.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id', request('warehouse_id')) == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="item_id" class="form-label">Item <span class="text-danger">*</span></label>
                        <select name="item_id" id="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item['id'] }}" 
                                        data-unit="{{ $item['unit'] }}"
                                        data-warehouse="{{ $item['warehouse_id'] }}"
                                        data-quantity="{{ $item['quantity'] }}"
                                        {{ old('item_id', request('item_id')) == $item['id'] ? 'selected' : '' }}>
                                    {{ $item['name'] }} ({{ $item['code'] }}) - {{ $item['warehouse_name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('item_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small id="available-stock" class="form-text text-muted"></small>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="quantity" 
                               id="quantity" 
                               class="form-control @error('quantity') is-invalid @enderror" 
                               value="{{ old('quantity') }}" 
                               min="1" 
                               required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Enter the quantity you need</small>
                    </div>

                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose/Reason <span class="text-danger">*</span></label>
                        <textarea name="purpose" 
                                  id="purpose" 
                                  class="form-control @error('purpose') is-invalid @enderror" 
                                  rows="3" 
                                  required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Explain why you need this item</small>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea name="notes" 
                                  id="notes" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('staff.stock-requests.my-requests') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title text-white">
                    <i class="bi bi-info-circle me-2"></i>Information
                </h5>
                <ul class="mb-0 ps-3">
                    <li>Select the warehouse where the item is located</li>
                    <li>Choose the item you need from the dropdown</li>
                    <li>Enter the quantity you want to request</li>
                    <li>Provide a clear reason for your request</li>
                    <li>Your request will be reviewed by admin gudang</li>
                    <li>You'll receive a notification when your request is processed</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSelect = document.getElementById('warehouse_id');
    const itemSelect = document.getElementById('item_id');
    const quantityInput = document.getElementById('quantity');
    const availableStockText = document.getElementById('available-stock');

    // Filter items based on selected warehouse
    warehouseSelect.addEventListener('change', function() {
        const selectedWarehouse = this.value;
        const options = itemSelect.querySelectorAll('option');
        
        // Reset item selection
        itemSelect.value = '';
        availableStockText.textContent = '';
        
        options.forEach(option => {
            if (option.value === '') return; // Skip the placeholder
            
            const itemWarehouse = option.dataset.warehouse;
            if (selectedWarehouse === '' || itemWarehouse === selectedWarehouse) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Show available stock when item is selected
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value !== '') {
            const unit = selectedOption.dataset.unit;
            const quantity = selectedOption.dataset.quantity;
            availableStockText.textContent = `Tersedia: ${quantity} ${unit}`;
            availableStockText.className = quantity > 0 ? 'form-text text-success' : 'form-text text-danger';
            quantityInput.max = quantity;
        } else {
            availableStockText.textContent = '';
        }
    });

    // Trigger change event if item is pre-selected (from URL parameter)
    if (itemSelect.value !== '') {
        itemSelect.dispatchEvent(new Event('change'));
    }
    
    // Trigger warehouse filter if warehouse is pre-selected
    if (warehouseSelect.value !== '') {
        warehouseSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
