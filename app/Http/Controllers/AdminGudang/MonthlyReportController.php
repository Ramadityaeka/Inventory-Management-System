<?php

namespace App\Http\Controllers\AdminGudang;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\Submission;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class MonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        // Get user warehouse IDs
        $userWarehouses = auth()->user()->warehouses()->pluck('warehouses.id');
        
        if ($userWarehouses->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to any warehouse.');
        }

        // Set default month and year
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Get warehouses for filter
        $warehouses = auth()->user()->warehouses;
        $selectedWarehouse = $request->input('warehouse_id', $userWarehouses->first());
        
        // Generate report data if month and year are selected
        $reportData = null;
        if ($request->filled('generate') || $request->filled('month')) {
            $reportData = $this->generateReportData($selectedWarehouse, $month, $year);
        }
        
        return view('gudang.reports.monthly', compact(
            'warehouses',
            'selectedWarehouse',
            'month',
            'year',
            'reportData'
        ));
    }
    
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);
        
        // Check if user has access to this warehouse
        if (!auth()->user()->warehouses->contains($validated['warehouse_id'])) {
            return redirect()->back()->with('error', 'You do not have access to this warehouse.');
        }
        
        return redirect()->route('gudang.reports.monthly', [
            'warehouse_id' => $validated['warehouse_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'generate' => 1
        ]);
    }
    
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);
        
        // Check if user has access to this warehouse
        if (!auth()->user()->warehouses->contains($validated['warehouse_id'])) {
            abort(403, 'You do not have access to this warehouse.');
        }
        
        // Generate report data
        $reportData = $this->generateReportData(
            $validated['warehouse_id'], 
            $validated['month'], 
            $validated['year']
        );
        
        // Generate PDF filename
        $filename = 'Monthly_Report_' . $reportData['warehouse']->name . '_' . 
                    Carbon::create($validated['year'], $validated['month'])->format('F_Y') . '.pdf';
        
        // Load PDF view
        $pdf = PDF::loadView('gudang.reports.monthly-pdf', compact('reportData'))
            ->setPaper('a4', 'portrait');
        
        return $pdf->download($filename);
    }
    
    private function generateReportData($warehouseId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Get stock movements for the period
        $movements = StockMovement::with(['item', 'warehouse'])
            ->where('warehouse_id', $warehouseId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        // Group movements by item
        $itemMovements = $movements->groupBy('item_id')->map(function ($movements, $itemId) use ($warehouseId, $startDate, $endDate) {
            $item = $movements->first()->item;
            
            // Get current stock
            $currentStock = Stock::where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->first();
            
            // Get submissions with price for this item in this period
            $submissions = Submission::where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->where('status', 'approved')
                ->whereNotNull('unit_price')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            
            $avgPrice = $submissions->avg('unit_price');
            $totalValue = $submissions->sum('total_price');
            $lastPrice = $submissions->sortByDesc('created_at')->first();
            
            return [
                'item' => $item,
                'stock_in' => $movements->where('movement_type', 'in')->sum('quantity'),
                'stock_out' => -$movements->where('movement_type', 'out')->sum('quantity'),  // Since out quantities are negative
                'adjustments' => $movements->where('movement_type', 'adjustment')->sum('quantity'),
                'current_stock' => $currentStock ? $currentStock->quantity : 0,
                'unit' => $item->unit,
                'total_movements' => $movements->count(),
                'in_movements' => $movements->where('movement_type', 'in')->count(),
                'out_movements' => $movements->where('movement_type', 'out')->count(),
                'adjustment_movements' => $movements->where('movement_type', 'adjustment')->count(),
                'avg_price' => $avgPrice,
                'total_value' => $totalValue,
                'last_price' => $lastPrice ? $lastPrice->unit_price : null,
                'purchase_count' => $submissions->count(),
            ];
        });
        
        // Get submissions for the period
        $submissions = Submission::where('warehouse_id', $warehouseId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        // Get transfers for the period
        $transfersOut = Transfer::where('from_warehouse_id', $warehouseId)
            ->whereBetween('requested_at', [$startDate, $endDate])
            ->count();
        
        $transfersIn = Transfer::where('to_warehouse_id', $warehouseId)
            ->whereBetween('requested_at', [$startDate, $endDate])
            ->count();
        
        // Get current stock levels
        $currentStocks = Stock::with('item')
            ->where('warehouse_id', $warehouseId)
            ->get();
        
        // Calculate purchase values
        $totalPurchaseValue = $submissions->where('status', 'approved')
            ->whereNotNull('total_price')
            ->sum('total_price');
        
        $avgPurchaseValue = $submissions->where('status', 'approved')
            ->whereNotNull('unit_price')
            ->avg('unit_price');
        
        return [
            'period' => $startDate->format('F Y'),
            'warehouse' => auth()->user()->warehouses->find($warehouseId),
            'total_stock_in' => $movements->where('quantity', '>', 0)->sum('quantity'),
            'total_stock_out' => abs($movements->where('quantity', '<', 0)->sum('quantity')),
            'total_movements' => $movements->count(),
            'item_movements' => $itemMovements,
            'submissions_count' => $submissions->count(),
            'submissions_approved' => $submissions->where('status', 'approved')->count(),
            'submissions_pending' => $submissions->where('status', 'pending')->count(),
            'submissions_rejected' => $submissions->where('status', 'rejected')->count(),
            'transfers_out' => $transfersOut,
            'transfers_in' => $transfersIn,
            'current_stocks' => $currentStocks,
            'low_stock_items' => $currentStocks->filter(function ($stock) {
                return $stock->quantity <= $stock->item->min_threshold && $stock->quantity > 0;
            })->count(),
            'out_of_stock_items' => $currentStocks->where('quantity', 0)->count(),
            'total_purchase_value' => $totalPurchaseValue,
            'avg_purchase_price' => $avgPurchaseValue,
        ];
    }
}