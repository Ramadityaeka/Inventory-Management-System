<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Submission;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOverviewExport;
use App\Exports\StockByCategoryExport;
use App\Exports\StockByWarehouseExport;
use App\Exports\StockBySupplierExport;
use App\Exports\DetailedStockExport;

class ReportController extends Controller
{
    public function stockOverview(Request $request)
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select([
                'stocks.*',
                'items.name as item_name',
                'items.code as item_code',
                'items.unit as item_unit',
                'warehouses.name as warehouse_name',
                'categories.name as category_name'
            ]);

        // Apply warehouse filter
        if ($request->filled('warehouse_ids')) {
            $query->whereIn('stocks.warehouse_id', $request->warehouse_ids);
        }

        // Apply category filter
        if ($request->filled('category_id')) {
            $query->where('items.category_id', $request->category_id);
        }

        // Apply stock status filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'out') {
                $query->where('stocks.quantity', '=', 0);
            }
            // 'all' doesn't need additional filtering
        }

        // Get all stocks for summary calculations (before pagination)
        $allStocks = $query->orderBy('warehouses.name')
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->get();

        // Calculate summary statistics
        $totalItems = $allStocks->count();
        $totalStock = $allStocks->sum('quantity');
        $outOfStockItems = $allStocks->filter(function($stock) {
            return $stock->quantity <= 0;
        })->count();

        // Get paginated stocks
        $stocks = $query->orderBy('warehouses.name')
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->paginate(50);

        // Add summary data to paginated collection
        $stocks->totalItems = $totalItems;
        $stocks->totalStock = $totalStock;
        $stocks->outOfStockItems = $outOfStockItems;

        // Get filter options
        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.reports.stock-overview', compact('stocks', 'warehouses', 'categories'));
    }

    public function transferSummary(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Count transfers by status
        $statusCounts = Transfer::whereBetween('requested_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Calculate main statistics
        $totalTransfers = array_sum($statusCounts);
        $completedCount = $statusCounts['completed'] ?? 0;
        $inTransitCount = $statusCounts['in_transit'] ?? 0;
        $rejectedCount = $statusCounts['rejected'] ?? 0;

        // Get chart data (last 12 months)
        $chartData = [];
        $chartLabels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $chartLabels[] = $date->format('M Y');
            
            $monthlyData = Transfer::whereBetween('requested_at', [$monthStart, $monthEnd])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
                
            $chartData[] = [
                'completed' => $monthlyData['completed'] ?? 0,
                'in_transit' => $monthlyData['in_transit'] ?? 0,
                'rejected' => $monthlyData['rejected'] ?? 0,
                'waiting_approval' => $monthlyData['waiting_approval'] ?? 0,
                'approved' => $monthlyData['approved'] ?? 0,
                'cancelled' => $monthlyData['cancelled'] ?? 0,
            ];
        }

        // Get transfers with relationships
        $transfers = Transfer::with(['item', 'fromWarehouse', 'toWarehouse', 'requester', 'approver'])
            ->whereBetween('requested_at', [$dateFrom, $dateTo])
            ->orderBy('requested_at', 'desc')
            ->paginate(50);

        return view('admin.reports.transfer-summary', compact(
            'transfers', 
            'statusCounts', 
            'totalTransfers',
            'completedCount',
            'inTransitCount', 
            'rejectedCount',
            'chartData',
            'chartLabels',
            'dateFrom', 
            'dateTo'
        ));
    }

    public function monthly(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        // Get all warehouses with their monthly data
        $warehouses = Warehouse::with(['stocks.item.category'])->get();

        $monthlyData = [];
        $grandTotalOpening = 0;
        $grandTotalIn = 0;
        $grandTotalOut = 0;
        $grandTotalClosing = 0;

        foreach ($warehouses as $warehouse) {
            $warehouseData = [
                'warehouse' => $warehouse,
                'items' => [],
                'total_opening' => 0,
                'total_in' => 0,
                'total_out' => 0,
                'total_closing' => 0,
                'total_purchase_value' => 0
            ];

            foreach ($warehouse->stocks as $stock) {
                // Calculate opening stock (stock at start of month)
                $openingStock = $this->getStockAtDate($stock->item_id, $warehouse->id, $startDate);

                // Calculate stock movements in the month
                $stockIn = StockMovement::where('item_id', $stock->item_id)
                    ->where('warehouse_id', $warehouse->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('movement_type', ['in', 'transfer_in'])
                    ->sum('quantity');

                $stockOut = StockMovement::where('item_id', $stock->item_id)
                    ->where('warehouse_id', $warehouse->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('movement_type', ['out', 'transfer_out'])
                    ->sum('quantity');

                $closingStock = $openingStock + $stockIn - (-$stockOut);  // stockOut is negative, so -$stockOut makes it positive

                // Get price information for this item
                $lastPurchase = Submission::where('item_id', $stock->item_id)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('status', 'approved')
                    ->whereNotNull('unit_price')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->first();

                $itemValue = $lastPurchase ? ($lastPurchase->unit_price * $stockIn) : 0;

                $warehouseData['items'][] = [
                    'item' => $stock->item,
                    'opening_stock' => $openingStock,
                    'stock_in' => $stockIn,
                    'stock_out' => $stockOut,
                    'closing_stock' => $closingStock,
                    'last_price' => $lastPurchase ? $lastPurchase->unit_price : null,
                    'item_value' => $itemValue
                ];

                // Add to warehouse totals
                $warehouseData['total_opening'] += $openingStock;
                $warehouseData['total_in'] += $stockIn;
                $warehouseData['total_out'] += $stockOut;
                $warehouseData['total_closing'] += $closingStock;
                $warehouseData['total_purchase_value'] += $itemValue;
            }

            // Add to grand totals
            $grandTotalOpening += $warehouseData['total_opening'];
            $grandTotalIn += $warehouseData['total_in'];
            $grandTotalOut += $warehouseData['total_out'];
            $grandTotalClosing += $warehouseData['total_closing'];

            $monthlyData[] = $warehouseData;
        }

        // Calculate total purchase value across all warehouses
        $grandTotalPurchaseValue = array_sum(array_column($monthlyData, 'total_purchase_value'));

        $grandTotals = [
            'opening' => $grandTotalOpening,
            'in' => $grandTotalIn,
            'out' => $grandTotalOut,
            'closing' => $grandTotalClosing,
            'purchase_value' => $grandTotalPurchaseValue
        ];

        return view('admin.reports.monthly', compact('monthlyData', 'grandTotals', 'month', 'year'));
    }

    public function exportExcel(Request $request)
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select([
                'stocks.*',
                'items.name as item_name',
                'items.code as item_code',
                'items.unit as item_unit',
                'warehouses.name as warehouse_name',
                'categories.name as category_name'
            ]);

        // Apply same filters as stockOverview
        if ($request->filled('warehouse_ids')) {
            $query->whereIn('stocks.warehouse_id', $request->warehouse_ids);
        }

        if ($request->filled('category_id')) {
            $query->where('items.category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'out') {
                $query->where('stocks.quantity', '=', 0);
            }
        }

        $stocks = $query->orderBy('warehouses.name')
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->get();

        // Calculate summary statistics
        $totalItems = $stocks->count();
        $totalStock = $stocks->sum('quantity');
        $outOfStockItems = $stocks->filter(function($stock) {
            return $stock->quantity <= 0;
        })->count();

        // Get warehouse filter for export
        $warehouse = null;
        if ($request->filled('warehouse_ids') && count($request->warehouse_ids) === 1) {
            $warehouse = Warehouse::find($request->warehouse_ids[0]);
        }

        // TODO: Enable after installing maatwebsite/excel
        return Excel::download(
            new StockOverviewExport($stocks),
            'stock_overview_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select([
                'stocks.*',
                'items.name as item_name',
                'items.code as item_code',
                'items.unit as item_unit',
                'warehouses.name as warehouse_name',
                'categories.name as category_name'
            ]);

        // Apply same filters as stockOverview
        if ($request->filled('warehouse_ids')) {
            $query->whereIn('stocks.warehouse_id', $request->warehouse_ids);
        }

        if ($request->filled('category_id')) {
            $query->where('items.category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'out') {
                $query->where('stocks.quantity', '=', 0);
            }
        }

        $stocks = $query->orderBy('warehouses.name')
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->get();

        // Calculate summary statistics
        $totalItems = $stocks->count();
        $totalStock = $stocks->sum('quantity');
        $outOfStockItems = $stocks->filter(function($stock) {
            return $stock->quantity <= 0;
        })->count();

        // Get warehouse filter for export
        $warehouse = null;
        if ($request->filled('warehouse_ids') && count($request->warehouse_ids) === 1) {
            $warehouse = Warehouse::find($request->warehouse_ids[0]);
        }

        $data = [
            'stocks' => $stocks,
            'warehouse' => $warehouse,
            'totalItems' => $totalItems,
            'totalStock' => $totalStock,
            'outOfStockItems' => $outOfStockItems,
        ];

        // TODO: Enable after installing barryvdh/dompdf
        return redirect()->back()->with('error', 'PDF export temporarily disabled. Please install required packages.');
        
        /*
        $pdf = Pdf::loadView('admin.reports.stock-overview-pdf', $data);
        return $pdf->download('stock_overview_' . now()->format('Y-m-d_H-i-s') . '.pdf');
        */
    }

    private function getStockAtDate($itemId, $warehouseId, $date)
    {
        // Calculate stock at a specific date by summing all movements up to that date
        $movements = StockMovement::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('created_at', '<', $date)
            ->orderBy('created_at')
            ->get();

        $stock = 0;
        foreach ($movements as $movement) {
            $stock += $movement->quantity;  // All quantities are signed: + for in/add, - for out/reduce
        }

        return max(0, $stock); // Ensure non-negative
    }

    public function exportStockOverviewExcel(Request $request)
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->select('stocks.*');

        // Apply filters
        if ($request->filled('warehouse_ids')) {
            $query->whereIn('stocks.warehouse_id', $request->warehouse_ids);
        }
        if ($request->filled('category_id')) {
            $query->where('items.category_id', $request->category_id);
        }
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'out') {
                $query->where('stocks.quantity', '=', 0);
            }
        }

        $stocks = $query->get();
        
        return Excel::download(new StockOverviewExport($stocks), 'stock-overview-' . now()->format('Y-m-d-His') . '.xlsx');
    }

    public function exportStockOverviewPdf(Request $request)
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->select('stocks.*');

        if ($request->filled('warehouse_ids')) {
            $query->whereIn('stocks.warehouse_id', $request->warehouse_ids);
        }
        if ($request->filled('category_id')) {
            $query->where('items.category_id', $request->category_id);
        }
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'out') {
                $query->where('stocks.quantity', '=', 0);
            }
        }

        $stocks = $query->orderBy('stocks.warehouse_id')->get();
        
        // Calculate statistics
        $totalItems = $stocks->unique('item_id')->count();
        $totalStock = $stocks->sum('quantity');
        $outOfStockItems = $stocks->where('quantity', 0)->count();
        
        $pdf = Pdf::loadView('admin.reports.stock-overview-pdf', compact(
            'stocks', 
            'warehouse', 
            'totalItems', 
            'totalStock', 
            'lowStockItems', 
            'outOfStockItems'
        ))->setPaper('a4', 'landscape');
        
        return $pdf->download('Stock_Overview_' . now()->format('Y-m-d_His') . '.pdf');
    }

    // Export by Category
    public function exportByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $warehouseId = $request->get('warehouse_id');
        
        $filename = 'Stock_By_Category_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new StockByCategoryExport($categoryId, $warehouseId), $filename);
    }

    // Export by Warehouse
    public function exportByWarehouse(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        
        $filename = 'Stock_By_Warehouse_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new StockByWarehouseExport($warehouseId), $filename);
    }

    // Export by Supplier
    public function exportBySupplier(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        
        $filename = 'Stock_By_Supplier_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new StockBySupplierExport($supplierId), $filename);
    }

    // Export Detailed (with all filters)
    public function exportDetailed(Request $request)
    {
        $filters = [
            'warehouse_id' => $request->get('warehouse_id'),
            'category_id' => $request->get('category_id'),
            'supplier_id' => $request->get('supplier_id'),
            'status' => $request->get('status'),
        ];
        
        $filename = 'Detailed_Stock_Report_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new DetailedStockExport($filters), $filename);
    }
}
