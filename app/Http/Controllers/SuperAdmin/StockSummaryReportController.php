<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Category;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockSummaryReportExport;

class StockSummaryReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Base query untuk mengelompokkan per barang
            $query = Item::query()
                ->select([
                    'items.id',
                    'items.code',
                    'items.name',
                    'items.unit',
                    'items.category_id',
                    'categories.name as category_name'
                ])
                ->with('itemUnits')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id');

            // Apply filters
            if ($request->filled('category_id')) {
                $query->where('items.category_id', $request->category_id);
            }

            if ($request->filled('item_name')) {
                $query->where('items.name', 'LIKE', '%' . $request->item_name . '%');
            }

            if ($request->filled('item_code')) {
                $query->where('items.code', 'LIKE', '%' . $request->item_code . '%');
            }

            $items = $query->orderBy('items.code')->get();

            // Build summary data
            $summaryData = [];
            $warehouseId = $request->filled('warehouse_id') ? $request->warehouse_id : null;
            $year = $request->filled('year') ? $request->year : null;
            $month = $request->filled('month') ? $request->month : null;

            foreach ($items as $item) {
                // Get unit information - use first unit from itemUnits or fallback to item.unit property
                $firstUnit = $item->itemUnits->first();
                $unitName = $firstUnit ? $firstUnit->name : ($item->unit ?? '-');

                // Get stock per warehouse
                if ($warehouseId) {
                    // Filtered by specific warehouse
                    
                    // Query untuk barang masuk (approved submissions)
                    $stockIn = Submission::where('item_id', $item->id)
                        ->where('status', 'approved')
                        ->whereNotNull('submitted_at')
                        ->where('warehouse_id', $warehouseId)
                        ->when($year, fn($q) => $q->whereYear('submitted_at', $year))
                        ->when($month, fn($q) => $q->whereMonth('submitted_at', $month))
                        ->sum('quantity') ?? 0;

                    // Query untuk barang keluar (approved stock requests)
                    $stockOut = StockRequest::where('item_id', $item->id)
                        ->where('status', 'approved')
                        ->where('warehouse_id', $warehouseId)
                        ->when($year, fn($q) => $q->whereYear('approved_at', $year))
                        ->when($month, fn($q) => $q->whereMonth('approved_at', $month))
                        ->sum('base_quantity') ?? 0;
                    
                    $currentStock = Stock::where('item_id', $item->id)
                        ->where('warehouse_id', $warehouseId)
                        ->sum('quantity') ?? 0;
                    
                    $warehouse = Warehouse::find($warehouseId);
                    
                    if ($currentStock > 0) {
                        $summaryData[] = [
                            'item_id' => $item->id,
                            'warehouse_id' => $warehouseId,
                            'warehouse_name' => $warehouse ? $warehouse->name : '-',
                            'code' => $item->code,
                            'name' => $item->name,
                            'category' => $item->category_name ?? '-',
                            'unit' => $unitName,
                            'stock_in' => $stockIn,
                            'stock_out' => $stockOut,
                            'current_stock' => $currentStock,
                        ];
                    }
                } else {
                    // Show all warehouses with stock
                    $stocks = Stock::where('item_id', $item->id)
                        ->where('quantity', '>', 0)
                        ->with('warehouse')
                        ->get();
                    
                    foreach ($stocks as $stock) {
                        // Calculate stock in/out for this specific warehouse
                        $whStockIn = Submission::where('item_id', $item->id)
                            ->where('warehouse_id', $stock->warehouse_id)
                            ->where('status', 'approved')
                            ->whereNotNull('submitted_at')
                            ->when($year, fn($q) => $q->whereYear('submitted_at', $year))
                            ->when($month, fn($q) => $q->whereMonth('submitted_at', $month))
                            ->sum('quantity') ?? 0;
                            
                        $whStockOut = StockRequest::where('item_id', $item->id)
                            ->where('warehouse_id', $stock->warehouse_id)
                            ->where('status', 'approved')
                            ->when($year, fn($q) => $q->whereYear('approved_at', $year))
                            ->when($month, fn($q) => $q->whereMonth('approved_at', $month))
                            ->sum('base_quantity') ?? 0;
                        
                        $summaryData[] = [
                            'item_id' => $item->id,
                            'warehouse_id' => $stock->warehouse_id,
                            'warehouse_name' => $stock->warehouse->name ?? '-',
                            'code' => $item->code,
                            'name' => $item->name,
                            'category' => $item->category_name ?? '-',
                            'unit' => $unitName,
                            'stock_in' => $whStockIn,
                            'stock_out' => $whStockOut,
                            'current_stock' => $stock->quantity,
                        ];
                    }
                }
            }

            // Convert to collection for pagination
            $collection = collect($summaryData);
            
            // Paginate
            $perPage = 50;
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
            
            $summary = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $collection->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Calculate totals
            $totals = [
                'total_stock_in' => $collection->sum('stock_in'),
                'total_stock_out' => $collection->sum('stock_out'),
                'total_current_stock' => $collection->sum('current_stock'),
                'total_items' => $collection->count(),
            ];

            // Get filter options
            $categories = Category::orderBy('name')->get();
            $warehouses = Warehouse::orderBy('name')->get();
            
            // Get years from submissions
            $years = Submission::selectRaw('YEAR(submitted_at) as year')
                ->whereNotNull('submitted_at')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');

            return view('admin.reports.stock-summary', compact(
                'summary',
                'categories',
                'warehouses',
                'years',
                'totals'
            ));
        } catch (\Exception $e) {
            \Log::error('Stock Summary Report Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat laporan: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            \Log::info('Starting Stock Summary Excel Export', ['filters' => $request->all()]);
            $filters = $request->all();
            $fileName = 'Laporan_Ringkasan_Stok_' . date('Y-m-d_His') . '.xlsx';
            
            $export = new StockSummaryReportExport($filters);
            \Log::info('Export object created successfully');
            
            return Excel::download($export, $fileName);
        } catch (\Exception $e) {
            \Log::error('Stock Summary Excel Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat export Excel: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            \Log::info('Starting Stock Summary PDF Export', ['filters' => $request->all()]);
            
            // Get same data as index
            $query = Item::query()
                ->select([
                    'items.id',
                    'items.code',
                    'items.name',
                    'items.unit',
                    'items.category_id',
                    'categories.name as category_name'
                ])
                ->with('itemUnits')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id');

            // Apply filters
            if ($request->filled('category_id')) {
                $query->where('items.category_id', $request->category_id);
            }

            if ($request->filled('item_name')) {
                $query->where('items.name', 'LIKE', '%' . $request->item_name . '%');
            }

            if ($request->filled('item_code')) {
                $query->where('items.code', 'LIKE', '%' . $request->item_code . '%');
            }

            $items = $query->orderBy('items.code')->get();
            
            \Log::info('Items fetched for PDF', ['count' => $items->count()]);

            // Build summary data
            $summaryData = [];
            $warehouseId = $request->filled('warehouse_id') ? $request->warehouse_id : null;
            $year = $request->filled('year') ? $request->year : null;
            $month = $request->filled('month') ? $request->month : null;

            foreach ($items as $item) {
                // Get unit - use first unit from itemUnits or fallback to item.unit property
                $firstUnit = $item->itemUnits->first();
                $unitName = $firstUnit ? $firstUnit->name : ($item->unit ?? '-');

                // Get stock per warehouse
                if ($warehouseId) {
                    // Filtered by specific warehouse
                    
                    // Query untuk barang masuk (approved submissions)
                    $stockIn = Submission::where('item_id', $item->id)
                        ->where('status', 'approved')
                        ->whereNotNull('submitted_at')
                        ->where('warehouse_id', $warehouseId)
                        ->when($year, fn($q) => $q->whereYear('submitted_at', $year))
                        ->when($month, fn($q) => $q->whereMonth('submitted_at', $month))
                        ->sum('quantity') ?? 0;

                    // Query untuk barang keluar (approved stock requests)
                    $stockOut = StockRequest::where('item_id', $item->id)
                        ->where('status', 'approved')
                        ->where('warehouse_id', $warehouseId)
                        ->when($year, fn($q) => $q->whereYear('approved_at', $year))
                        ->when($month, fn($q) => $q->whereMonth('approved_at', $month))
                        ->sum('base_quantity') ?? 0;
                    
                    $currentStock = Stock::where('item_id', $item->id)
                        ->where('warehouse_id', $warehouseId)
                        ->sum('quantity') ?? 0;
                    
                    $warehouse = Warehouse::find($warehouseId);
                    
                    if ($currentStock > 0) {
                        $summaryData[] = [
                            'warehouse_name' => $warehouse ? $warehouse->name : '-',
                            'code' => $item->code,
                            'name' => $item->name,
                            'category' => $item->category_name ?? '-',
                            'unit' => $unitName,
                            'stock_in' => $stockIn,
                            'stock_out' => $stockOut,
                            'current_stock' => $currentStock,
                        ];
                    }
                } else {
                    // Show all warehouses with stock
                    $stocks = Stock::where('item_id', $item->id)
                        ->where('quantity', '>', 0)
                        ->with('warehouse')
                        ->get();
                    
                    foreach ($stocks as $stock) {
                        // Calculate stock in/out for this specific warehouse
                        $whStockIn = Submission::where('item_id', $item->id)
                            ->where('warehouse_id', $stock->warehouse_id)
                            ->where('status', 'approved')
                            ->whereNotNull('submitted_at')
                            ->when($year, fn($q) => $q->whereYear('submitted_at', $year))
                            ->when($month, fn($q) => $q->whereMonth('submitted_at', $month))
                            ->sum('quantity') ?? 0;
                            
                        $whStockOut = StockRequest::where('item_id', $item->id)
                            ->where('warehouse_id', $stock->warehouse_id)
                            ->where('status', 'approved')
                            ->when($year, fn($q) => $q->whereYear('approved_at', $year))
                            ->when($month, fn($q) => $q->whereMonth('approved_at', $month))
                            ->sum('base_quantity') ?? 0;
                        
                        $summaryData[] = [
                            'warehouse_name' => $stock->warehouse->name ?? '-',
                            'code' => $item->code,
                            'name' => $item->name,
                            'category' => $item->category_name ?? '-',
                            'unit' => $unitName,
                            'stock_in' => $whStockIn,
                            'stock_out' => $whStockOut,
                            'current_stock' => $stock->quantity,
                        ];
                    }
                }
            }

            $totals = [
                'total_items' => count($summaryData),
                'total_stock_in' => collect($summaryData)->sum('stock_in'),
                'total_stock_out' => collect($summaryData)->sum('stock_out'),
                'total_current_stock' => collect($summaryData)->sum('current_stock'),
            ];

            \Log::info('PDF data prepared', [
                'total_items' => count($summaryData),
                'totals' => $totals
            ]);

            $filters = $request->all();
            
            \Log::info('Generating PDF view');
            
            $pdf = Pdf::loadView('admin.reports.stock-summary-pdf', compact('summaryData', 'totals', 'filters'))
                ->setPaper('a4', 'landscape');

            \Log::info('PDF generated successfully');
            
            return $pdf->download('Laporan_Ringkasan_Stok_' . date('Y-m-d_His') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Stock Summary PDF Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat export PDF: ' . $e->getMessage());
        }

        }
}
