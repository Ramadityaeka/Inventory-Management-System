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
        // Get filtered data
        $query = Stock::with(['item.category', 'warehouse'])
            ->where('quantity', '>', 0);

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

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stocks = $query->orderBy('updated_at', 'desc')->get();

        // Calculate unit prices
        $stocksData = $stocks->map(function ($stock) {
            $latestSubmission = \App\Models\Submission::where('item_id', $stock->item_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->where('status', 'approved')
                ->whereNotNull('submitted_at')
                ->orderBy('submitted_at', 'desc')
                ->first();

            $unitPrice = $latestSubmission && $latestSubmission->quantity > 0
                ? ($latestSubmission->total_price / $latestSubmission->quantity)
                : 0;

            $totalValue = $unitPrice * $stock->quantity;

            return [
                'item' => $stock->item,
                'warehouse' => $stock->warehouse,
                'quantity' => $stock->quantity,
                'unit_price' => $unitPrice,
                'total_value' => $totalValue,
            ];
        });

        // Generate CSV
        $filename = 'laporan-stok-nilai-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($stocksData, $request) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Title rows
            fputcsv($file, ['LAPORAN DAFTAR STOK BARANG & NILAI']);
            fputcsv($file, ['Per Tanggal: ' . date('d F Y')]);
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
            
            // Summary cards
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Jenis Barang:', $stocksData->count()]);
            fputcsv($file, ['Total Jumlah Barang:', $stocksData->sum('quantity')]);
            fputcsv($file, ['Total Nilai Keseluruhan:', $stocksData->sum('total_value')]);
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
                'Harga/Satuan (Rp)',
                'Harga Total (Rp)'
            ]);

            // Data rows
            foreach ($stocksData as $index => $data) {
                fputcsv($file, [
                    $index + 1,
                    $data['warehouse']->name,
                    $data['item']->code,
                    $data['item']->name,
                    $data['item']->category->name,
                    $data['quantity'],
                    $data['item']->unit,
                    $data['unit_price'] > 0 ? $data['unit_price'] : 0,
                    $data['total_value'] > 0 ? $data['total_value'] : 0
                ]);
            }

            // Empty row before total
            fputcsv($file, []);
            
            // Total row
            fputcsv($file, [
                '',
                '',
                '',
                '',
                'TOTAL KESELURUHAN:',
                $stocksData->sum('quantity'),
                'item',
                '',
                $stocksData->sum('total_value')
            ]);
            
            fputcsv($file, []); // Empty row
            fputcsv($file, []); // Empty row
            
            // Footer note
            fputcsv($file, ['Catatan:']);
            fputcsv($file, ['Laporan ini dibuat secara otomatis oleh sistem.']);
            fputcsv($file, ['Harga satuan dan total nilai diambil dari data submission terakhir yang disetujui untuk setiap barang.']);

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
