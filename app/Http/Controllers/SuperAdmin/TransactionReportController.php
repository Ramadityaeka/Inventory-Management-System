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
        // Get filtered data
        $query = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'approvals.admin'
        ])->whereNotNull('submitted_at');

        // Apply same filters as index method
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

        // Generate CSV
        $filename = 'laporan-transaksi-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($transactions, $request) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Title rows
            fputcsv($file, ['LAPORAN TRANSAKSI BARANG MASUK & KELUAR']);
            
            // Period info
            if ($request->filled('month') && $request->filled('year')) {
                $periodText = \Carbon\Carbon::create($request->year, $request->month)->locale('id')->translatedFormat('F Y');
                fputcsv($file, [$periodText]);
            } elseif ($request->filled('year')) {
                fputcsv($file, ['Tahun ' . $request->year]);
            } else {
                fputcsv($file, ['Semua Periode']);
            }
            
            fputcsv($file, []); // Empty row
            
            // Info rows
            fputcsv($file, ['Tanggal Cetak:', date('d F Y H:i') . ' WIB']);
            fputcsv($file, ['Dicetak oleh:', auth()->user()->name]);
            
            if ($request->filled('warehouse_id')) {
                $warehouse = Warehouse::find($request->warehouse_id);
                fputcsv($file, ['Gudang:', $warehouse ? $warehouse->name : '-']);
            }
            if ($request->filled('category_id')) {
                $category = Category::find($request->category_id);
                fputcsv($file, ['Kategori:', $category ? $category->name : '-']);
            }
            
            fputcsv($file, []); // Empty row
            
            // Summary statistics
            $approvedCount = $transactions->where('status', 'approved')->count();
            $pendingCount = $transactions->where('status', 'pending')->count();
            $rejectedCount = $transactions->where('status', 'rejected')->count();
            
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Transaksi:', $transactions->count()]);
            fputcsv($file, ['Disetujui:', $approvedCount]);
            fputcsv($file, ['Menunggu:', $pendingCount]);
            fputcsv($file, ['Ditolak:', $rejectedCount]);
            fputcsv($file, []); // Empty row
            
            // Table headers
            fputcsv($file, [
                'No',
                'Gudang',
                'Kode Barang',
                'Nama Barang',
                'Kategori',
                'Jumlah',
                'Satuan',
                'Sisa Stok',
                'Keterangan',
                'Status',
                'Diproses Oleh',
                'Waktu Proses',
                'Waktu Submit'
            ]);

            // Data rows
            foreach ($transactions as $index => $transaction) {
                $currentStock = Stock::where('warehouse_id', $transaction->warehouse_id)
                    ->where('item_id', $transaction->item_id)
                    ->first();
                $remainingStock = $currentStock ? $currentStock->quantity : 0;
                $approval = $transaction->approvals->first();

                $statusText = match($transaction->status) {
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    default => 'Menunggu'
                };

                fputcsv($file, [
                    $index + 1,
                    $transaction->warehouse->name,
                    $transaction->item->code,
                    $transaction->item->name,
                    $transaction->item->category->name,
                    $transaction->quantity,
                    $transaction->item->unit,
                    $remainingStock,
                    $transaction->notes ?? 'Penerimaan dari ' . ($transaction->supplier->name ?? '-'),
                    $statusText,
                    $approval ? $approval->admin->name : '-',
                    $approval ? $approval->created_at->format('d/m/Y H:i') : '-',
                    $transaction->submitted_at->format('d/m/Y H:i')
                ]);
            }
            
            fputcsv($file, []); // Empty row
            fputcsv($file, []); // Empty row
            
            // Footer note
            fputcsv($file, ['Catatan:']);
            fputcsv($file, ['Laporan ini dibuat secara otomatis oleh sistem.']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
