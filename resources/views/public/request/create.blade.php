<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Permintaan Barang - Inventory ESDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f4f8; font-family: 'Segoe UI', sans-serif; }
        .page-header { background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%); color: white; padding: 20px 0; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .signature-canvas { border: 2px dashed #ced4da; border-radius: 8px; width: 100%; height: 180px; background: white; cursor: crosshair; touch-action: none; }
        .item-row { background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 10px; }
        .loading-spinner { display: none; }
    </style>
</head>
<body>
<div class="page-header mb-4">
    <div class="container">
        <div class="d-flex align-items-center">
            <a href="{{ route('login') }}" class="text-white text-decoration-none me-3">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-bold"><i class="bi bi-clipboard-plus me-2"></i>Ajukan Permintaan Barang</h4>
                <small class="opacity-75">Sistem Inventory ESDM</small>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Terjadi Kesalahan:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="request-form" action="{{ route('public.request.store') }}" method="POST">
        @csrf

        {{-- Info Pemohon --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-person me-2 text-primary"></i>Informasi Pemohon
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="requester_name" class="form-control" placeholder="Masukkan nama lengkap Anda"
                           value="{{ old('requester_name') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Unit / Gudang Tujuan <span class="text-danger">*</span></label>
                    <select name="warehouse_id" id="warehouse-select" class="form-select" required>
                        <option value="">-- Pilih Unit/Gudang --</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">PIC (Penanggung Jawab) <span class="text-danger">*</span></label>
                    <div class="loading-spinner" id="pic-loading">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        <span class="text-muted">Memuat daftar PIC...</span>
                    </div>
                    <select name="pic_user_id" id="pic-select" class="form-select" required>
                        <option value="">-- Pilih unit terlebih dahulu --</option>
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label fw-medium">Catatan / Keterangan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Opsional — tulis keterangan tambahan jika ada">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Daftar Barang --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box-seam me-2 text-primary"></i>Barang yang Diminta</span>
                <button type="button" id="add-item-btn" class="btn btn-sm btn-outline-primary" disabled>
                    <i class="bi bi-plus-circle me-1"></i>Tambah Barang
                </button>
            </div>
            <div class="card-body">
                <div id="items-loading" class="text-center py-3 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>Memuat daftar barang...
                </div>
                <div id="no-warehouse-msg" class="text-center py-3 text-muted">
                    <i class="bi bi-arrow-up-circle me-2"></i>Pilih unit/gudang terlebih dahulu untuk melihat barang tersedia.
                </div>
                <div id="items-container">
                    {{-- Baris barang akan di-generate oleh JavaScript --}}
                </div>
                <div id="no-stock-msg" class="text-center py-3 text-danger" style="display:none;">
                    <i class="bi bi-exclamation-circle me-2"></i>Tidak ada barang tersedia di unit ini.
                </div>
            </div>
        </div>

        {{-- Tanda Tangan --}}
        <div class="card mb-4">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-pen me-2 text-primary"></i>Tanda Tangan Pemohon <span class="text-danger">*</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Gambar tanda tangan Anda di kotak di bawah ini.</p>
                <canvas id="signature-canvas" class="signature-canvas"></canvas>
                <input type="hidden" name="signature_data" id="signature-hidden">
                <div class="mt-2">
                    <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eraser me-1"></i>Hapus Tanda Tangan
                    </button>
                </div>
                <div id="sig-error" class="text-danger small mt-1" style="display:none;">
                    Tanda tangan wajib diisi!
                </div>
            </div>
        </div>

        <div class="d-grid">
            <button type="button" id="submit-btn" class="btn btn-primary btn-lg">
                <i class="bi bi-send me-2"></i>Submit Permohonan
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
// Data stok dari AJAX
let availableStocks = [];

// Inisialisasi signature pad
const canvas = document.getElementById('signature-canvas');
const signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgb(255, 255, 255)',
    penColor: 'rgb(0, 0, 0)'
});

// Resize canvas
function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    signaturePad.clear();
}
window.addEventListener("resize", resizeCanvas);
resizeCanvas();

// Hapus TTD
document.getElementById('clear-signature').addEventListener('click', () => {
    signaturePad.clear();
});

// Saat unit dipilih
document.getElementById('warehouse-select').addEventListener('change', function () {
    const warehouseId = this.value;
    if (!warehouseId) {
        document.getElementById('no-warehouse-msg').style.display = 'block';
        document.getElementById('items-container').innerHTML = '';
        document.getElementById('no-stock-msg').style.display = 'none';
        document.getElementById('add-item-btn').disabled = true;
        resetPicSelect();
        return;
    }

    document.getElementById('no-warehouse-msg').style.display = 'none';

    // Load stocks & pics secara bersamaan
    loadStocks(warehouseId);
    loadPics(warehouseId);
});

