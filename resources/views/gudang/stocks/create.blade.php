@extends('layouts.app')

@section('title', 'Tambah Stok')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Stok Barang
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('gudang.stocks.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="item_id" class="form-label">Barang <span class="text-danger">*</span></label>
                                    <select class="form-select @error('item_id') is-invalid @enderror" 
                                            id="item_id" name="item_id" required>
                                        <option value="">Pilih Barang</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" 
                                                    {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }} ({{ $item->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="warehouse_id" class="form-label">Unit <span class="text-danger">*</span></label>
                                    <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" name="warehouse_id" required>
                                        <option value="">Pilih Unit</option>
                                        @foreach($userWarehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" 
                                                    {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" value="{{ old('quantity') }}" 
                                           min="1" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" required>{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Simpan
                            </button>
                            <a href="{{ route('gudang.stocks.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
