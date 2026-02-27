<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Notification;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Submission;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $user = auth()->user();

            if ($user->isSuperAdmin()) {
                return $this->superAdminDashboard();
            } elseif ($user->isAdminGudang()) {
                return $this->adminGudangDashboard();
            } elseif ($user->isStaffGudang()) {
                return $this->staffGudangDashboard();
            }

            abort(403, 'Unauthorized: Invalid user role');
            
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            Auth::logout();
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat mengakses dashboard. Silakan login kembali.');
        }
    }

    private function superAdminDashboard()
    {
        try {
            // Consolidated counts in fewer queries
            $warehouseCount = Warehouse::count();
            $activeWarehouseCount = Warehouse::whereHas('stocks', function($q) {
                $q->where('quantity', '>', 0);
            })->count();

            // Consolidated today's stock movement stats (1 query instead of 2)
            $todayMovements = StockMovement::whereDate('created_at', today())
                ->selectRaw("
                    SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as stock_in,
                    SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as stock_out
                ")->first();

            $stats = [
                'total_units' => $warehouseCount,
                'total_warehouses' => $warehouseCount,
                'total_items' => Item::count(),
                'total_stock' => Stock::sum('quantity') ?? 0,
                'pending_transfers' => 0,
                'total_users' => User::count(),
                'active_units' => $activeWarehouseCount,
                'active_warehouses' => $activeWarehouseCount,
                'today_total_stock_in' => $todayMovements->stock_in ?? 0,
                'today_total_stock_out' => $todayMovements->stock_out ?? 0,
                'today_transfers_approved' => 0,
                'today_new_alerts' => Stock::where('quantity', '=', 0)->distinct('item_id')->count('item_id') ?? 0,
                'monthly_transfers_current' => 0,
                'monthly_transfers_target' => 50,
                'monthly_movements_current' => StockMovement::whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->sum(DB::raw('ABS(quantity)')) ?? 0,
                'monthly_movements_target' => 1000,
            ];

            // Optimized monthly movements - last 6 months only
            $monthlyMovements = StockMovement::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw("SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as stock_in"),
                DB::raw("SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as stock_out")
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

            // Optimized top items with limit
            $topItems = Stock::join('items', 'stocks.item_id', '=', 'items.id')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
                ->select(
                    'items.name as item_name',
                    DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
                    DB::raw('SUM(stocks.quantity) as total_stock')
                )
                ->groupBy('items.id', 'items.name', 'categories.name')
                ->orderBy('total_stock', 'desc')
                ->limit(10)
                ->get();

            // Out of stock items
            $lowStockItems = Item::join('stocks', 'items.id', '=', 'stocks.item_id')
                ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
                ->where('stocks.quantity', '=', 0)
                ->select(
                    'items.id as item_id',
                    'items.name as item_name',
                    'warehouses.name as warehouse_name',
                    'stocks.quantity as current_stock'
                )
                ->limit(20)
                ->get();

            // Pending transfers with relationships - Feature disabled
            $pendingTransfers = collect(); // Empty collection - no transfers table

            // Warehouse list with stats
            $warehouses = $warehouseModel::withCount(['stocks'])
                ->get()
                ->map(function($warehouse) {
                    $warehouse->total_quantity = $warehouse->stocks()->sum('quantity') ?? 0;
                    return $warehouse;
                });

            // Recent activities across all warehouses
            $recentActivities = StockMovement::with(['item:id,name,unit', 'warehouse:id,name', 'creator:id,name'])
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();

            return view('dashboard.super-admin', compact(
                'stats',
                'monthlyMovements',
                'topItems',
                'lowStockItems',
                'pendingTransfers',
                'warehouses',
                'recentActivities'
            ));
        } catch (\Exception $e) {
            Log::error('Super Admin Dashboard Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return view with empty data instead of throwing error
            return view('dashboard.super-admin', [
                'stats' => [
                    'total_units' => 0,
                    'total_warehouses' => 0,
                    'total_items' => 0,
                    'total_stock' => 0,
                    'pending_transfers' => 0,
                    'total_users' => 0,
                    'active_units' => 0,
                    'active_warehouses' => 0,
                    'today_total_stock_in' => 0,
                    'today_total_stock_out' => 0,
                    'today_transfers_approved' => 0,
                    'today_new_alerts' => 0,
                    'monthly_transfers_current' => 0,
                    'monthly_transfers_target' => 50,
                    'monthly_movements_current' => 0,
                    'monthly_movements_target' => 1000,
                ],
                'monthlyMovements' => collect(),
                'topItems' => collect(),
                'lowStockItems' => collect(),
                'pendingTransfers' => collect(),
                'warehouses' => collect(),
                'recentActivities' => collect(),
                'error' => 'Some data could not be loaded. Please try again later.'
            ]);
        }
    }

    private function adminGudangDashboard()
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        // Check if user has warehouses assigned
        if ($warehouseIds->isEmpty()) {
            return view('dashboard.admin-unit', [
                'warehouseName' => 'No Warehouse Assigned',
                'stats' => [
                    'total_items' => 0,
                    'total_stock' => 0,
                    'pending_submissions' => 0,
                    'incoming_transfers' => 0,
                ],
                'dailyMovements' => collect(),
                'recentSubmissions' => collect(),
                'lowStockItems' => collect(),
                'recentActivities' => collect()
            ]);
        }

        $warehouseName = $user->warehouses()->first()->name ?? 'No Warehouse';

        // === CONSOLIDATED STOCK STATS (1 query instead of 3) ===
        $stockStats = Stock::whereIn('warehouse_id', $warehouseIds)
            ->selectRaw("
                COUNT(DISTINCT item_id) as total_items,
                COALESCE(SUM(quantity), 0) as total_stock,
                SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as low_stock_items
            ")->first();

        // === CONSOLIDATED SUBMISSION STATS (1 query instead of 8) ===
        $submissionStats = Submission::whereIn('warehouse_id', $warehouseIds)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' AND is_draft = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN status = 'approved' AND DATE(updated_at) = CURDATE() THEN 1 ELSE 0 END) as today_approved,
                SUM(CASE WHEN status = 'pending' AND DATE(submitted_at) = CURDATE() THEN 1 ELSE 0 END) as today_pending,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as monthly_count
            ")->first();

        // === CONSOLIDATED MOVEMENT STATS (1 query instead of 7) ===
        $weekStart = now()->startOfWeek()->format('Y-m-d H:i:s');
        $weekEnd = now()->endOfWeek()->format('Y-m-d H:i:s');
        $lastWeekStart = now()->subWeek()->startOfWeek()->format('Y-m-d H:i:s');
        $lastWeekEnd = now()->subWeek()->endOfWeek()->format('Y-m-d H:i:s');

        $movementStats = StockMovement::whereIn('warehouse_id', $warehouseIds)
            ->selectRaw("
                SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as stock_in_count,
                SUM(CASE WHEN quantity < 0 THEN 1 ELSE 0 END) as stock_out_count,
                SUM(CASE WHEN DATE(created_at) = CURDATE() AND quantity > 0 THEN quantity ELSE 0 END) as today_stock_in,
                SUM(CASE WHEN DATE(created_at) = CURDATE() AND quantity < 0 THEN ABS(quantity) ELSE 0 END) as today_stock_out,
                SUM(CASE WHEN created_at BETWEEN ? AND ? AND quantity > 0 THEN quantity ELSE 0 END) as week_stock_in,
                SUM(CASE WHEN created_at BETWEEN ? AND ? AND quantity < 0 THEN ABS(quantity) ELSE 0 END) as week_stock_out,
                SUM(CASE WHEN created_at BETWEEN ? AND ? AND quantity > 0 THEN quantity ELSE 0 END) as last_week_stock_in,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN ABS(quantity) ELSE 0 END) as monthly_movements
            ", [$weekStart, $weekEnd, $weekStart, $weekEnd, $lastWeekStart, $lastWeekEnd])
            ->first();

        // Build stats array from consolidated results
        $totalApproved = $submissionStats->approved ?? 0;
        $totalRejected = $submissionStats->rejected ?? 0;
        $totalDecided = $totalApproved + $totalRejected;

        $stats = [
            'total_items' => $stockStats->total_items ?? 0,
            'total_stock' => $stockStats->total_stock ?? 0,
            'pending_submissions' => $submissionStats->pending ?? 0,
            'incoming_transfers' => 0,
            'low_stock_items' => $stockStats->low_stock_items ?? 0,
            'stock_in_count' => $movementStats->stock_in_count ?? 0,
            'stock_out_count' => $movementStats->stock_out_count ?? 0,
            'total_submissions' => $submissionStats->total ?? 0,
            'unread_notifications' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            'today_stock_in' => $movementStats->today_stock_in ?? 0,
            'today_stock_out' => $movementStats->today_stock_out ?? 0,
            'today_approved' => $submissionStats->today_approved ?? 0,
            'today_pending' => $submissionStats->today_pending ?? 0,
            'today_submissions' => $submissionStats->today_count ?? 0,
            'week_stock_in' => $movementStats->week_stock_in ?? 0,
            'week_stock_out' => $movementStats->week_stock_out ?? 0,
            'last_week_stock_in' => $movementStats->last_week_stock_in ?? 0,
            'total_approved' => $totalApproved,
            'total_rejected' => $totalRejected,
            'approval_rate' => $totalDecided > 0 ? round(($totalApproved / $totalDecided) * 100, 1) : 0,
            'monthly_submissions_current' => $submissionStats->monthly_count ?? 0,
            'monthly_submissions_target' => 100,
            'monthly_movements_current' => $movementStats->monthly_movements ?? 0,
            'monthly_movements_target' => 500,
        ];
        
        // Approval rate already calculated above

        // Optimized daily movements - only last 30 days
        $dailyMovements = StockMovement::select(
            DB::raw("DATE(created_at) as date"),
            DB::raw("SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as stock_in"),
            DB::raw("SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as stock_out")
        )
        ->whereIn('warehouse_id', $warehouseIds)
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Optimized recent submissions with eager loading - ONLY PENDING
        $recentSubmissions = Submission::whereIn('warehouse_id', $warehouseIds)
            ->where('status', 'pending')
            ->where('is_draft', 0)
            ->with(['item:id,name', 'staff:id,name'])
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get();

        // Out of stock items
        $lowStockItems = Item::join('stocks', 'items.id', '=', 'stocks.item_id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->whereIn('stocks.warehouse_id', $warehouseIds)
            ->where('stocks.quantity', '=', 0)
            ->select(
                'items.id as item_id',
                'items.name as item_name',
                'warehouses.name as warehouse_name',
                'stocks.quantity as current_stock'
            )
            ->limit(20)
            ->get();

        // Optimized recent activities with eager loading
        $recentActivities = StockMovement::whereIn('warehouse_id', $warehouseIds)
            ->with(['item:id,name,unit', 'creator:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top items in this warehouse
        $topItems = Stock::join('items', 'stocks.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->whereIn('stocks.warehouse_id', $warehouseIds)
            ->select(
                'items.name as item_name',
                'categories.name as category_name',
                'stocks.quantity as total_stock',
                'items.id as item_id'
            )
            ->orderBy('stocks.quantity', 'desc')
            ->limit(5)
            ->get();

        // Top submitting staff
        $topStaff = Submission::whereIn('warehouse_id', $warehouseIds)
            ->join('users', 'submissions.staff_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as total_submissions'),
                DB::raw('SUM(CASE WHEN submissions.status = "approved" THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN submissions.status = "rejected" THEN 1 ELSE 0 END) as rejected_count')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_submissions', 'desc')
            ->limit(5)
            ->get();

        // Critical items (out of stock)
        $criticalItems = Item::join('stocks', 'items.id', '=', 'stocks.item_id')
            ->whereIn('stocks.warehouse_id', $warehouseIds)
            ->where('stocks.quantity', '=', 0)
            ->select('items.id as item_id', 'items.name as item_name', 'stocks.quantity')
            ->limit(10)
            ->get();

        // Stock turnover - most active items this month
        $activeItems = StockMovement::whereIn('warehouse_id', $warehouseIds)
            ->whereMonth('stock_movements.created_at', date('m'))
            ->join('items', 'stock_movements.item_id', '=', 'items.id')
            ->select(
                'items.id',
                'items.name',
                DB::raw('SUM(ABS(quantity)) as total_movement')
            )
            ->groupBy('items.id', 'items.name')
            ->orderBy('total_movement', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.admin-unit', compact(
            'warehouseName',
            'stats',
            'dailyMovements',
            'recentSubmissions',
            'lowStockItems',
            'recentActivities',
            'topItems',
            'topStaff',
            'criticalItems',
            'activeItems'
        ));
    }

    private function staffGudangDashboard()
    {
        $user = auth()->user();

        // Consolidated submission stats (1 query instead of 5)
        $submissionStats = Submission::where('staff_id', $user->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' AND YEAR(updated_at) = ? AND MONTH(updated_at) = ? THEN 1 ELSE 0 END) as approved_month,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN is_draft = 1 THEN 1 ELSE 0 END) as drafts
            ", [date('Y'), date('m')])
            ->first();

        $stats = [
            'total_submissions' => $submissionStats->total ?? 0,
            'pending_approval' => $submissionStats->pending ?? 0,
            'approved_this_month' => $submissionStats->approved_month ?? 0,
            'rejected' => $submissionStats->rejected ?? 0,
        ];

        $draftCount = $submissionStats->drafts ?? 0;

        // Recent submissions with eager loading
        $recentSubmissions = Submission::where('staff_id', $user->id)
            ->with(['item:id,name', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Consolidated notification query (1 query instead of 2)
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $recentNotifications = $notifications;
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('dashboard.staff', compact(
            'stats',
            'draftCount',
            'recentSubmissions',
            'recentNotifications',
            'unreadNotifications'
        ));
    }
}