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
        $query = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'approvals.admin'
        ])->whereNotNull('submitted_at');

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

        // Filter by Warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by Processed By (Admin)
        if ($request->filled('processed_by')) {
            $query->whereHas('approvals', function($q) use ($request) {
                $q->where('admin_id', $request->processed_by);
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('submitted_at', 'desc')->paginate(50);

        // Get filter options
        $categories = Category::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $admins = User::whereIn('role', ['super_admin', 'admin_gudang'])->orderBy('name')->get();
        
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
            'years'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'approvals.admin'
        ])->whereNotNull('submitted_at');

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

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('processed_by')) {
            $query->whereHas('approvals', function($q) use ($request) {
                $q->where('admin_id', $request->processed_by);
            });
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
