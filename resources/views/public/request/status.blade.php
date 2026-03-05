<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cek Status Permohonan - Inventory ESDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f4f8; font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .status-card { background: white; border-radius: 16px; padding: 50px 40px; max-width: 480px; width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="status-card">
        <div class="text-center mb-4">
            <i class="bi bi-search fs-1 text-primary"></i>
            <h3 class="fw-bold mt-2 mb-1">Cek Status Permohonan</h3>
            <p class="text-muted">Masukkan kode permohonan yang Anda terima saat submit.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <form action="{{ route('public.request.find-status') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Kode Permohonan</label>
                <input type="text" name="request_code" class="form-control form-control-lg text-center"
                       placeholder="REQ-20260303-0001"
                       value="{{ old('request_code') }}"
                       style="letter-spacing: 2px; font-weight: 600;"
                       required autofocus>
                @error('request_code')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-search me-2"></i>Cari Permohonan
                </button>
            </div>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
            </a>
            <span class="text-muted mx-2">|</span>
            <a href="{{ route('public.request.create') }}" class="text-muted text-decoration-none small">
                <i class="bi bi-clipboard-plus me-1"></i>Ajukan Permohonan Baru
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
