<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Category;
use App\Models\Item;
use App\Models\Stock;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionReportExport;
use App\Exports\StockValueReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class GudangReportController extends Controller
{
    /**
     * Display reports index page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        // Check if user has warehouses assigned
        if ($warehouseIds->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        // Get warehouse names
        $warehouses = $user->warehouses;
        
        // Get filter options
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        
        // Get years from submissions
        $years = Submission::whereIn('warehouse_id', $warehouseIds)
            ->selectRaw('YEAR(submitted_at) as year')
            ->whereNotNull('submitted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('gudang.reports.index', compact(
            'warehouses',
            'categories',
            'items',
            'years'
        ));
    }

    /**
     * Display transaction report
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        if ($warehouseIds->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        $query = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'staff'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->whereNotNull('submitted_at');

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

        // Filter by Year
        if ($request->filled('year')) {
            $query->whereYear('submitted_at', $request->year);
        }

        // Filter by Month
        if ($request->filled('month')) {
            $query->whereMonth('submitted_at', $request->month);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('submitted_at', 'desc')->paginate(50);

        // Get filter options
        $warehouses = $user->warehouses;
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        
        $years = Submission::whereIn('warehouse_id', $warehouseIds)
            ->selectRaw('YEAR(submitted_at) as year')
            ->whereNotNull('submitted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('gudang.reports.transactions', compact(
            'transactions',
            'warehouses',
            'categories',
            'items',
            'years'
        ));
    }

    /**
     * Display stock value report
     */
    public function stockValues(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        if ($warehouseIds->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        $query = Stock::with(['item.category', 'warehouse'])
            ->whereIn('warehouse_id', $warehouseIds)
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

        $stocks = $query->orderBy('updated_at', 'desc')->paginate(50);

        // Calculate total value
        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($stocks as $stock) {
            $latestSubmission = Submission::where('item_id', $stock->item_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->whereNotNull('unit_price')
                ->whereNotNull('submitted_at')
                ->orderBy('submitted_at', 'desc')
                ->first();

            $unitPrice = $latestSubmission ? $latestSubmission->unit_price : 0;
            $stock->latest_unit_price = $unitPrice;
            $stock->total_value = $stock->quantity * $unitPrice;

            $totalQuantity += $stock->quantity;
            $totalValue += $stock->total_value;
        }

        // Get filter options
        $warehouses = $user->warehouses;
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();

        return view('gudang.reports.stock-values', compact(
            'stocks',
            'warehouses',
            'categories',
            'items',
            'totalQuantity',
            'totalValue'
        ));
    }

    /**
     * Export transaction report to Excel
     */
    public function exportTransactions(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();

        if (empty($warehouseIds)) {
            return redirect()->back()
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        // Collect filters
        $filters = [
            'warehouse_ids' => $warehouseIds, // Only user's warehouses
            'category_id' => $request->category_id,
            'item_name' => $request->item_name,
            'item_code' => $request->item_code,
            'year' => $request->year,
            'month' => $request->month,
            'status' => $request->status,
        ];

        $filename = 'laporan-transaksi-gudang-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(new TransactionReportExport($filters), $filename);
    }

    /**
     * Export stock value report to Excel
     */
    public function exportStockValues(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();

        if (empty($warehouseIds)) {
            return redirect()->back()
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        // Collect filters
        $filters = [
            'warehouse_ids' => $warehouseIds, // Only user's warehouses
            'category_id' => $request->category_id,
            'item_name' => $request->item_name,
            'item_code' => $request->item_code,
        ];

        $filename = 'laporan-stok-nilai-gudang-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(new StockValueReportExport($filters), $filename);
    }

    /**
     * Export transaction report to PDF
     */
    public function exportTransactionsPdf(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        if ($warehouseIds->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        $query = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'approvals.admin'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->whereNotNull('submitted_at');

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

        if ($request->filled('year')) {
            $query->whereYear('submitted_at', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('submitted_at', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('submitted_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_transactions' => $transactions->count(),
            'total_stock_in' => $transactions->where('status', 'approved')->sum('quantity'),
            'approved_count' => $transactions->where('status', 'approved')->count(),
            'pending_count' => $transactions->where('status', 'pending')->count(),
            'rejected_count' => $transactions->where('status', 'rejected')->count(),
        ];

        $filters = $request->all();
        $warehouses = $user->warehouses;

        $pdf = Pdf::loadView('gudang.reports.transactions-pdf', compact('transactions', 'stats', 'filters', 'warehouses'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-transaksi-gudang-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export stock value report to PDF
     */
    public function exportStockValuesPdf(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        if ($warehouseIds->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        $query = Stock::with(['item.category', 'warehouse'])
            ->whereIn('warehouse_id', $warehouseIds)
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

        $stocks = $query->orderBy('updated_at', 'desc')->get();

        // Calculate stock values
        $stocksData = $stocks->map(function($stock) {
            $latestSubmission = Submission::where('item_id', $stock->item_id)
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
        $warehouses = $user->warehouses;

        $pdf = Pdf::loadView('gudang.reports.stock-values-pdf', compact('stocksData', 'stats', 'filters', 'warehouses'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-nilai-gudang-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Display monthly report
     */
    public function monthly(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        if ($warehouseIds->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        $query = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'staff'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->whereNotNull('submitted_at')
          ->where('status', 'approved');

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

        // Filter by Year
        if ($request->filled('year')) {
            $query->whereYear('submitted_at', $request->year);
        }

        // Filter by Month
        if ($request->filled('month')) {
            $query->whereMonth('submitted_at', $request->month);
        }

        $transactions = $query->orderBy('submitted_at', 'desc')->paginate(50);

        // Calculate monthly statistics
        $stats = [
            'total_transactions' => $transactions->count(),
            'total_quantity' => $transactions->sum('quantity'),
            'total_value' => $transactions->sum(function($transaction) {
                return $transaction->quantity * $transaction->unit_price;
            }),
        ];

        // Get filter options
        $warehouses = $user->warehouses;
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        
        $years = Submission::whereIn('warehouse_id', $warehouseIds)
            ->selectRaw('YEAR(submitted_at) as year')
            ->whereNotNull('submitted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('gudang.reports.monthly', compact(
            'transactions',
            'warehouses',
            'categories',
            'items',
            'years',
            'stats'
        ));
    }
}
