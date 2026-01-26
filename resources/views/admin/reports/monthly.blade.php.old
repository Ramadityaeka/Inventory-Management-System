@extends('layouts.app')

@section('page-title', 'Laporan Bulanan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Laporan Bulanan</h4>
            <button type="button" class="btn btn-danger" onclick="exportPdf()">
                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
            </button>
        </div>
    </div>
</div>

<!-- Month/Year Selector -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Pilih Periode</h6>
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="month" class="form-label">Month</label>
                            <select name="month" id="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label">Year</label>
                            <select name="year" id="year" class="form-select">
                                @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-play-circle me-1"></i>Generate Report
                            </button>
                            <a href="{{ route('admin.reports.monthly') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Current Month
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(count($monthlyData) > 0)
    <!-- Grand Total Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-calculator me-2"></i>Grand Total - {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded">
                                <h5 class="text-secondary mb-1">{{ number_format($grandTotals['opening']) }}</h5>
                                <small class="text-muted">Total Opening</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <h5 class="text-success mb-1">+{{ number_format($grandTotals['in']) }}</h5>
                                <small class="text-success">Total IN</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <h5 class="text-danger mb-1">-{{ number_format($grandTotals['out']) }}</h5>
                                <small class="text-danger">Total OUT</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <h5 class="text-primary mb-1 fw-bold">{{ number_format($grandTotals['closing']) }}</h5>
                                <small class="text-primary">Total Closing</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <h5 class="text-info mb-1 fw-bold">Rp {{ number_format($grandTotals['purchase_value'] ?? 0, 0, ',', '.') }}</h5>
                                <small class="text-info">Total Nilai Pembelian</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Accordion -->
    <div class="row">
        <div class="col-12">
            <div class="accordion" id="warehouseAccordion">
                @foreach($monthlyData as $index => $warehouseData)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $index }}">
                            <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" 
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                    aria-controls="collapse{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div>
                                        <i class="bi bi-building me-2"></i>
                                        <strong>{{ $warehouseData['warehouse']->name }}</strong>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary me-2">{{ count($warehouseData['items']) }} Items</span>
                                        <small class="text-muted">
                                            Opening: {{ number_format($warehouseData['total_opening']) }} | 
                                            Closing: {{ number_format($warehouseData['total_closing']) }}
                                            @if(isset($warehouseData['total_purchase_value']) && $warehouseData['total_purchase_value'] > 0)
                                                | Nilai: Rp {{ number_format($warehouseData['total_purchase_value'], 0, ',', '.') }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                             aria-labelledby="heading{{ $index }}" data-bs-parent="#warehouseAccordion">
                            <div class="accordion-body">
                                <!-- Summary Row -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="card bg-light border-0">
                                            <div class="card-body py-3">
                                                <div class="row text-center">
                                                    <div class="col-3">
                                                        <h6 class="text-secondary mb-1">{{ number_format($warehouseData['total_opening']) }}</h6>
                                                        <small class="text-muted">Opening</small>
                                                    </div>
                                                    <div class="col-3">
                                                        <h6 class="text-success mb-1">+{{ number_format($warehouseData['total_in']) }}</h6>
                                                        <small class="text-success">IN</small>
                                                    </div>
                                                    <div class="col-3">
                                                        <h6 class="text-danger mb-1">-{{ number_format($warehouseData['total_out']) }}</h6>
                                                        <small class="text-danger">OUT</small>
                                                    </div>
                                                    <div class="col-3">
                                                        <h6 class="text-primary mb-1 fw-bold">{{ number_format($warehouseData['total_closing']) }}</h6>
                                                        <small class="text-primary">Closing</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(count($warehouseData['items']) > 0)
                                    <!-- Items Table -->
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Item Name</th>
                                                    <th>Category</th>
                                                    <th class="text-center">Opening</th>
                                                    <th class="text-center">IN</th>
                                                    <th class="text-center">OUT</th>
                                                    <th class="text-center">Closing</th>
                                                    <th class="text-center">Harga Terakhir</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($warehouseData['items'] as $itemData)
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <strong>{{ $itemData['item']->name }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ $itemData['item']->code }}</small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">{{ $itemData['item']->category->name ?? 'N/A' }}</span>
                                                        </td>
                                                        <td class="text-center">{{ number_format($itemData['opening_stock']) }}</td>
                                                        <td class="text-center text-success">+{{ number_format($itemData['stock_in']) }}</td>
                                                        <td class="text-center text-danger">-{{ number_format($itemData['stock_out']) }}</td>
                                                        <td class="text-center fw-bold">{{ number_format($itemData['closing_stock']) }}</td>
                                                        <td class="text-center">
                                                            @if(isset($itemData['last_price']) && $itemData['last_price'])
                                                                <span class="badge bg-info bg-opacity-25 text-info">
                                                                    Rp {{ number_format($itemData['last_price'], 0, ',', '.') }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            
                                            <!-- Subtotal Row -->
                                            <tfoot class="table-primary">
                                                <tr class="fw-bold">
                                                    <td colspan="2" class="text-end">Subtotal:</td>
                                                    <td class="text-center">{{ number_format($warehouseData['total_opening']) }}</td>
                                                    <td class="text-center text-success">+{{ number_format($warehouseData['total_in']) }}</td>
                                                    <td class="text-center text-danger">-{{ number_format($warehouseData['total_out']) }}</td>
                                                    <td class="text-center">{{ number_format($warehouseData['total_closing']) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bi bi-info-circle text-muted fs-2 mb-3"></i>
                                        <p class="text-muted mb-0">No stock movement data for this warehouse in {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">Tidak Ada Data</h5>
                    <p class="text-muted mb-0">Tidak ditemukan data stok untuk periode yang dipilih.</p>
                    <a href="{{ route('admin.reports.monthly') }}" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset ke Bulan Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
function exportPdf() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Create a temporary form for POST request
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("admin.reports.export-pdf") }}';
    exportForm.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfInput);
    
    // Add report type
    const reportTypeInput = document.createElement('input');
    reportTypeInput.type = 'hidden';
    reportTypeInput.name = 'report_type';
    reportTypeInput.value = 'monthly';
    exportForm.appendChild(reportTypeInput);
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        exportForm.appendChild(input);
    }
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}

// Auto-expand first accordion on page load
document.addEventListener('DOMContentLoaded', function() {
    // Optional: Add smooth scroll behavior for accordion items
    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            setTimeout(() => {
                if (!this.classList.contains('collapsed')) {
                    this.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        });
    });
});
</script>

<style>
/* Custom accordion styling */
.accordion-button {
    background-color: #f8f9fa;
    border: none;
    box-shadow: none;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    border-color: #b6d7ff;
}

.accordion-button:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Card hover effects */
.card {
    transition: all 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Table improvements */
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}

/* Badge styling */
.badge {
    font-size: 0.75em;
}

/* Summary cards styling */
.bg-light {
    background-color: #f8f9fa !important;
}

.bg-success.bg-opacity-10 {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-danger.bg-opacity-10 {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.bg-primary.bg-opacity-10 {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .accordion-button .text-end {
        display: none;
    }
}
</style>
@endsection