<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockValueReportExport;

class StockValueReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->where('quantity', '>', 0);

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by Item Name
        if ($request->filled('item_name')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->item_name . '%');
            });
        }

        // Filter by Item Code
        if ($request->filled('item_code')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->item_code . '%');
            });
        }

        // Filter by Warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stocks = $query->orderBy('updated_at', 'desc')->paginate(50);

        // Calculate stock values and additional data
        $stocksData = $stocks->map(function($stock) use ($request) {
            // Get latest approved submission for this item and warehouse to get unit price
            $latestSubmission = \App\Models\Submission::where('item_id', $stock->item_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('status', 'approved')
                ->whereNotNull('unit_price')
                ->orderBy('submitted_at', 'desc')
                ->first();

            $unitPrice = $latestSubmission ? $latestSubmission->unit_price : 0;
            $totalValue = $stock->quantity * $unitPrice;

            return [
                'stock' => $stock,
                'item' => $stock->item,
                'warehouse' => $stock->warehouse,
                'quantity' => $stock->quantity,
                'unit_price' => $unitPrice,
                'total_value' => $totalValue,
            ];
        });

        // Calculate totals
        $totalStockValue = $stocksData->sum('total_value');
        $totalItems = $stocksData->count();
        $totalQuantity = $stocksData->sum('quantity');

        // Get filter options
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.reports.stock-values', compact(
            'stocks',
            'stocksData',
            'categories',
            'items',
            'warehouses',
            'totalStockValue',
            'totalItems',
            'totalQuantity'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->where('quantity', '>', 0);

        // Apply same filters
        if ($request->filled('category_id')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('item_name')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->item_name . '%');
            });
        }

        if ($request->filled('item_code')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->item_code . '%');
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stocks = $query->orderBy('updated_at', 'desc')->get();

        // Calculate stock values
        $stocksData = $stocks->map(function($stock) {
            $latestSubmission = \App\Models\Submission::where('item_id', $stock->item_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('status', 'approved')
                ->whereNotNull('unit_price')
                ->orderBy('submitted_at', 'desc')
                ->first();

            $unitPrice = $latestSubmission ? $latestSubmission->unit_price : 0;
            $totalValue = $stock->quantity * $unitPrice;

            return [
                'stock' => $stock,
                'item' => $stock->item,
                'warehouse' => $stock->warehouse,
                'quantity' => $stock->quantity,
                'unit_price' => $unitPrice,
                'total_value' => $totalValue,
            ];
        });

        $stats = [
            'total_stock_value' => $stocksData->sum('total_value'),
            'total_items' => $stocksData->count(),
            'total_quantity' => $stocksData->sum('quantity'),
        ];

        $filters = $request->all();

        $pdf = Pdf::loadView('admin.reports.stock-values-pdf', compact('stocksData', 'stats', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-nilai-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Collect filters
        $filters = [
            'category_id' => $request->input('category_id'),
            'item_name' => $request->input('item_name'),
            'item_code' => $request->input('item_code'),
            'warehouse_id' => $request->input('warehouse_id'),
        ];

        // Generate filename
        $filename = 'laporan-stok-nilai-' . date('Y-m-d') . '.xlsx';
        
        // Download as XLSX using StockValueReportExport class
        return Excel::download(
            new StockValueReportExport($filters),
            $filename
        );
    }

    /**
     * Search items for autocomplete
     */
    public function searchItems(Request $request)
    {
        $search = $request->get('q', '');
        
        $items = Item::where('name', 'LIKE', '%' . $search . '%')
            ->orWhere('code', 'LIKE', '%' . $search . '%')
            ->limit(10)
            ->get(['id', 'name', 'code']);
        
        return response()->json($items);
    }
}
