<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Category;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Stock;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionReportExport;

class TransactionReportController extends Controller
{
    public function index(Request $request)
    {
        // Get Submissions (Barang Masuk)
        $submissionsQuery = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'staff',
            'approvals.admin'
        ])->whereNotNull('submitted_at');

        // Get Stock Requests (Barang Keluar) - only approved
        $stockRequestsQuery = \App\Models\StockRequest::with([
            'item.category',
            'warehouse',
            'staff',
            'approver'
        ])->where('status', 'approved');

        // Apply filters to submissions
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

        if ($request->filled('warehouse_id')) {
            $submissionsQuery->where('warehouse_id', $request->warehouse_id);
            $stockRequestsQuery->where('warehouse_id', $request->warehouse_id);
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

        // Only get stock requests if status is empty or approved (because all stock requests are approved)
        $stockRequests = collect([]);
        if (!$request->filled('status') || $request->status == 'approved') {
            $stockRequests = $stockRequestsQuery->get()->map(function($item) {
                $item->transaction_type = 'out';
                $item->transaction_date = $item->approved_at ?? $item->created_at;
                return $item;
            });
        }

        // Merge and sort
        $allTransactions = $submissions->concat($stockRequests)->sortByDesc('transaction_date');
        
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
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $admins = User::whereIn('role', ['super_admin', 'admin_gudang'])->orderBy('name')->get();
        
        // Calculate statistics
        $stats = [
            'total_transactions' => $allTransactions->count(),
            'total_stock_in' => $submissions->where('status', 'approved')->sum('quantity'),
            'total_stock_out' => $stockRequests->sum('base_quantity'),
            'approved_count' => $submissions->where('status', 'approved')->count() + $stockRequests->count(),
            'pending_count' => $submissions->where('status', 'pending')->count(),
            'rejected_count' => $submissions->where('status', 'rejected')->count(),
        ];
        
        // Get years from submissions
        $years = Submission::selectRaw('YEAR(submitted_at) as year')
            ->whereNotNull('submitted_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('admin.reports.transactions', compact(
            'transactions',
            'categories',
            'items',
            'warehouses',
            'admins',
            'years',
            'stats'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get Submissions (Barang Masuk)
        $submissionsQuery = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'staff',
            'approvals.admin'
        ])->whereNotNull('submitted_at');

        // Get Stock Requests (Barang Keluar)
        $stockRequestsQuery = \App\Models\StockRequest::with([
            'item.category',
            'warehouse',
            'staff',
            'approver'
        ])->where('status', 'approved');

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

        if ($request->filled('warehouse_id')) {
            $submissionsQuery->where('warehouse_id', $request->warehouse_id);
            $stockRequestsQuery->where('warehouse_id', $request->warehouse_id);
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

        $pdf = Pdf::loadView('admin.reports.transactions-pdf', compact('transactions', 'stats', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-transaksi-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Collect filters
        $filters = [
            'category_id' => $request->input('category_id'),
            'item_name' => $request->input('item_name'),
            'item_code' => $request->input('item_code'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'warehouse_id' => $request->input('warehouse_id'),
            'processed_by' => $request->input('processed_by'),
            'status' => $request->input('status'),
        ];

        // Generate filename
        $filename = 'laporan-transaksi-' . date('Y-m-d') . '.xlsx';
        
        // Download as XLSX using TransactionReportExport class
        return Excel::download(
            new TransactionReportExport($filters),
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
