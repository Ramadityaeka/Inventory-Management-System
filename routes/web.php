<?php

use App\Http\Controllers\AdminGudang\AlertController;
use App\Http\Controllers\AdminGudang\MonthlyReportController;
use App\Http\Controllers\AdminGudang\StockController;
use App\Http\Controllers\AdminGudang\StockRequestManagementController;
use App\Http\Controllers\AdminGudang\SubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Staff\DraftController;
use App\Http\Controllers\Staff\ReceiveItemController;
use App\Http\Controllers\Staff\StockRequestController;
use App\Http\Controllers\SuperAdmin\CategoryController;
use App\Http\Controllers\SuperAdmin\ItemController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\SupplierController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Test route untuk debug dashboard
    Route::get('/test-dashboard', function() {
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isAdminGudang' => $user->isAdminGudang(),
            'isStaffGudang' => $user->isStaffGudang(),
            'warehouses' => $user->warehouses->pluck('name', 'id'),
            'should_redirect_to' => $user->isAdminGudang() ? 'admin-gudang.blade.php' : ($user->isSuperAdmin() ? 'super-admin.blade.php' : 'staff.blade.php')
        ]);
    });
    
    // Notifications (all roles)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::match(['get', 'post'], '/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Super Admin Routes
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        // Master Data
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('items', ItemController::class);
        Route::post('items/{item}/set-threshold', [ItemController::class, 'setThreshold']);

        // Reports
        Route::get('reports/stock-overview', [ReportController::class, 'stockOverview'])->name('reports.stock-overview');
        Route::get('reports/stock-overview/export-excel', [ReportController::class, 'exportStockOverviewExcel'])->name('reports.stock-overview.export-excel');
        Route::get('reports/stock-overview/export-pdf', [ReportController::class, 'exportStockOverviewPdf'])->name('reports.stock-overview.export-pdf');
        Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
        Route::post('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
        Route::post('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    });

    // Admin Gudang Routes
    Route::middleware('role:admin_gudang')->prefix('gudang')->name('gudang.')->group(function () {
        // Submissions
        Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/statistics', [SubmissionController::class, 'statistics'])->name('submissions.statistics');
        Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::post('/submissions/{submission}/approve', [SubmissionController::class, 'approve'])->name('submissions.approve');
        Route::post('/submissions/{submission}/reject', [SubmissionController::class, 'reject'])->name('submissions.reject');
        
        // Stock Requests Management
        Route::get('/stock-requests', [StockRequestManagementController::class, 'index'])->name('stock-requests.index');
        Route::get('/stock-requests/{stockRequest}', [StockRequestManagementController::class, 'show'])->name('stock-requests.show');
        Route::post('/stock-requests/{stockRequest}/approve', [StockRequestManagementController::class, 'approve'])->name('stock-requests.approve');
        Route::post('/stock-requests/{stockRequest}/reject', [StockRequestManagementController::class, 'reject'])->name('stock-requests.reject');
        
        // Stocks
        Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/create', [StockController::class, 'create'])->name('stocks.create');
        Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
        Route::get('/stocks/history/{item}', [StockController::class, 'history'])->name('stocks.history');
        Route::post('/stocks/adjust', [StockController::class, 'adjustment'])->name('stocks.adjust');
        Route::get('/stocks/movement', [StockController::class, 'movement'])->name('stock.movement');
        
        // Transfers
        Route::get('/transfers/create', [StockRequestManagementController::class, 'create'])->name('transfers.create');
        
        // Alerts & Notifications
        Route::get('/alerts', [AlertController::class, 'index'])->name('alerts');
        Route::post('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.markAsRead');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        
        // Reports
        Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])->name('reports.monthly');
        Route::post('/reports/monthly/generate', [MonthlyReportController::class, 'generate'])->name('reports.monthly.generate');
        Route::post('/reports/monthly/export-pdf', [MonthlyReportController::class, 'exportPdf'])->name('reports.monthly.exportPdf');
    });

    // Staff Gudang Routes
    Route::middleware('role:staff_gudang')->prefix('staff')->name('staff.')->group(function () {
        Route::resource('/receive-items', ReceiveItemController::class);
        Route::post('/receive-items/{submission}/upload-photo', [ReceiveItemController::class, 'uploadPhoto'])->name('receive-items.upload-photo');
        Route::delete('/receive-items/{submission}/delete-photo/{photo}', [ReceiveItemController::class, 'deletePhoto'])->name('receive-items.delete-photo');
        Route::post('/receive-items/{submission}/submit', [ReceiveItemController::class, 'submit'])->name('receive-items.submit');
        Route::get('/drafts', [DraftController::class, 'index'])->name('drafts');
        Route::get('/drafts/{submission}/edit', [DraftController::class, 'edit'])->name('drafts.edit');
        Route::delete('/drafts/{submission}', [DraftController::class, 'destroy'])->name('drafts.destroy');
        Route::get('/search-items', [ReceiveItemController::class, 'search'])->name('search-items');
        
        // Stock Requests
        Route::get('/stock-requests', [StockRequestController::class, 'index'])->name('stock-requests.index');
        Route::get('/stock-requests/my-requests', [StockRequestController::class, 'myRequests'])->name('stock-requests.my-requests');
        Route::get('/stock-requests/create', [StockRequestController::class, 'create'])->name('stock-requests.create');
        Route::post('/stock-requests', [StockRequestController::class, 'store'])->name('stock-requests.store');
        Route::get('/stock-requests/{stockRequest}', [StockRequestController::class, 'show'])->name('stock-requests.show');
        Route::delete('/stock-requests/{stockRequest}', [StockRequestController::class, 'destroy'])->name('stock-requests.destroy');
    });
});

