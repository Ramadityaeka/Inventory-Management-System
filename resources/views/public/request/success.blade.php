<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permohonan Berhasil - Inventory ESDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f4f8; font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-card { background: white; border-radius: 16px; padding: 50px 40px; text-align: center; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .success-icon { font-size: 5rem; color: #198754; }
        .request-code { font-size: 2rem; font-weight: 700; letter-spacing: 3px; color: #0d6efd; background: #e7f1ff; padding: 15px 25px; border-radius: 10px; display: inline-block; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon mb-3">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h3 class="fw-bold text-success mb-1">Permohonan Berhasil Diajukan!</h3>
        <p class="text-muted mb-3">Simpan kode di bawah ini dan gunakan untuk melacak status permohonan Anda.</p>

        <div class="request-code" id="req-code">{{ $code }}</div>

        <div class="d-grid gap-2 mb-2">
            <button class="btn btn-outline-primary" onclick="copyCode()">
                <i class="bi bi-clipboard me-2"></i>Salin Kode
            </button>
        </div>

        <div class="alert alert-warning text-start small mt-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Penting!</strong> Simpan kode ini baik-baik. Anda akan membutuhkannya untuk mengecek status permohonan.
        </div>

        <hr>

        <p class="text-muted small mb-3">
            Permohonan Anda telah diterima dan sedang menunggu proses dari PIC yang bertanggung jawab.
            Anda dapat memantau perkembangan melalui halaman status.
        </p>

        <div class="d-grid gap-2">
            <a href="{{ route('public.request.status') }}" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>Cek Status Permohonan
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                <i class="bi bi-house me-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function copyCode() {
        const code = document.getElementById('req-code').textContent;
        navigator.clipboard.writeText(code).then(() => {
            alert('Kode berhasil disalin: ' + code);
        });
    }
    </script>
</body>
</html>
