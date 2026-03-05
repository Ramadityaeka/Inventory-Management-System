<?php

use App\Http\Controllers\AdminUnit\AlertController;
use App\Http\Controllers\AdminUnit\GudangReportController;
use App\Http\Controllers\AdminUnit\MonthlyReportController;
use App\Http\Controllers\AdminUnit\PublicRequestManagementController;
use App\Http\Controllers\AdminUnit\StockController;
use App\Http\Controllers\AdminUnit\StockRequestManagementController;
use App\Http\Controllers\AdminUnit\SubmissionController;
use App\Http\Controllers\AdminUnit\UserSignatureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Public\PublicRequestController;
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

// =========================================
// ROUTE PUBLIK - TIDAK PERLU LOGIN
// =========================================

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Form dan proses pengajuan publik
Route::prefix('request')->name('public.request.')->group(function () {
    Route::get('/create', [PublicRequestController::class, 'create'])->name('create');
    Route::post('/store', [PublicRequestController::class, 'store'])
         ->name('store')
         ->middleware('throttle:10,1');
    Route::get('/success', [PublicRequestController::class, 'success'])->name('success');
    Route::get('/status', [PublicRequestController::class, 'checkStatus'])->name('status');
    Route::post('/status', [PublicRequestController::class, 'findStatus'])->name('find-status');
    Route::get('/{token}/document', [PublicRequestController::class, 'document'])->name('document');
    Route::get('/{token}/pdf', [PublicRequestController::class, 'exportPdf'])->name('pdf');
});

// AJAX endpoint publik
Route::get('/api/unit/{id}/stocks', [PublicRequestController::class, 'getStocks'])->name('api.unit.stocks');
Route::get('/api/unit/{id}/pics', [PublicRequestController::class, 'getPics'])->name('api.unit.pics');

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
        Route::get('categories/api/check-code', [CategoryController::class, 'checkCode'])->name('categories.check-code');
        
        Route::resource('suppliers', SupplierController::class);
        // Item API endpoints (harus sebelum resource route)
        Route::get('items/api/generate-code', [ItemController::class, 'generateCode'])->name('items.generate-code');
        Route::resource('items', ItemController::class);
        Route::post('items/{item}/set-threshold', [ItemController::class, 'setThreshold']);
        // Item Unit routes
        Route::post('items/{item}/units', [ItemController::class, 'storeUnit'])->name('items.units.store');
        Route::put('items/{item}/units/{itemUnit}', [ItemController::class, 'updateUnit'])->name('items.units.update');
        Route::delete('items/{item}/units/{itemUnit}', [ItemController::class, 'destroyUnit'])->name('items.units.destroy');

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
        
        // Stock Summary Report Routes
        Route::get('reports/stock-summary', [\App\Http\Controllers\SuperAdmin\StockSummaryReportController::class, 'index'])->name('reports.stock-summary');
        Route::get('reports/stock-summary/pdf', [\App\Http\Controllers\SuperAdmin\StockSummaryReportController::class, 'exportPdf'])->name('reports.stock-summary.pdf');
        Route::get('reports/stock-summary/excel', [\App\Http\Controllers\SuperAdmin\StockSummaryReportController::class, 'exportExcel'])->name('reports.stock-summary.excel');
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
        Route::post('/stocks/{item}/adjust', [StockController::class, 'adjust'])->name('stocks.adjust-item');
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
        Route::get('/reports/stock-summary', [GudangReportController::class, 'stockSummary'])->name('reports.stock-summary');
        Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])->name('reports.monthly');
        Route::post('/reports/monthly/generate', [MonthlyReportController::class, 'generate'])->name('reports.monthly.generate');
        Route::get('/reports/monthly/pdf', [MonthlyReportController::class, 'exportPdf'])->name('reports.monthly.pdf');
        Route::get('/reports/transactions/export', [GudangReportController::class, 'exportTransactions'])->name('reports.transactions.export');
        Route::get('/reports/stock-values/export', [GudangReportController::class, 'exportStockValues'])->name('reports.stock-values.export');
        Route::get('/reports/stock-summary/excel', [GudangReportController::class, 'exportStockSummaryExcel'])->name('reports.stock-summary.excel');
        Route::get('/reports/stock-summary/pdf', [GudangReportController::class, 'exportStockSummaryPdf'])->name('reports.stock-summary.pdf');
        Route::post('/reports/transactions/export-pdf', [GudangReportController::class, 'exportTransactionsPdf'])->name('reports.transactions.exportPdf');
        Route::post('/reports/stock-values/export-pdf', [GudangReportController::class, 'exportStockValuesPdf'])->name('reports.stock-values.exportPdf');

        // Manajemen Permintaan Publik
        Route::prefix('public-requests')->name('public-requests.')->group(function () {
            Route::get('/', [PublicRequestManagementController::class, 'index'])->name('index');
            Route::get('/{id}', [PublicRequestManagementController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [PublicRequestManagementController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [PublicRequestManagementController::class, 'reject'])->name('reject');
            Route::get('/{id}/sign', [PublicRequestManagementController::class, 'showSign'])->name('sign');
            Route::post('/{id}/sign', [PublicRequestManagementController::class, 'saveSign'])->name('save-sign');
        });

        // Manajemen Tanda Tangan PIC
        Route::prefix('signature')->name('signature.')->group(function () {
            Route::get('/', [UserSignatureController::class, 'show'])->name('show');
            Route::post('/save', [UserSignatureController::class, 'save'])->name('save');
            Route::delete('/', [UserSignatureController::class, 'destroy'])->name('destroy');
        });
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
        Route::get('/api/item-units', [ReceiveItemController::class, 'getItemUnits'])->name('api.item-units');
        
        // Legacy route for backward compatibility
        Route::get('/search-items', [ReceiveItemController::class, 'search'])->name('search-items');
        
        // Stock Requests
        Route::get('/stock-requests', [StockRequestController::class, 'index'])->name('stock-requests.index');
        Route::get('/stock-requests/my-requests', [StockRequestController::class, 'myRequests'])->name('stock-requests.my-requests');
        Route::get('/stock-requests/create', [StockRequestController::class, 'create'])->name('stock-requests.create');
        Route::post('/stock-requests', [StockRequestController::class, 'store'])->name('stock-requests.store');
        Route::get('/stock-requests/{stockRequest}', [StockRequestController::class, 'show'])->name('stock-requests.show');
        Route::delete('/stock-requests/{stockRequest}', [StockRequestController::class, 'destroy'])->name('stock-requests.destroy');
        Route::post('/stock-requests/{stockRequest}/upload-proof', [StockRequestController::class, 'uploadProof'])->name('stock-requests.upload-proof');
    });
});

