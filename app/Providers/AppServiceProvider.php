<?php

namespace App\Providers;

use App\Events\LowStockDetected;
use App\Events\StockRequestCreated;
use App\Events\SubmissionCreated;
use App\Events\UserPasswordReset;
use App\Events\UserRoleChanged;
use App\Events\UserStatusChanged;
use App\Events\UserWarehouseAssigned;
use App\Listeners\NotifyAdminsLowStock;
use App\Listeners\NotifyAdminsNewStockRequest;
use App\Listeners\NotifyAdminsNewSubmission;
use App\Listeners\NotifyUserPasswordReset;
use App\Listeners\NotifyUserRoleChange;
use App\Listeners\NotifyUserStatusChange;
use App\Listeners\NotifyUserWarehouseAssignment;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set Carbon locale to Indonesian
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian');
        
        // Register Blade directives for date formatting
        \Blade::directive('dateIndo', function ($expression) {
            return "<?php echo formatDateIndo($expression); ?>";
        });
        
        \Blade::directive('dateIndoLong', function ($expression) {
            return "<?php echo formatDateIndoLong($expression); ?>";
        });
        
        // Register observers
        \App\Models\Stock::observe(\App\Observers\StockObserver::class);
        
        // Register event listeners untuk user management notifications
        Event::listen(UserWarehouseAssigned::class, NotifyUserWarehouseAssignment::class);
        Event::listen(UserRoleChanged::class, NotifyUserRoleChange::class);
        Event::listen(UserPasswordReset::class, NotifyUserPasswordReset::class);
        Event::listen(UserStatusChanged::class, NotifyUserStatusChange::class);
        
        // Register event listeners untuk admin gudang notifications
        Event::listen(SubmissionCreated::class, NotifyAdminsNewSubmission::class);
        Event::listen(StockRequestCreated::class, NotifyAdminsNewStockRequest::class);
        Event::listen(LowStockDetected::class, NotifyAdminsLowStock::class);
    }
}
