@extends('layouts.app')

@section('page-title', 'Tanda Tangan PIC - ' . $publicRequest->request_code)

@section('content')
<div class="mb-3">
    <a href="{{ route('gudang.public-requests.show', $publicRequest->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Detail
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-file-check me-2 text-primary"></i>Ringkasan Permohonan yang Disetujui
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6"><span class="text-muted small">Pemohon</span><div class="fw-semibold">{{ $publicRequest->requester_name }}</div></div>
                    <div class="col-6"><span class="text-muted small">Unit</span><div class="fw-semibold">{{ $publicRequest->warehouse->name }}</div></div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr><th>Barang</th><th class="text-center">Disetujui</th></tr>
                        </thead>
                        <tbody>
                            @foreach($publicRequest->items as $item)
                                @if($item->quantity_approved > 0)
                                    <tr>
                                        <td>{{ $item->item->name }}</td>
                                        <td class="text-center">{{ $item->quantity_approved }} {{ $item->item->unit }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($publicRequest->requesterSignature)
                    <div class="border-top pt-3 mt-3">
                        <p class="text-muted small mb-1">Tanda Tangan Pemohon:</p>
                        <div class="border rounded p-2 d-inline-block">
                            <img src="{{ $publicRequest->requesterSignature->signature_data }}"
                                 alt="TTD Pemohon" style="max-height: 80px; max-width: 180px;">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-pen me-2 text-warning"></i>Tanda Tangan Anda sebagai PIC
            </div>
            <div class="card-body">
                <form action="{{ route('gudang.public-requests.save-sign', $publicRequest->id) }}" method="POST" id="sign-form">
                    @csrf
                    <input type="hidden" name="use_saved" id="use-saved-input" value="0">
                    <input type="hidden" name="signature_data" id="signature-data-input">

                    @if($savedSignature)
                        <div id="saved-section" class="mb-4">
                            <p class="fw-medium mb-2">Tanda Tangan Tersimpan Anda:</p>
                            <div class="border rounded p-3 text-center mb-3">
                                <img src="{{ $savedSignature->signature_data }}"
                                     alt="TTD Tersimpan" style="max-height: 100px; max-width: 220px;">
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" id="use-saved-btn" class="btn btn-success">
                                    <i class="bi bi-check2 me-1"></i>Gunakan TTD Ini
                                </button>
                                <button type="button" id="new-signature-btn" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil me-1"></i>Buat TTD Baru
                                </button>
                            </div>
                        </div>

                        <div id="confirm-saved-section" class="d-none mb-4">
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Tanda tangan tersimpan Anda akan digunakan untuk dokumen ini.
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check2-circle me-2"></i>Setujui & Selesaikan Dokumen
                                </button>
                            </div>
                        </div>
                    @endif

                    <div id="new-signature-section" class="{{ $savedSignature ? 'd-none' : '' }}">
                        <p class="fw-medium mb-2">Gambar Tanda Tangan Baru:</p>
                        <canvas id="pic-signature-canvas"
                                style="border: 2px dashed #ced4da; border-radius: 8px; width: 100%; height: 180px; background: white; cursor: crosshair; touch-action: none;">
                        </canvas>
                        <div class="mt-2 mb-3">
                            <button type="button" id="clear-pic-signature" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eraser me-1"></i>Hapus
                            </button>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="save_as_default" value="1" id="save-default-check">
                            <label class="form-check-label" for="save-default-check">
                                Simpan sebagai tanda tangan default saya
                            </label>
                        </div>

                        <div id="sig-error-pic" class="text-danger small mb-2" style="display:none;">
                            Tanda tangan wajib diisi!
                        </div>

                        <div class="d-grid">
                            <button type="button" id="save-sign-btn" class="btn btn-primary btn-lg">
                                <i class="bi bi-check2-circle me-2"></i>Simpan & Selesaikan Dokumen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
const canvas = document.getElementById('pic-signature-canvas');
let signaturePad = null;

function initCanvas() {
    if (!canvas) return;
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
}

window.addEventListener("resize", initCanvas);
initCanvas();

document.getElementById('clear-pic-signature')?.addEventListener('click', () => signaturePad?.clear());

document.getElementById('use-saved-btn')?.addEventListener('click', function () {
    document.getElementById('use-saved-input').value = '1';
    document.getElementById('confirm-saved-section').classList.remove('d-none');
    document.getElementById('new-signature-section').classList.add('d-none');
    document.getElementById('saved-section').classList.add('d-none');
});

document.getElementById('new-signature-btn')?.addEventListener('click', function () {
    document.getElementById('use-saved-input').value = '0';
    document.getElementById('new-signature-section').classList.remove('d-none');
    document.getElementById('confirm-saved-section').classList.add('d-none');
    document.getElementById('saved-section').classList.add('d-none');
    setTimeout(initCanvas, 100);
});

document.getElementById('save-sign-btn')?.addEventListener('click', function (e) {
    e.preventDefault();
    if (!signaturePad || signaturePad.isEmpty()) {
        document.getElementById('sig-error-pic').style.display = 'block';
        return;
    }
    document.getElementById('sig-error-pic').style.display = 'none';
    document.getElementById('signature-data-input').value = signaturePad.toDataURL('image/jpeg', 0.95);
    document.getElementById('sign-form').submit();
});
</script>
@endsection
