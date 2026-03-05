@extends('layouts.app')

@section('page-title', 'Tanda Tangan Saya')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1"><i class="bi bi-pen me-2 text-primary"></i>Tanda Tangan Saya</h4>
    <p class="text-muted mb-0">Kelola tanda tangan digital tersimpan yang digunakan untuk menyetujui permintaan barang.</p>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


<div class="row justify-content-center">
    <div class="col-md-7">
        @if($signature)
            <div class="card mb-4">
                <div class="card-header fw-semibold">
                    <i class="bi bi-check-circle text-success me-2"></i>Tanda Tangan Tersimpan
                </div>
                <div class="card-body text-center">
                    <div class="border rounded p-4 mb-3 d-inline-block">
                        <img src="{{ $signature->signature_data }}" alt="TTD Tersimpan"
                             style="max-width: 280px; max-height: 120px;">
                    </div>
                    <p class="text-muted small mb-0">
                        Terakhir diperbarui: {{ $signature->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button class="btn btn-outline-primary btn-sm" id="toggle-update-btn">
                        <i class="bi bi-pencil me-1"></i>Perbarui Tanda Tangan
                    </button>
                    <form action="{{ route('gudang.signature.destroy') }}" method="POST"
                          onsubmit="return confirm('Yakin hapus tanda tangan tersimpan?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>

            <div class="card d-none" id="update-form-card">
                <div class="card-header fw-semibold">
                    <i class="bi bi-pencil me-2 text-primary"></i>Buat Tanda Tangan Baru
                </div>
                <div class="card-body">
                    <form action="{{ route('gudang.signature.save') }}" method="POST" id="signature-form">
                        @csrf
                        <input type="hidden" name="signature_data" id="signature-hidden">
                        <canvas id="signature-canvas"
                                style="border: 2px dashed #ced4da; border-radius: 8px; width: 100%; height: 200px; background: white; cursor: crosshair; touch-action: none;">
                        </canvas>
                        <div class="mt-2 mb-3">
                            <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eraser me-1"></i>Hapus
                            </button>
                        </div>
                        <div id="sig-error" class="text-danger small mb-2" style="display:none;">
                            Tanda tangan wajib diisi!
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" id="save-btn" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Tanda Tangan
                            </button>
                            <button type="button" id="cancel-update-btn" class="btn btn-outline-secondary">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            {{-- Belum ada TTD --}}
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="bi bi-pen me-2 text-primary"></i>Buat Tanda Tangan
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Anda belum memiliki tanda tangan tersimpan. Buat tanda tangan untuk mempercepat proses persetujuan permintaan barang.</p>
                    <form action="{{ route('gudang.signature.save') }}" method="POST" id="signature-form">
                        @csrf
                        <input type="hidden" name="signature_data" id="signature-hidden">
                        <canvas id="signature-canvas"
                                style="border: 2px dashed #ced4da; border-radius: 8px; width: 100%; height: 200px; background: white; cursor: crosshair; touch-action: none;">
                        </canvas>
                        <div class="mt-2 mb-3">
                            <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eraser me-1"></i>Hapus
                            </button>
                        </div>
                        <div id="sig-error" class="text-danger small mb-2" style="display:none;">
                            Tanda tangan wajib diisi!
                        </div>
                        <div class="d-grid">
                            <button type="button" id="save-btn" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>Simpan Tanda Tangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
let signaturePad = null;

function initCanvas() {
    const canvas = document.getElementById('signature-canvas');
    if (!canvas) return;
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
}

window.addEventListener('resize', initCanvas);
initCanvas();

document.getElementById('clear-signature')?.addEventListener('click', () => signaturePad?.clear());

document.getElementById('save-btn')?.addEventListener('click', function () {
    if (!signaturePad || signaturePad.isEmpty()) {
        document.getElementById('sig-error').style.display = 'block';
        return;
    }
    document.getElementById('sig-error').style.display = 'none';
    document.getElementById('signature-hidden').value = signaturePad.toDataURL('image/jpeg', 0.95);
    document.getElementById('signature-form').submit();
});

// Toggle update form
document.getElementById('toggle-update-btn')?.addEventListener('click', function () {
    const card = document.getElementById('update-form-card');
    card.classList.toggle('d-none');
    if (!card.classList.contains('d-none')) {
        setTimeout(initCanvas, 100);
    }
});

document.getElementById('cancel-update-btn')?.addEventListener('click', function () {
    document.getElementById('update-form-card').classList.add('d-none');
});
</script>
@endsection
