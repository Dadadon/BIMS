<?php

namespace App\Providers;

use App\Models\HR\Employee;
use App\Models\Payroll\PayrollSlip;
use App\Models\Sales\Sale;
use App\Models\Tasks\Task;
use App\Policies\EmployeePolicy;
use App\Policies\PayrollPolicy;
use App\Policies\SalePolicy;
use App\Policies\TaskPolicy;
use App\Models\Setting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Module;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Register Policies
        Gate::policy(Employee::class,    EmployeePolicy::class);
        Gate::policy(Sale::class,        SalePolicy::class);
        Gate::policy(PayrollSlip::class, PayrollPolicy::class);
        Gate::policy(Task::class,        TaskPolicy::class);

        // System Admins bypass all gates
        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) return true;
        });

        // @module('sales') Blade directive — hides UI for disabled modules
        Blade::if('module', function (string $key) {
            return Module::isEnabled($key);
        });

        // @permission('sales', 'edit') Blade directive
        Blade::if('permission', function (string $module, string $action) {
            return auth()->check() && auth()->user()->hasPermission($module, $action);
        });

        // @admin Blade directive
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        // Share $settings with every view that uses the main layout
        View::composer(['layouts.app', 'layouts.partials.sidebar-content', 'layouts.auth'], function ($view) {
            try {
                $view->with('settings', Setting::current());
            } catch (\Exception) {
                // Settings row may not exist during migrations/setup
                $view->with('settings', null);
            }
        });
    }
}
