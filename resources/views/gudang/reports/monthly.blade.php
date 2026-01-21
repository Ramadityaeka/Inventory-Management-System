@extends('layouts.app')

@section('page-title', 'Monthly Report')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Monthly Report</h4>
        </div>
    </div>
</div>

<!-- Report Generator Form -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Generate Report</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('gudang.reports.monthly.generate') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ $selectedWarehouse == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                <select class="form-select" id="month" name="month" required>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                <select class="form-select" id="year" name="year" required>
                    @for($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
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
                <h5 class="mb-1">{{ $reportData['warehouse']->name }}</h5>
                <p class="text-muted mb-2">{{ $reportData['period'] }}</p>
                <small class="text-muted">
                    <i class="bi bi-geo-alt me-1"></i>{{ $reportData['warehouse']->location }}
                </small>
            </div>
            <div class="text-end">
                <small class="text-muted d-block">Generated on:</small>
                <strong>{{ now()->format('d M Y H:i') }}</strong>
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
                <p class="text-muted mb-0 small">Total Stock In</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="bi bi-arrow-down-circle text-danger fs-1 mb-2"></i>
                <h3 class="mb-1 text-danger">{{ number_format($reportData['total_stock_out']) }}</h3>
                <p class="text-muted mb-0 small">Total Stock Out</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-arrow-left-right text-primary fs-1 mb-2"></i>
                <h3 class="mb-1 text-primary">{{ number_format($reportData['total_movements']) }}</h3>
                <p class="text-muted mb-0 small">Total Movements</p>
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

<!-- Detailed Sections -->
<div class="row mb-4">
    <!-- Stock Movements by Item -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Stock Movements by Item - Detail</h6>
                <small class="text-muted">{{ $reportData['item_movements']->count() }} items with activity</small>
            </div>
            <div class="card-body">
                @if($reportData['item_movements']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th class="text-center">Stock In</th>
                                    <th class="text-center">Stock Out</th>
                                    <th class="text-center">Harga Terakhir</th>
                                    <th class="text-center">Total Nilai</th>
                                    <th class="text-center">Current Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['item_movements'] as $movement)
                                    <tr>
                                        <td>
                                            <strong>{{ $movement['item']->name }}</strong>
                                            <br><small class="text-muted">{{ $movement['item']->code }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($movement['stock_in'] > 0)
                                                <span class="badge bg-success">+{{ number_format($movement['stock_in']) }}</span>
                                                <br><small class="text-muted">{{ $movement['in_movements'] }} transaction(s)</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($movement['stock_out'] > 0)
                                                <span class="badge bg-danger">-{{ number_format($movement['stock_out']) }}</span>
                                                <br><small class="text-muted">{{ $movement['out_movements'] }} transaction(s)</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($movement['last_price'])
                                                <span class="badge bg-info bg-opacity-25 text-info">
                                                    Rp {{ number_format($movement['last_price'], 0, ',', '.') }}
                                                </span>
                                                <br><small class="text-muted">{{ $movement['purchase_count'] }} pembelian</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($movement['total_value'])
                                                <strong class="text-success">Rp {{ number_format($movement['total_value'], 0, ',', '.') }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong class="fs-6">{{ number_format($movement['current_stock']) }}</strong>
                                            <span class="text-muted"> {{ $movement['unit'] }}</span>
                                            <br>
                                            @if($movement['current_stock'] <= 0)
                                                <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Out of Stock</small>
                                            @elseif($movement['current_stock'] <= $movement['item']->min_threshold)
                                                <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Low Stock</small>
                                            @else
                                                <small class="text-success"><i class="bi bi-check-circle"></i> Available</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2 mb-0">No stock movements this month</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Submissions Summary -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Submissions Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="bi bi-clock-history text-warning fs-2"></i>
                        </div>
                        <h4 class="mb-1">{{ $reportData['submissions_pending'] }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="bi bi-check-circle text-success fs-2"></i>
                        </div>
                        <h4 class="mb-1">{{ $reportData['submissions_approved'] }}</h4>
                        <small class="text-muted">Approved</small>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="bi bi-x-circle text-danger fs-2"></i>
                        </div>
                        <h4 class="mb-1">{{ $reportData['submissions_rejected'] }}</h4>
                        <small class="text-muted">Rejected</small>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <h6 class="mb-3">Transfers</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-arrow-right-circle text-danger fs-4 me-2"></i>
                            <div>
                                <div class="fw-bold">{{ $reportData['transfers_out'] }}</div>
                                <small class="text-muted">Transfers Out</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-arrow-left-circle text-success fs-4 me-2"></i>
                            <div>
                                <div class="fw-bold">{{ $reportData['transfers_in'] }}</div>
                                <small class="text-muted">Transfers In</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current Stock Status -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Current Stock Status</h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-box-seam me-2"></i>
                    <strong>{{ $reportData['current_stocks']->count() }}</strong> Total Items
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>{{ $reportData['low_stock_items'] }}</strong> Low Stock Items
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-circle me-2"></i>
                    <strong>{{ $reportData['out_of_stock_items'] }}</strong> Out of Stock Items
                </div>
            </div>
        </div>
        
        @if($reportData['current_stocks']->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Min Threshold</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['current_stocks'] as $stock)
                            <tr>
                                <td><code>{{ $stock->item->code }}</code></td>
                                <td>{{ $stock->item->name }}</td>
                                <td class="text-end">
                                    <strong>{{ number_format($stock->quantity) }}</strong>
                                </td>
                                <td class="text-end">{{ number_format($stock->item->min_threshold) }}</td>
                                <td>
                                    @if($stock->quantity == 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($stock->quantity <= $stock->item->min_threshold)
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">Available</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Export Actions -->
<div class="card">
    <div class="card-body text-center">
        <form method="POST" action="{{ route('gudang.reports.monthly.exportPdf') }}" class="d-inline">
            @csrf
            <input type="hidden" name="warehouse_id" value="{{ $selectedWarehouse }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i>Export to PDF
            </button>
        </form>
        <button class="btn btn-outline-success" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Print Report
        </button>
    </div>
</div>
@else
<!-- Empty State -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-file-earmark-bar-graph text-muted" style="font-size: 5rem;"></i>
        <h5 class="text-muted mt-3">No Report Generated</h5>
        <p class="text-muted">Select a warehouse, month, and year above, then click "Generate" to create a monthly report.</p>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header, form { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush
