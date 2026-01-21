@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-house-door fs-1 text-primary"></i>
                </div>
                <h2 class="mb-3">Selamat Datang di Inventory ESDM</h2>
                <p class="text-muted mb-4">
                    Sistem manajemen inventori untuk Kementerian Energi dan Sumber Daya Mineral
                </p>
                
                @if(auth()->user())
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Halo, {{ auth()->user()->name }}!</strong><br>
                        Role Anda: <span class="badge bg-primary">{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</span>
                        
                        @if(auth()->user()->warehouses->count() > 0)
                            <br>Gudang yang ditetapkan: 
                            @foreach(auth()->user()->warehouses as $warehouse)
                                <span class="badge bg-success">{{ $warehouse->name }}</span>
                            @endforeach
                        @endif
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div><i class="bi bi-exclamation-triangle me-2"></i>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <i class="bi bi-box-seam fs-2 text-primary mb-2"></i>
                                <h5>Manajemen Inventori</h5>
                                <p class="text-muted small">Kelola stok barang dengan mudah dan efisien</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <i class="bi bi-building fs-2 text-success mb-2"></i>
                                <h5>Multi Gudang</h5>
                                <p class="text-muted small">Pantau stok di berbagai lokasi gudang</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <i class="bi bi-graph-up fs-2 text-warning mb-2"></i>
                                <h5>Laporan Real-time</h5>
                                <p class="text-muted small">Analisis dan laporan stok secara real-time</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection