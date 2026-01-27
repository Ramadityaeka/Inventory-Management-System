@extends('layouts.app')

@section('page-title', 'Laporan Bulanan Semua Unit')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Laporan Bulanan Semua Unit</h4>
        </div>
    </div>
</div>

<!-- Report Generator Form -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Buat Laporan</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.reports.monthly.generate') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Unit <span class="text-danger">*</span></label>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="select_all_warehouses">
                        <label class="form-check-label fw-bold" for="select_all_warehouses">
                            Pilih Semua
                        </label>
                    </div>
                    <hr class="my-2">
                    @foreach($warehouses as $warehouse)
                        <div class="form-check mb-2">
                            <input class="form-check-input warehouse-checkbox" 
                                   type="checkbox" 
                                   name="warehouse_ids[]" 
                                   value="{{ $warehouse->id }}"
                                   id="warehouse_{{ $warehouse->id }}"
                                   {{ in_array($warehouse->id, $selectedWarehouses) ? 'checked' : '' }}>
                            <label class="form-check-label" for="warehouse_{{ $warehouse->id }}">
                                {{ $warehouse->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-2">
                <label for="month" class="form-label">Bulan <span class="text-danger">*</span></label>
                <select class="form-select" id="month" name="month" required>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
                <select class="form-select" id="year" name="year" required>
                    @for($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-bar-chart me-1"></i>Generate
                </button>
            </div>
        </form>
    </div>
</div>

@if($reportData)
<!-- Report Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1">
                    @if($reportData['warehouse_count'] > 1)
                        {{ $reportData['warehouse_count'] }} Unit Terpilih
                    @else
                        {{ $reportData['warehouses'] }}
                    @endif
                </h5>
                <p class="text-muted mb-2">{{ $reportData['period'] }}</p>
                @if($reportData['warehouse_count'] > 1)
                    <small class="text-muted">{{ $reportData['warehouses'] }}</small>
                @endif
            </div>
            <div class="text-end">
                <small class="text-muted d-block">Dibuat pada:</small>
                <strong>{{ formatDateIndo(now(), 'd M Y H:i') }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-arrow-up-circle text-success fs-1 mb-2"></i>
                <h3 class="mb-1 text-success">{{ number_format($reportData['total_stock_in']) }}</h3>
                <p class="text-muted mb-0 small">Total Barang Masuk</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="bi bi-arrow-down-circle text-danger fs-1 mb-2"></i>
                <h3 class="mb-1 text-danger">{{ number_format($reportData['total_stock_out']) }}</h3>
                <p class="text-muted mb-0 small">Total Barang Keluar</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-arrow-left-right text-primary fs-1 mb-2"></i>
                <h3 class="mb-1 text-primary">{{ number_format($reportData['total_movements']) }}</h3>
                <p class="text-muted mb-0 small">Total Pergerakan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar text-info fs-1 mb-2"></i>
                <h3 class="mb-1 text-info">Rp {{ number_format($reportData['total_purchase_value'] ?? 0, 0, ',', '.') }}</h3>
                <p class="text-muted mb-0 small">Nilai Pembelian</p>
            </div>
        </div>
    </div>
</div>

<!-- TABEL 1: Transaksi Barang Masuk/Keluar -->
<div class="row mb-4">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Tabel 1: Transaksi Barang Masuk & Keluar</h6>
            </div>
            <div class="card-body">
                @if($reportData['transactions']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="10%">Unit</th>
                                    <th width="14%">Nama Barang</th>
                                    <th width="8%" class="text-center">Jumlah</th>
                                    <th width="8%" class="text-center">Sisa Stok</th>
                                    <th width="15%">Keterangan</th>
                                    <th width="8%" class="text-center">Status</th>
                                    <th width="12%">Diproses Oleh</th>
                                    <th width="11%" class="text-center">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['transactions'] as $index => $transaction)
                                    @php
                                        $currentStock = \App\Models\Stock::where('warehouse_id', $transaction->warehouse_id)
                                            ->where('item_id', $transaction->item_id)
                                            ->first();
                                        $remainingStock = $currentStock ? $currentStock->quantity : 0;
                                        $approval = $transaction->approvals->first();
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <small class="badge bg-info">{{ $transaction->warehouse->name }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $transaction->item->name }}</strong>
                                            <br><small class="text-muted">{{ $transaction->item->code }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">+{{ number_format($transaction->quantity) }}</span>
                                            <br><small class="text-muted">{{ $transaction->item->unit }}</small>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-primary">{{ number_format($remainingStock) }}</strong>
                                            <br><small class="text-muted">{{ $transaction->item->unit }}</small>
                                        </td>
                                        <td>
                                            @if($transaction->notes)
                                                <small>{{ Str::limit($transaction->notes, 40) }}</small>
                                            @else
                                                <small class="text-muted">Penerimaan dari {{ $transaction->supplier->name ?? '-' }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($transaction->status == 'approved')
                                                <span class="badge bg-success">Disetujui</span>
                                            @elseif($transaction->status == 'rejected')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @else
                                                <span class="badge bg-warning">Menunggu</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($approval)
                                                <strong>{{ $approval->admin->name }}</strong>
                                                <br><small class="text-muted">Admin</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <small>{{ $transaction->created_at->format('d M Y') }}</small>
                                            <br><small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>{{ $reportData['submissions_approved'] }}</strong> Transaksi Disetujui
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-clock me-2"></i>
                                    <strong>{{ $reportData['submissions_pending'] }}</strong> Transaksi Menunggu
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-x-circle me-2"></i>
                                    <strong>{{ $reportData['submissions_rejected'] }}</strong> Transaksi Ditolak
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2 mb-0">Tidak ada transaksi bulan ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- TABEL 2: Stok Barang dengan Harga -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Tabel 2: Daftar Stok Barang & Nilai</h6>
            </div>
            <div class="card-body">
                @if($reportData['stocks_with_values']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="12%">Unit</th>
                                    <th width="8%">Kode Barang</th>
                                    <th width="18%">Nama Barang</th>
                                    <th width="10%">Kategori</th>
                                    <th width="7%" class="text-center">Jumlah</th>
                                    <th width="7%" class="text-center">Satuan</th>
                                    <th width="13%" class="text-end">Harga/Satuan</th>
                                    <th width="15%" class="text-end">Harga Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['stocks_with_values'] as $index => $stockData)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <small class="badge bg-info">{{ $stockData['warehouse']->name }}</small>
                                        </td>
                                        <td><code>{{ $stockData['item']->code }}</code></td>
                                        <td>
                                            <strong>{{ $stockData['item']->name }}</strong>
                                            @if($stockData['quantity'] <= 0)
                                                <br><small class="badge bg-danger">Habis</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-25">
                                                {{ $stockData['item']->category->name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ number_format($stockData['quantity']) }}</strong>
                                        </td>
                                        <td class="text-center">{{ $stockData['item']->unit }}</td>
                                        <td class="text-end">
                                            @if($stockData['unit_price'] > 0)
                                                Rp {{ number_format($stockData['unit_price'], 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($stockData['total_value'] > 0)
                                                <strong class="text-success">Rp {{ number_format($stockData['total_value'], 0, ',', '.') }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>TOTAL KESELURUHAN:</strong></td>
                                    <td class="text-center">
                                        <strong class="text-primary">{{ number_format($reportData['stocks_with_values']->sum('quantity')) }}</strong>
                                    </td>
                                    <td class="text-center"><small class="text-muted">item</small></td>
                                    <td></td>
                                    <td class="text-end">
                                        <strong class="text-success fs-5">Rp {{ number_format($reportData['total_stock_value'], 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Summary Box -->
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="bi bi-box-seam text-primary fs-3"></i>
                                        <h4 class="mt-2 mb-0 text-primary">{{ number_format($reportData['stocks_with_values']->count()) }}</h4>
                                        <small class="text-muted">Total Jenis Barang</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="bi bi-stack text-info fs-3"></i>
                                        <h4 class="mt-2 mb-0 text-info">{{ number_format($reportData['stocks_with_values']->sum('quantity')) }}</h4>
                                        <small class="text-muted">Total Jumlah Barang</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="bi bi-currency-dollar text-success fs-3"></i>
                                        <h3 class="mt-2 mb-0 text-success">Rp {{ number_format($reportData['total_stock_value'], 0, ',', '.') }}</h3>
                                        <small class="text-muted">Total Nilai Stok Keseluruhan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2 mb-0">Tidak ada stok barang</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Export Actions -->
<div class="card">
    <div class="card-body text-center">
        <form method="POST" action="{{ route('admin.reports.monthly.exportPdf') }}" class="d-inline">
            @csrf
            @foreach($selectedWarehouses as $whId)
                <input type="hidden" name="warehouse_ids[]" value="{{ $whId }}">
            @endforeach
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i>Export ke PDF
            </button>
        </form>
        <button class="btn btn-outline-success" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Cetak Laporan
        </button>
    </div>
</div>
@else
<!-- Empty State -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-file-earmark-bar-graph text-muted" style="font-size: 5rem;"></i>
        <h5 class="text-muted mt-3">Belum Ada Laporan</h5>
        <p class="text-muted">Pilih unit, bulan, dan tahun di atas, lalu klik "Generate" untuk membuat laporan bulanan.</p>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Select all warehouses checkbox
    document.getElementById('select_all_warehouses')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.warehouse-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Update "select all" when individual checkboxes change
    document.querySelectorAll('.warehouse-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.warehouse-checkbox');
            const checkedCount = document.querySelectorAll('.warehouse-checkbox:checked').length;
            document.getElementById('select_all_warehouses').checked = checkedCount === allCheckboxes.length;
        });
    });
</script>
@endpush

@push('styles')
<style>
    @media print {
        .btn, .card-header, form { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush
