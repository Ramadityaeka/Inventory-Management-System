@extends('layouts.app')

@section('page-title', 'Summary Transfer Barang')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Summary Transfer Barang</h4>
            <div>
                <button type="button" class="btn btn-success me-2" onclick="exportExcel()">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="exportPdf()">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Filter Periode</h6>
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                   value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                   value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.reports.transfer-summary') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Transfers</h6>
                        <h3 class="mb-0">{{ number_format($totalTransfers) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Completed</h6>
                        <h3 class="mb-0">{{ number_format($completedCount) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">In Transit</h6>
                        <h3 class="mb-0">{{ number_format($inTransitCount) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Rejected</h6>
                        <h3 class="mb-0">{{ number_format($rejectedCount) }}</h3>
                    </div>
                    <div class="fs-2 opacity-75">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Transfer by Month (Last 12 Months)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 400px;">
                    <canvas id="transferChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Details Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-table me-2"></i>Transfer Details ({{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }})</h6>
            </div>
            <div class="card-body">
                @if($transfers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Transfer Number</th>
                                    <th>Item</th>
                                    <th>From → To</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfers as $transfer)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.transfers.show', $transfer) }}" class="text-decoration-none fw-bold text-primary">
                                                {{ $transfer->transfer_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $transfer->item->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $transfer->item->code }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-secondary me-2">{{ $transfer->fromWarehouse->name }}</span>
                                                <i class="bi bi-arrow-right text-muted mx-2"></i>
                                                <span class="badge bg-primary">{{ $transfer->toWarehouse->name }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($transfer->quantity) }} {{ $transfer->item->unit }}</td>
                                        <td>
                                            @switch($transfer->status)
                                                @case('waiting_approval')
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock me-1"></i>Waiting Approval
                                                    </span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check me-1"></i>Approved
                                                    </span>
                                                    @break
                                                @case('in_transit')
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-truck me-1"></i>In Transit
                                                    </span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Completed
                                                    </span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                                    </span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-ban me-1"></i>Cancelled
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ ucfirst($transfer->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $transfer->requested_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $transfers->firstItem() }} - {{ $transfers->lastItem() }} dari {{ $transfers->total() }} data
                        </div>
                        <div>
                            {{ $transfers->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">Tidak Ada Data</h5>
                        <p class="text-muted mb-0">Tidak ditemukan data transfer pada periode yang dipilih.</p>
                        <a href="{{ route('admin.reports.transfer-summary') }}" class="btn btn-outline-primary mt-3">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Filter
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart Data
const chartData = @json($chartData);
const chartLabels = @json($chartLabels);

// Prepare datasets
const datasets = [
    {
        label: 'Completed',
        data: chartData.map(item => item.completed),
        backgroundColor: '#198754',
        borderColor: '#198754',
        borderWidth: 1
    },
    {
        label: 'In Transit',
        data: chartData.map(item => item.in_transit),
        backgroundColor: '#0dcaf0',
        borderColor: '#0dcaf0',
        borderWidth: 1
    },
    {
        label: 'Rejected',
        data: chartData.map(item => item.rejected),
        backgroundColor: '#dc3545',
        borderColor: '#dc3545',
        borderWidth: 1
    },
    {
        label: 'Waiting Approval',
        data: chartData.map(item => item.waiting_approval),
        backgroundColor: '#ffc107',
        borderColor: '#ffc107',
        borderWidth: 1
    }
];

// Create Chart
const ctx = document.getElementById('transferChart').getContext('2d');
const transferChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartLabels,
        datasets: datasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            title: {
                display: true,
                text: 'Monthly Transfer Statistics'
            },
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    title: function(context) {
                        return 'Month: ' + context[0].label;
                    },
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + ' transfers';
                    }
                }
            }
        },
        scales: {
            x: {
                stacked: false,
                title: {
                    display: true,
                    text: 'Month'
                }
            },
            y: {
                stacked: false,
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Transfers'
                },
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Export functions
function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Create a temporary form for POST request
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("admin.reports.export-excel") }}';
    exportForm.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfInput);
    
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
</script>

<style>
/* Card hover effects */
.card {
    transition: all 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Table hover effects */
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}

/* Badge animations */
.badge {
    transition: all 0.2s ease-in-out;
}

.badge:hover {
    transform: scale(1.05);
}

/* Chart container */
.chart-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}

/* Custom card colors */
.bg-primary { background-color: #0d6efd !important; }
.bg-success { background-color: #198754 !important; }
.bg-info { background-color: #0dcaf0 !important; }
.bg-danger { background-color: #dc3545 !important; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body h3 {
        font-size: 1.5rem;
    }
    
    .fs-2 {
        font-size: 1.5rem !important;
    }
    
    .chart-container {
        height: 300px !important;
    }
}
</style>
@endsection