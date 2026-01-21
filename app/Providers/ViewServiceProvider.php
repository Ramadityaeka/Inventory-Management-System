<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\StockAlert;
use App\Models\StockRequest;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share data with layouts.app
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                $view->with('unreadNotifications', $user->notifications()->unread()->count());
                $view->with('notifications', $user->notifications()->latest()->take(5)->get());
            }
        });

        // Share data with admin_gudang layouts
        View::composer('layouts.admin_gudang', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                if ($user->isAdminGudang()) {
                    // Count pending submissions in user's warehouses
                    $pendingSubmissions = Submission::where('status', Submission::STATUS_PENDING)
                        ->whereHas('warehouse.users', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->count();

                    // Count unread low stock alerts in user's warehouses
                    $lowStockAlerts = StockAlert::where('alert_type', StockAlert::ALERT_TYPE_LOW_STOCK)
                        ->where('is_read', false)
                        ->whereHas('warehouse.users', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->count();

                    // Count pending stock requests in user's warehouses
                    $pendingStockRequests = StockRequest::where('status', StockRequest::STATUS_PENDING)
                        ->whereHas('warehouse.users', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->count();

                    $view->with('pendingSubmissions', $pendingSubmissions);
                    $view->with('lowStockAlerts', $lowStockAlerts);
                    $view->with('pendingStockRequests', $pendingStockRequests);
                }
            }
        });

        // Share data with staff_gudang layouts
        View::composer('layouts.staff_gudang', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                if ($user->isStaffGudang()) {
                    $draftCount = Submission::where('staff_id', $user->id)
                        ->where('is_draft', true)
                        ->count();

                    $view->with('draftCount', $draftCount);
                }
            }
        });
    }
}