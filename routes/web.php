<?php

use App\Http\Controllers\AdminUnit\AlertController;
use App\Http\Controllers\AdminUnit\StockController;
use App\Http\Controllers\AdminUnit\StockRequestManagementController;
use App\Http\Controllers\AdminUnit\SubmissionController;
use App\Http\Controllers\AdminUnit\GudangReportController;
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
            'should_redirect_to' => $user->isAdminGudang() ? 'admin-unit.blade.php' : ($user->isSuperAdmin() ? 'super-admin.blade.php' : 'staff.blade.php')
        ]);
    });
    
    // Notifications (all roles)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::match(['get', 'post'], '/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/delete-all-read', [NotificationController::class, 'deleteAllRead'])->name('notifications.delete-all-read');
    Route::post('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');

    // Super Admin Routes
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        // Master Data
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('categories', CategoryController::class);
        // Category API endpoints for AJAX
        Route::get('categories/api/search', [CategoryController::class, 'search'])->name('categories.search');
        Route::get('categories/api/generate-code', [CategoryController::class, 'generateCode'])->name('categories.generate-code');
        
        Route::resource('suppliers', SupplierController::class);
        Route::resource('items', ItemController::class);
        Route::post('items/{item}/set-threshold', [ItemController::class, 'setThreshold']);

        // Reports
        Route::post('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
        Route::post('reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        
        // Transaction Report Routes
        Route::get('reports/transactions', [\App\Http\Controllers\SuperAdmin\TransactionReportController::class, 'index'])->name('reports.transactions');
        Route::get('reports/transactions/pdf', [\App\Http\Controllers\SuperAdmin\TransactionReportController::class, 'exportPdf'])->name('reports.transactions.pdf');
        Route::get('reports/transactions/excel', [\App\Http\Controllers\SuperAdmin\TransactionReportController::class, 'exportExcel'])->name('reports.transactions.excel');
        Route::get('reports/transactions/search-items', [\App\Http\Controllers\SuperAdmin\TransactionReportController::class, 'searchItems'])->name('reports.transactions.search-items');
        
        // Stock Value Report Routes
        Route::get('reports/stock-values', [\App\Http\Controllers\SuperAdmin\StockValueReportController::class, 'index'])->name('reports.stock-values');
        Route::get('reports/stock-values/pdf', [\App\Http\Controllers\SuperAdmin\StockValueReportController::class, 'exportPdf'])->name('reports.stock-values.pdf');
        Route::get('reports/stock-values/excel', [\App\Http\Controllers\SuperAdmin\StockValueReportController::class, 'exportExcel'])->name('reports.stock-values.excel');
        Route::get('reports/stock-values/search-items', [\App\Http\Controllers\SuperAdmin\StockValueReportController::class, 'searchItems'])->name('reports.stock-values.search-items');
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
        
        // Gudang Reports (Transactions & Stock Values) - Same as Super Admin
        Route::get('/reports', [GudangReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/transactions', [GudangReportController::class, 'transactions'])->name('reports.transactions');
        Route::get('/reports/stock-values', [GudangReportController::class, 'stockValues'])->name('reports.stock-values');
        Route::get('/reports/transactions/export', [GudangReportController::class, 'exportTransactions'])->name('reports.transactions.export');
        Route::get('/reports/stock-values/export', [GudangReportController::class, 'exportStockValues'])->name('reports.stock-values.export');
        Route::post('/reports/transactions/export-pdf', [GudangReportController::class, 'exportTransactionsPdf'])->name('reports.transactions.exportPdf');
        Route::post('/reports/stock-values/export-pdf', [GudangReportController::class, 'exportStockValuesPdf'])->name('reports.stock-values.exportPdf');
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
        
        // API endpoints
        Route::get('/api/search-items', [ReceiveItemController::class, 'search'])->name('api.search-items');
        Route::get('/api/search-categories', [ReceiveItemController::class, 'searchCategories'])->name('api.search-categories');
        Route::get('/api/generate-item-code', [ReceiveItemController::class, 'generateItemCode'])->name('api.generate-item-code');
        
        // Legacy route for backward compatibility
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

