<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\Submission;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class MonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        // Set default month and year
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Get all warehouses and categories for filter
        $warehouses = Warehouse::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $selectedWarehouses = $request->input('warehouse_ids', []);
        $categoryId = $request->input('category_id');
        
        // Generate report data if requested
        $reportData = null;
        if ($request->filled('generate') || $request->filled('month')) {
            if (empty($selectedWarehouses)) {
                $selectedWarehouses = $warehouses->pluck('id')->toArray();
            }
            $reportData = $this->generateReportData($selectedWarehouses, $month, $year, $categoryId);
        }
        
        return view('admin.reports.monthly', compact(
            'warehouses',
            'categories',
            'selectedWarehouses',
            'month',
            'year',
            'reportData'
        ));
    }
    
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        return redirect()->route('admin.reports.monthly', [
            'warehouse_ids' => $validated['warehouse_ids'] ?? [],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'category_id' => $validated['category_id'] ?? null,
            'generate' => 1
        ]);
    }
    
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        $warehouseIds = $validated['warehouse_ids'] ?? Warehouse::pluck('id')->toArray();
        
        // Generate report data
        $reportData = $this->generateReportData(
            $warehouseIds,
            $validated['month'], 
            $validated['year'],
            $validated['category_id'] ?? null
        );
        
        // Generate PDF filename
        $filename = 'Monthly_Report_All_Warehouses_' . 
                    Carbon::create($validated['year'], $validated['month'])->format('F_Y') . '.pdf';
        
        // Load PDF view
        $pdf = PDF::loadView('admin.reports.monthly-pdf', compact('reportData'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download($filename);
    }
    
    private function generateReportData($warehouseIds, $month, $year, $categoryId = null)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Base query for stock movements
        $movementsQuery = StockMovement::with(['item.category', 'warehouse', 'creator'])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter by category if specified
        if ($categoryId) {
            $movementsQuery->whereHas('item', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        
        $movements = $movementsQuery->orderBy('created_at', 'desc')->get();
        
        // Get detailed transactions (submissions)
        $transactionsQuery = Submission::with(['item.category', 'staff', 'warehouse', 'approvals.admin'])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        if ($categoryId) {
            $transactionsQuery->whereHas('item', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        
        $transactions = $transactionsQuery->orderBy('created_at', 'desc')->get();
        
        // Get submissions for the period
        $submissions = Submission::whereIn('warehouse_id', $warehouseIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        // Get transfers for the period
        $transfersOut = Transfer::whereIn('from_warehouse_id', $warehouseIds)
            ->whereBetween('requested_at', [$startDate, $endDate])
            ->count();
        
        $transfersIn = Transfer::whereIn('to_warehouse_id', $warehouseIds)
            ->whereBetween('requested_at', [$startDate, $endDate])
            ->count();
        
        // Get current stock levels with price info
        $currentStocksQuery = Stock::with(['item.category', 'warehouse'])
            ->whereIn('warehouse_id', $warehouseIds);
            
        if ($categoryId) {
            $currentStocksQuery->whereHas('item', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        
        $currentStocks = $currentStocksQuery->get();
        
        // Calculate stock values with prices
        $stocksWithValues = $currentStocks->map(function($stock) use ($startDate, $endDate) {
            // Get latest submission price for this item
            $latestSubmission = Submission::where('warehouse_id', $stock->warehouse_id)
                ->where('item_id', $stock->item_id)
                ->where('status', 'approved')
                ->whereNotNull('unit_price')
                ->orderBy('created_at', 'desc')
                ->first();
            
            $unitPrice = $latestSubmission ? $latestSubmission->unit_price : 0;
            $totalValue = $stock->quantity * $unitPrice;
            
            return [
                'item' => $stock->item,
                'warehouse' => $stock->warehouse,
                'quantity' => $stock->quantity,
                'unit_price' => $unitPrice,
                'total_value' => $totalValue,
            ];
        });
        
        // Calculate purchase values
        $totalPurchaseValue = $submissions->where('status', 'approved')
            ->whereNotNull('total_price')
            ->sum('total_price');
        
        // Calculate total stock value
        $totalStockValue = $stocksWithValues->sum('total_value');
        
        // Get warehouse names
        $warehouseNames = Warehouse::whereIn('id', $warehouseIds)->pluck('name')->join(', ');
        
        return [
            'period' => $startDate->format('F Y'),
            'warehouses' => $warehouseNames,
            'warehouse_count' => count($warehouseIds),
            'total_stock_in' => $movements->where('quantity', '>', 0)->sum('quantity'),
            'total_stock_out' => abs($movements->where('quantity', '<', 0)->sum('quantity')),
            'total_movements' => $movements->count(),
            'transactions' => $transactions,
            'submissions_count' => $submissions->count(),
            'submissions_approved' => $submissions->where('status', 'approved')->count(),
            'submissions_pending' => $submissions->where('status', 'pending')->count(),
            'submissions_rejected' => $submissions->where('status', 'rejected')->count(),
            'transfers_out' => $transfersOut,
            'transfers_in' => $transfersIn,
            'current_stocks' => $currentStocks,
            'stocks_with_values' => $stocksWithValues,
            'total_stock_value' => $totalStockValue,
            'low_stock_items' => 0, // No threshold anymore
            'out_of_stock_items' => $currentStocks->where('quantity', 0)->count(),
            'total_purchase_value' => $totalPurchaseValue,
        ];
    }
}
