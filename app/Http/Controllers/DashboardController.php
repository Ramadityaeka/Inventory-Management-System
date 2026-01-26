<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Notification;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Submission;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // Default fallback - redirect to staff dashboard for users without specific role
            return $this->staffGudangDashboard();
        } catch (\Exception $e) {
            // Log the error and show a friendly message
            \Log::error('Dashboard error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return view('dashboard.staff', [
                'user' => auth()->user(),
                'error' => 'Dashboard temporarily unavailable: ' . $e->getMessage(),
                'stats' => [
                    'total_submissions' => 0,
                    'pending_approval' => 0,
                    'approved_this_month' => 0,
                    'rejected' => 0,
                ],
                'draftCount' => 0,
                'recentSubmissions' => collect(),
                'recentNotifications' => collect(),
                'unreadNotifications' => 0,
            ]);
        }
    }

    private function superAdminDashboard()
    {
        // Optimize queries with caching where possible
        // Support both Warehouse and Unit models
        $warehouseModel = class_exists('App\\Models\\Unit') ? Unit::class : Warehouse::class;
        
        $stats = [
            'total_units' => $warehouseModel::count(),
            'total_warehouses' => $warehouseModel::count(), // Alias untuk backward compatibility
            'total_items' => Item::count(),
            'total_stock' => Stock::sum('quantity') ?? 0,
            'pending_transfers' => Transfer::where('status', 'waiting_approval')->count(),
            'total_users' => \App\Models\User::count(),
            'active_units' => $warehouseModel::whereHas('stocks', function($q) {
                $q->where('quantity', '>', 0);
            })->count(),
            'active_warehouses' => $warehouseModel::whereHas('stocks', function($q) {
                $q->where('quantity', '>', 0);
            })->count(), // Alias untuk backward compatibility
            // Today's stats
            'today_total_stock_in' => StockMovement::whereDate('created_at', today())->where('quantity', '>', 0)->sum('quantity') ?? 0,
            'today_total_stock_out' => abs(StockMovement::whereDate('created_at', today())->where('quantity', '<', 0)->sum('quantity')) ?? 0,
            'today_transfers_approved' => Transfer::whereDate('approved_at', today())->count() ?? 0,
            'today_new_alerts' => Item::join('stocks', 'items.id', '=', 'stocks.item_id')
                ->where('stocks.quantity', '=', 0)
                ->distinct('items.id')
                ->count(),
            // Monthly progress targets (these could be configurable)
            'monthly_transfers_current' => Transfer::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
            'monthly_transfers_target' => 50,
            'monthly_movements_current' => StockMovement::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum(DB::raw('ABS(quantity)')),
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
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'items.name as item_name',
                'categories.name as category_name',
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

        // Pending transfers with relationships
        $pendingTransfers = Transfer::where('status', 'waiting_approval')
            ->with(['fromWarehouse:id,name', 'toWarehouse:id,name', 'item:id,name', 'requestedBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Warehouse list with stats
        $warehouses = Warehouse::withCount(['stocks'])
            ->with(['stocks' => function($q) {
                $q->select('warehouse_id', DB::raw('SUM(quantity) as total_quantity'))
                  ->groupBy('warehouse_id');
            }])
            ->get();

        // Recent activities across all warehouses
        $recentActivities = StockMovement::with(['item:id,name', 'warehouse:id,name', 'user:id,name'])
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

        // Optimized stats queries
        $stats = [
            'total_items' => Stock::whereIn('warehouse_id', $warehouseIds)
                ->distinct('item_id')
                ->count('item_id'),
            'total_stock' => Stock::whereIn('warehouse_id', $warehouseIds)
                ->sum('quantity') ?? 0,
            'pending_submissions' => Submission::whereIn('warehouse_id', $warehouseIds)
                ->where('status', 'pending')
                ->count(),
            'incoming_transfers' => Transfer::whereIn('to_warehouse_id', $warehouseIds)
                ->where('status', 'approved')
                ->count(),
            'low_stock_items' => Stock::whereIn('stocks.warehouse_id', $warehouseIds)
                ->where('stocks.quantity', '=', 0)
                ->count(),
            'stock_in_count' => StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->where('quantity', '>', 0)
                ->count(),
            'stock_out_count' => StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->where('quantity', '<', 0)
                ->count(),
            'total_submissions' => Submission::whereIn('warehouse_id', $warehouseIds)->count(),
            'unread_notifications' => auth()->user()->unreadNotifications()->count(),
            // Today's stats
            'today_stock_in' => StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->whereDate('created_at', today())
                ->where('quantity', '>', 0)
                ->sum('quantity') ?? 0,
            'today_stock_out' => abs(StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->whereDate('created_at', today())
                ->where('quantity', '<', 0)
                ->sum('quantity')) ?? 0,
            'today_approved' => Submission::whereIn('warehouse_id', $warehouseIds)
                ->where('status', 'approved')
                ->whereDate('updated_at', today())
                ->count() ?? 0,
            'today_pending' => Submission::whereIn('warehouse_id', $warehouseIds)
                ->whereDate('submitted_at', today())
                ->where('status', 'pending')
                ->count() ?? 0,
            'today_submissions' => Submission::whereIn('warehouse_id', $warehouseIds)
                ->whereDate('created_at', today())
                ->count() ?? 0,
            // Weekly comparison
            'week_stock_in' => StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('quantity', '>', 0)
                ->sum('quantity') ?? 0,
            'week_stock_out' => abs(StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('quantity', '<', 0)
                ->sum('quantity')) ?? 0,
            'last_week_stock_in' => StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
                ->where('quantity', '>', 0)
                ->sum('quantity') ?? 0,
            // Approval statistics
            'total_approved' => Submission::whereIn('warehouse_id', $warehouseIds)
                ->where('status', 'approved')
                ->count(),
            'total_rejected' => Submission::whereIn('warehouse_id', $warehouseIds)
                ->where('status', 'rejected')
                ->count(),
            'approval_rate' => 0,
            // Monthly progress targets
            'monthly_submissions_current' => Submission::whereIn('warehouse_id', $warehouseIds)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'monthly_submissions_target' => 100,
            'monthly_movements_current' => StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum(DB::raw('ABS(quantity)')),
            'monthly_movements_target' => 500,
        ];
        
        // Calculate approval rate
        $totalSubmissions = $stats['total_approved'] + $stats['total_rejected'];
        if ($totalSubmissions > 0) {
            $stats['approval_rate'] = round(($stats['total_approved'] / $totalSubmissions) * 100, 1);
        }

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
            ->with(['item:id,name', 'user:id,name'])
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
            ->whereMonth('stock_movements.created_at', now()->month)
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

        // Optimized stats queries
        $stats = [
            'total_submissions' => Submission::where('staff_id', $user->id)->count(),
            'pending_approval' => Submission::where('staff_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'approved_this_month' => Submission::where('staff_id', $user->id)
                ->where('status', 'approved')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count(),
            'rejected' => Submission::where('staff_id', $user->id)
                ->where('status', 'rejected')
                ->count(),
        ];

        // Draft count
        $draftCount = Submission::where('staff_id', $user->id)
            ->where('is_draft', true)
            ->count();

        // Recent submissions with eager loading
        $recentSubmissions = Submission::where('staff_id', $user->id)
            ->with(['item:id,name', 'warehouse:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent notifications
        $recentNotifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Unread notifications count
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