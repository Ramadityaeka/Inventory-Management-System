<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\StockRequest;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share notification + navigation badge data with layouts.app
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $userId = $user->id;

                // Direct queries (lightweight COUNT/LIMIT queries)
                $view->with('unreadNotifications', Notification::where('user_id', $userId)->where('is_read', false)->count());
                $view->with('notifications', Notification::where('user_id', $userId)->latest()->take(5)->get());

                // Navigation badge counts for Admin Gudang
                if ($user->isAdminGudang()) {
                    $warehouseIds = $user->warehouses()->pluck('warehouses.id');

                    $view->with('pendingSubmissions', Submission::whereIn('warehouse_id', $warehouseIds)
                        ->where('status', 'pending')->where('is_draft', false)->count());

                    $view->with('pendingStockRequests', StockRequest::whereIn('warehouse_id', $warehouseIds)
                        ->where('status', 'pending')->count());
                }

                // Draft count for Staff Gudang
                if ($user->isStaffGudang()) {
                    $view->with('draftCount', Submission::where('staff_id', $userId)->where('is_draft', true)->count());
                }
            }
        });
    }
}