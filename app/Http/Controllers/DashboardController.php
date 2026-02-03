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

            // Log untuk debugging
            Log::info('Dashboard access', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name
            ]);

            if ($user->isSuperAdmin()) {
                Log::info('Redirecting to Super Admin Dashboard');
                return $this->superAdminDashboard();
            } elseif ($user->isAdminGudang()) {
                Log::info('Redirecting to Admin Gudang Dashboard');
                return $this->adminGudangDashboard();
            } elseif ($user->isStaffGudang()) {
                Log::info('Redirecting to Staff Gudang Dashboard');
                return $this->staffGudangDashboard();
            }

            // Default fallback - abort with 403 for unknown roles
            Log::error('Unknown role accessing dashboard', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);
            abort(403, 'Unauthorized: Invalid user role');
            
        } catch (\Exception $e) {
            // Log the error and show a friendly message
            Log::error('Dashboard error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Redirect back to login with error message
            auth()->logout();
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat mengakses dashboard. Silakan login kembali.');
        }
    }

    private function superAdminDashboard()
    {
        try {
            // Use Warehouse model (units table doesn't exist)
            $warehouseModel = Warehouse::class;
            
            $stats = [
                'total_units' => $warehouseModel::count(),
                'total_warehouses' => $warehouseModel::count(), // Alias untuk backward compatibility
                'total_items' => Item::count(),
                'total_stock' => Stock::sum('quantity') ?? 0,
                'pending_transfers' => 0, // Feature disabled - no transfers table
                'total_users' => User::count(),
                'active_units' => $warehouseModel::whereHas('stocks', function($q) {
                    $q->where('quantity', '>', 0);
                })->count(),
                'active_warehouses' => $warehouseModel::whereHas('stocks', function($q) {
                    $q->where('quantity', '>', 0);
                })->count(), // Alias untuk backward compatibility
                // Today's stats
                'today_total_stock_in' => StockMovement::whereDate('created_at', today())->where('quantity', '>', 0)->sum('quantity') ?? 0,
                'today_total_stock_out' => abs(StockMovement::whereDate('created_at', today())->where('quantity', '<', 0)->sum('quantity')) ?? 0,
                'today_transfers_approved' => 0, // Feature disabled - no transfers table
                'today_new_alerts' => Item::join('stocks', 'items.id', '=', 'stocks.item_id')
                    ->where('stocks.quantity', '=', 0)
                    ->distinct('items.id')
                    ->count() ?? 0,
                // Monthly progress targets (these could be configurable)
                'monthly_transfers_current' => 0, // Feature disabled - no transfers table
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
            'incoming_transfers' => 0, // Feature disabled - no transfers table
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
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->count(),
            'monthly_submissions_target' => 100,
            'monthly_movements_current' => StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
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

        // Optimized stats queries
        $stats = [
            'total_submissions' => Submission::where('staff_id', $user->id)->count(),
            'pending_approval' => Submission::where('staff_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'approved_this_month' => Submission::where('staff_id', $user->id)
                ->where('status', 'approved')
                ->whereMonth('updated_at', date('m'))
                ->whereYear('updated_at', date('Y'))
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