function loadStocks(warehouseId) {
    document.getElementById('items-loading').style.display = 'block';
    document.getElementById('items-container').innerHTML = '';
    document.getElementById('no-stock-msg').style.display = 'none';

    fetch(`/api/unit/${warehouseId}/stocks`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('items-loading').style.display = 'none';
            availableStocks = data;

            if (data.length === 0) {
                document.getElementById('no-stock-msg').style.display = 'block';
                document.getElementById('add-item-btn').disabled = true;
                return;
            }

            document.getElementById('add-item-btn').disabled = false;
            addItemRow();
        })
        .catch(() => {
            document.getElementById('items-loading').style.display = 'none';
            document.getElementById('no-stock-msg').style.display = 'block';
        });
}

function loadPics(warehouseId) {
    document.getElementById('pic-loading').style.display = 'block';
    const picSelect = document.getElementById('pic-select');
    picSelect.disabled = true;

    fetch(`/api/unit/${warehouseId}/pics`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('pic-loading').style.display = 'none';
            picSelect.disabled = false;
            picSelect.innerHTML = '<option value="">-- Pilih PIC --</option>';
            data.forEach(pic => {
                const opt = document.createElement('option');
                opt.value = pic.user_id;
                opt.textContent = pic.name;
                picSelect.appendChild(opt);
            });
            if (data.length === 0) {
                picSelect.innerHTML = '<option value="">Tidak ada PIC tersedia</option>';
            }
        })
        .catch(() => {
            document.getElementById('pic-loading').style.display = 'none';
            resetPicSelect();
        });
}

function resetPicSelect() {
    const picSelect = document.getElementById('pic-select');
    picSelect.innerHTML = '<option value="">-- Pilih unit terlebih dahulu --</option>';
    picSelect.disabled = false;
}

function buildStockOptions(excludeItemIds = []) {
    return availableStocks
        .filter(s => !excludeItemIds.includes(String(s.item_id)))
        .map(s => `<option value="${s.item_id}" data-qty="${s.quantity}" data-unit="${s.unit}">${s.name} (Stok: ${s.quantity} ${s.unit})</option>`)
        .join('');
}

let itemRowCount = 0;

function addItemRow() {
    const container = document.getElementById('items-container');
    const usedItemIds = Array.from(container.querySelectorAll('.item-select')).map(s => s.value).filter(Boolean);
    const options = buildStockOptions(usedItemIds);

    if (!options) {
        alert('Semua barang tersedia sudah ditambahkan.');
        return;
    }

    const idx = itemRowCount++;
    const div = document.createElement('div');
    div.className = 'item-row';
    div.dataset.idx = idx;
    div.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-md-7">
                <label class="form-label small fw-medium">Nama Barang</label>
                <select name="items[${idx}][item_id]" class="form-select item-select" required>
                    <option value="">-- Pilih Barang --</option>
                    ${options}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium">Jumlah</label>
                <div class="input-group">
                    <input type="number" name="items[${idx}][quantity]" class="form-control item-qty" min="1" placeholder="0" required>
                    <span class="input-group-text item-unit-label">-</span>
                </div>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" onclick="removeItemRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;

    // Saat barang dipilih, update label satuan dan max qty
    div.querySelector('.item-select').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const maxQty = opt.dataset.qty || '';
        const unit = opt.dataset.unit || '-';
        div.querySelector('.item-unit-label').textContent = unit;
        div.querySelector('.item-qty').max = maxQty;
    });

    container.appendChild(div);
}

function removeItemRow(btn) {
    btn.closest('.item-row').remove();
    if (document.getElementById('items-container').children.length === 0) {
        addItemRow();
    }
}

document.getElementById('add-item-btn').addEventListener('click', addItemRow);

// Submit
document.getElementById('submit-btn').addEventListener('click', function (e) {
    e.preventDefault();

    // Validasi tanda tangan
    if (signaturePad.isEmpty()) {
        document.getElementById('sig-error').style.display = 'block';
        document.getElementById('signature-canvas').scrollIntoView({ behavior: 'smooth' });
        return;
    }

    document.getElementById('sig-error').style.display = 'none';
    document.getElementById('signature-hidden').value = signaturePad.toDataURL('image/jpeg', 0.95);
    document.getElementById('request-form').submit();
});
</script>
</body>
</html>
