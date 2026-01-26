<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class StockReportController extends Controller
{
    /**
     * Laporan Perpindahan Barang
     */
    public function movements(Request $request)
    {
        $warehouses = auth()->user()->warehouses;
        $categories = Category::orderBy('name')->get();
        
        $query = StockMovement::with(['item.category', 'warehouse', 'creator'])
            ->whereIn('warehouse_id', $warehouses->pluck('id'));
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Filter by month & year
        if ($request->filled('month') && $request->filled('year')) {
            $startDate = Carbon::create($request->year, $request->month, 1)->startOfMonth();
            $endDate = Carbon::create($request->year, $request->month, 1)->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        // Filter by movement type
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Calculate running totals per item
        $movements->getCollection()->transform(function($movement) {
            $stockAfter = Stock::where('warehouse_id', $movement->warehouse_id)
                ->where('item_id', $movement->item_id)
                ->first();
            
            $movement->current_stock = $stockAfter ? $stockAfter->quantity : 0;
            return $movement;
        });
        
        return view('gudang.reports.movements', compact('movements', 'warehouses', 'categories'));
    }
    
    /**
     * Laporan Barang Masuk & Keluar
     */
    public function inOut(Request $request)
    {
        $warehouses = auth()->user()->warehouses;
        $categories = Category::orderBy('name')->get();
        
        $query = StockMovement::with(['item.category', 'warehouse', 'creator'])
            ->whereIn('warehouse_id', $warehouses->pluck('id'))
            ->whereIn('movement_type', ['in', 'out']);
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Filter by month & year
        if ($request->filled('month') && $request->filled('year')) {
            $startDate = Carbon::create($request->year, $request->month, 1)->startOfMonth();
            $endDate = Carbon::create($request->year, $request->month, 1)->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        // Filter by type (in or out)
        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Statistics
        $stats = [
            'total_in' => StockMovement::whereIn('warehouse_id', $warehouses->pluck('id'))
                ->where('movement_type', 'in')
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                })
                ->sum('quantity'),
            'total_out' => abs(StockMovement::whereIn('warehouse_id', $warehouses->pluck('id'))
                ->where('movement_type', 'out')
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->end_date);
                })
                ->sum('quantity')),
        ];
        
        return view('gudang.reports.in-out', compact('movements', 'warehouses', 'categories', 'stats'));
    }
    
    /**
     * Laporan Stok Available & Out of Stock
     */
    public function stockStatus(Request $request)
    {
        $warehouses = auth()->user()->warehouses;
        $categories = Category::orderBy('name')->get();
        
        $query = Stock::with(['item.category', 'warehouse'])
            ->whereIn('warehouse_id', $warehouses->pluck('id'));
        
        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'available') {
                $query->where('quantity', '>', 0);
            } elseif ($request->status == 'out_of_stock') {
                $query->where('quantity', '=', 0);
            }
        }
        
        $stocks = $query->orderBy('quantity', 'asc')->paginate(50);
        
        // Statistics
        $stats = [
            'total_items' => Stock::whereIn('warehouse_id', $warehouses->pluck('id'))->count(),
            'available' => Stock::whereIn('warehouse_id', $warehouses->pluck('id'))
                ->where('quantity', '>', 0)->count(),
            'out_of_stock' => Stock::whereIn('warehouse_id', $warehouses->pluck('id'))
                ->where('quantity', '=', 0)->count(),
        ];
        
        return view('gudang.reports.stock-status', compact('stocks', 'warehouses', 'categories', 'stats'));
    }
    
    /**
     * Export Movements to PDF
     */
    public function exportMovementsPdf(Request $request)
    {
        // Similar to movements() but return PDF
        $warehouses = auth()->user()->warehouses;
        
        $query = StockMovement::with(['item.category', 'warehouse', 'creator'])
            ->whereIn('warehouse_id', $warehouses->pluck('id'));
        
        // Apply same filters...
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->get();
        
        $pdf = PDF::loadView('gudang.reports.movements-pdf', compact('movements'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download('Laporan_Perpindahan_' . now()->format('Y-m-d') . '.pdf');
    }
}
