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

        // Get Submissions (Barang Masuk)
        $submissionsQuery = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'staff'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->whereNotNull('submitted_at');

        // Get Stock Requests (Barang Keluar)
        $stockRequestsQuery = \App\Models\StockRequest::with([
            'item.category',
            'warehouse',
            'staff',
            'approver'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->where('status', 'approved');

        // Get Stock Adjustments (Penyesuaian Stok)
        $adjustmentsQuery = \App\Models\StockMovement::with([
            'item.category',
            'warehouse',
            'creator'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->where('movement_type', 'adjustment');

        // Filter by Category
        if ($request->filled('category_id')) {
            $submissionsQuery->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
            $stockRequestsQuery->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
            $adjustmentsQuery->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by Item Name
        if ($request->filled('item_name')) {
            $submissionsQuery->whereHas('item', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->item_name . '%');
            });
            $stockRequestsQuery->whereHas('item', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->item_name . '%');
            });
            $adjustmentsQuery->whereHas('item', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->item_name . '%');
            });
        }

        // Filter by Item Code
        if ($request->filled('item_code')) {
            $submissionsQuery->whereHas('item', function($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->item_code . '%');
            });
            $stockRequestsQuery->whereHas('item', function($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->item_code . '%');
            });
            $adjustmentsQuery->whereHas('item', function($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->item_code . '%');
            });
        }

        // Filter by Year
        if ($request->filled('year')) {
            $submissionsQuery->whereYear('submitted_at', $request->year);
            $stockRequestsQuery->whereYear('created_at', $request->year);
            $adjustmentsQuery->whereYear('created_at', $request->year);
        }

        // Filter by Month
        if ($request->filled('month')) {
            $submissionsQuery->whereMonth('submitted_at', $request->month);
            $stockRequestsQuery->whereMonth('created_at', $request->month);
            $adjustmentsQuery->whereMonth('created_at', $request->month);
        }

        // Apply status filter only to submissions
        if ($request->filled('status')) {
            $submissionsQuery->where('status', $request->status);
        }

        // Get results
        $submissions = $submissionsQuery->get()->map(function($item) {
            $item->transaction_type = 'in';
            $item->transaction_date = $item->submitted_at;
            return $item;
        });

        // Only get stock requests if status is empty or approved
        $stockRequests = collect([]);
        if (!$request->filled('status') || $request->status == 'approved') {
            $stockRequests = $stockRequestsQuery->get()->map(function($item) {
                $item->transaction_type = 'out';
                $item->transaction_date = $item->approved_at ?? $item->created_at;
                return $item;
            });
        }

        // Get adjustments (only when status is empty or approved)
        $adjustments = collect([]);
        if (!$request->filled('status') || $request->status == 'approved') {
            $adjustments = $adjustmentsQuery->get()->map(function($item) {
                $item->transaction_type = 'adjustment';
                $item->transaction_date = $item->created_at;
                // For display purposes, set status as approved since adjustments are immediate
                $item->status = 'approved';
                return $item;
            });
        }

        // Merge and sort
        $allTransactions = $submissions->concat($stockRequests)->concat($adjustments)->sortByDesc('transaction_date');
        
        // Calculate historical stock (stock after each transaction)
        // Get current stocks for all items/warehouses in the transaction list
        $currentStocks = [];
        foreach ($allTransactions as $transaction) {
            $key = $transaction->item_id . '_' . $transaction->warehouse_id;
            if (!isset($currentStocks[$key])) {
                $stock = \App\Models\Stock::where('item_id', $transaction->item_id)
                             ->where('warehouse_id', $transaction->warehouse_id)
                             ->first();
                $currentStocks[$key] = $stock ? $stock->quantity : 0;
            }
        }
        
        // Calculate stock_after for each transaction (working backwards from current)
        $runningStocks = $currentStocks; // Start with current stocks
        foreach ($allTransactions as $transaction) {
            $key = $transaction->item_id . '_' . $transaction->warehouse_id;
            
            // The stock after this transaction is the current running stock
            $transaction->stock_after = $runningStocks[$key];
            
            // Update running stock by reversing this transaction
            if ($transaction->transaction_type == 'in') {
                $runningStocks[$key] -= $transaction->quantity;
            } elseif ($transaction->transaction_type == 'out') {
                $runningStocks[$key] += ($transaction->base_quantity ?? $transaction->quantity);
            } elseif ($transaction->transaction_type == 'adjustment') {
                $runningStocks[$key] -= $transaction->quantity;
            }
        }
        
        // Manual pagination
        $perPage = 50;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $allTransactions->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $allTransactions->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get filter options
        $warehouses = $user->warehouses;
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        
        // Calculate statistics
        $adjustmentsIn = $adjustments->filter(function($item) {
            return $item->quantity > 0;
        });
        $adjustmentsOut = $adjustments->filter(function($item) {
            return $item->quantity < 0;
        });
        
        $stats = [
            'total_transactions' => $allTransactions->count(),
            'total_stock_in' => $submissions->where('status', 'approved')->sum('quantity') + $adjustmentsIn->sum('quantity'),
            'total_stock_out' => $stockRequests->sum('base_quantity') + abs($adjustmentsOut->sum('quantity')),
            'approved_count' => $submissions->where('status', 'approved')->count() + $stockRequests->count() + $adjustments->count(),
            'pending_count' => $submissions->where('status', 'pending')->count(),
            'rejected_count' => $submissions->where('status', 'rejected')->count(),
        ];
        
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
            'years',
            'stats'
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

        $query = Stock::with(['item.category', 'item.itemUnits', 'warehouse'])
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

        // Calculate totals from ALL data (before pagination)
        // Clone the query to get all records for total calculation
        $allStocks = (clone $query)->get();
        
        $totalQuantity = 0;
        $totalValue = 0;
        
        foreach ($allStocks as $stock) {
            $totalQuantity += $stock->quantity;
            
            $latestSubmission = Submission::where('item_id', $stock->item_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->whereNotNull('unit_price')
                ->whereNotNull('submitted_at')
                ->orderBy('submitted_at', 'desc')
                ->first();

            $unitPrice = $latestSubmission ? $latestSubmission->unit_price : 0;
            $totalValue += ($stock->quantity * $unitPrice);
        }
        
        $totalItems = $allStocks->count();

        // Now paginate for display
        $stocks = $query->orderBy('updated_at', 'desc')->paginate(50);

        // Add unit price and total value to each stock in current page
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
            'totalValue',
            'totalItems'
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

        // Get Submissions (Barang Masuk)
        $submissionsQuery = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'staff',
            'approvals.admin'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->whereNotNull('submitted_at');

        // Get Stock Requests (Barang Keluar)
        $stockRequestsQuery = \App\Models\StockRequest::with([
            'item.category',
            'warehouse',
            'staff',
            'approver'
        ])->whereIn('warehouse_id', $warehouseIds)
          ->where('status', 'approved');

        // Apply same filters
        if ($request->filled('category_id')) {
            $submissionsQuery->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
            $stockRequestsQuery->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('item_name')) {
            $submissionsQuery->whereHas('item', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->item_name . '%');
            });
            $stockRequestsQuery->whereHas('item', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->item_name . '%');
            });
        }

        if ($request->filled('item_code')) {
            $submissionsQuery->whereHas('item', function($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->item_code . '%');
            });
            $stockRequestsQuery->whereHas('item', function($q) use ($request) {
                $q->where('code', 'LIKE', '%' . $request->item_code . '%');
            });
        }

        if ($request->filled('year')) {
            $submissionsQuery->whereYear('submitted_at', $request->year);
            $stockRequestsQuery->whereYear('created_at', $request->year);
        }

        if ($request->filled('month')) {
            $submissionsQuery->whereMonth('submitted_at', $request->month);
            $stockRequestsQuery->whereMonth('created_at', $request->month);
        }

        // Apply status filter only to submissions
        if ($request->filled('status')) {
            $submissionsQuery->where('status', $request->status);
        }

        // Get results
        $submissions = $submissionsQuery->get()->map(function($item) {
            $item->transaction_type = 'in';
            $item->transaction_date = $item->submitted_at;
            return $item;
        });

        // Only get stock requests if status is empty or approved
        $stockRequests = collect([]);
        if (!$request->filled('status') || $request->status == 'approved') {
            $stockRequests = $stockRequestsQuery->get()->map(function($item) {
                $item->transaction_type = 'out';
                $item->transaction_date = $item->approved_at ?? $item->created_at;
                return $item;
            });
        }

        $transactions = $submissions->concat($stockRequests)->sortByDesc('transaction_date');

        // Calculate statistics
        $stats = [
            'total_transactions' => $transactions->count(),
            'total_stock_in' => $submissions->where('status', 'approved')->sum('quantity'),
            'total_stock_out' => $stockRequests->sum('base_quantity'),
            'approved_count' => $transactions->where('status', 'approved')->count(),
            'pending_count' => $submissions->where('status', 'pending')->count(),
            'rejected_count' => $submissions->where('status', 'rejected')->count(),
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

    /**
     * Display stock summary report
     */
    public function stockSummary(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        if ($warehouseIds->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

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
        $year = $request->filled('year') ? $request->year : null;
        $month = $request->filled('month') ? $request->month : null;
        
        // Filter by specific warehouse if provided
        $filterWarehouseIds = $warehouseIds;
        if ($request->filled('warehouse_id')) {
            $filterWarehouseIds = $warehouseIds->filter(function($id) use ($request) {
                return $id == $request->warehouse_id;
            });
        }

        foreach ($items as $item) {
            // Get unit information
            $firstUnit = $item->itemUnits->first();
            $unitName = $firstUnit ? $firstUnit->name : ($item->unit ?? '-');

            // Loop through user's warehouses (filtered if applicable)
            $stocks = Stock::where('item_id', $item->id)
                ->whereIn('warehouse_id', $filterWarehouseIds)
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
                    
                $whStockOut = \App\Models\StockRequest::where('item_id', $item->id)
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
        $warehouses = $user->warehouses;
        
        // Get years from submissions
        $years = Submission::whereIn('warehouse_id', $warehouseIds)
            ->selectRaw('YEAR(submitted_at) as year')
            ->whereNotNull('submitted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('gudang.reports.stock-summary', compact(
            'summary',
            'categories',
            'warehouses',
            'years',
            'totals'
        ));
    }

    /**
     * Export stock summary report to Excel
     */
    public function exportStockSummaryExcel(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();

        if (empty($warehouseIds)) {
            return redirect()->back()
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        $filters = [
            'warehouse_ids' => $warehouseIds,
            'warehouse_id' => $request->warehouse_id,
            'category_id' => $request->category_id,
            'item_name' => $request->item_name,
            'item_code' => $request->item_code,
            'year' => $request->year,
            'month' => $request->month,
        ];

        $filename = 'laporan-ringkasan-stok-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(new \App\Exports\StockSummaryReportExport($filters), $filename);
    }

    /**
     * Export stock summary report to PDF
     */
    public function exportStockSummaryPdf(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        if ($warehouseIds->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Anda belum ditugaskan ke gudang manapun.');
        }

        // Build same data as index
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

        $summaryData = [];
        $year = $request->filled('year') ? $request->year : null;
        $month = $request->filled('month') ? $request->month : null;
        
        // Filter by specific warehouse if provided
        $filterWarehouseIds = $warehouseIds;
        if ($request->filled('warehouse_id')) {
            $filterWarehouseIds = $warehouseIds->filter(function($id) use ($request) {
                return $id == $request->warehouse_id;
            });
        }

        foreach ($items as $item) {
            $firstUnit = $item->itemUnits->first();
            $unitName = $firstUnit ? $firstUnit->name : ($item->unit ?? '-');

            $stocks = Stock::where('item_id', $item->id)
                ->whereIn('warehouse_id', $filterWarehouseIds)
                ->where('quantity', '>', 0)
                ->with('warehouse')
                ->get();
            
            foreach ($stocks as $stock) {
                $whStockIn = Submission::where('item_id', $item->id)
                    ->where('warehouse_id', $stock->warehouse_id)
                    ->where('status', 'approved')
                    ->whereNotNull('submitted_at')
                    ->when($year, fn($q) => $q->whereYear('submitted_at', $year))
                    ->when($month, fn($q) => $q->whereMonth('submitted_at', $month))
                    ->sum('quantity') ?? 0;
                    
                $whStockOut = \App\Models\StockRequest::where('item_id', $item->id)
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

        $totals = [
            'total_items' => count($summaryData),
            'total_stock_in' => collect($summaryData)->sum('stock_in'),
            'total_stock_out' => collect($summaryData)->sum('stock_out'),
            'total_current_stock' => collect($summaryData)->sum('current_stock'),
        ];

        $filters = $request->all();

        $pdf = Pdf::loadView('gudang.reports.stock-summary-pdf', compact('summaryData', 'totals', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-ringkasan-stok-' . date('Y-m-d-His') . '.pdf');
    }
}
