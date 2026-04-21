<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\FieldsController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\PayrollAdjustmentController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\ClockController;
use App\Http\Controllers\Personal\PersonalAttendanceController;
use App\Http\Controllers\Personal\PersonalDashboardController;
use App\Http\Controllers\Personal\PersonalLeaveController;
use App\Http\Controllers\Personal\PersonalPayrollController;
use App\Http\Controllers\Personal\PersonalPerformanceController;
use App\Http\Controllers\Personal\PersonalSaleController;
use App\Http\Controllers\Personal\PersonalScheduleController;
use App\Http\Controllers\Personal\PersonalTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Auth routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::get('forgot-password', [\App\Http\Controllers\Auth\AuthController::class, 'showForgot'])->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\Auth\AuthController::class, 'sendReset'])->name('password.email');
    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\AuthController::class, 'showReset'])->name('password.reset');
    Route::post('reset-password', [\App\Http\Controllers\Auth\AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| SmartClock (auth required, no admin check)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('clock', [ClockController::class, 'show'])->name('clock');
    Route::post('clock/in',   [ClockController::class, 'clockIn'])->name('clock.in');
    Route::post('clock/out',  [ClockController::class, 'clockOut'])->name('clock.out');
    Route::post('clock/sale', [ClockController::class, 'logSale'])->name('clock.sale');
});

/*
|--------------------------------------------------------------------------
| Personal (Employee) Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('my')->name('my.')->group(function () {
    Route::get('dashboard', [PersonalDashboardController::class, 'index'])->name('dashboard');
    Route::get('attendance', [PersonalAttendanceController::class, 'index'])->name('attendance');
    Route::get('schedule',   [PersonalScheduleController::class,  'index'])->name('schedule');

    Route::middleware('module:leaves')->group(function () {
        Route::get('leaves', [PersonalLeaveController::class, 'index'])->name('leaves');
        Route::post('leaves', [PersonalLeaveController::class, 'store'])->name('leaves.store');
        Route::delete('leaves/{leaveRequest}', [PersonalLeaveController::class, 'cancel'])->name('leaves.cancel');
    });

    Route::middleware('module:sales')->group(function () {
        Route::get('sales', [PersonalSaleController::class, 'index'])->name('sales');
    });

    Route::middleware('module:payroll')->group(function () {
        Route::get('payroll', [PersonalPayrollController::class, 'index'])->name('payroll');
        Route::get('payroll/{slip}/download', [PersonalPayrollController::class, 'download'])->name('payroll.download');
    });

    Route::middleware('module:performance')->group(function () {
        Route::get('performance', [PersonalPerformanceController::class, 'index'])->name('performance');
    });

    Route::middleware('module:tasks')->group(function () {
        Route::get('tasks', [PersonalTaskController::class, 'index'])->name('tasks');
        Route::patch('tasks/{task}/status', [PersonalTaskController::class, 'updateStatus'])->name('tasks.status');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',              [NotificationController::class, 'index'])->name('index');
        Route::get('recent',         [NotificationController::class, 'recent'])->name('recent');
        Route::post('mark-all-read', [NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::post('{id}/read',     [NotificationController::class, 'markRead'])->name('markRead');
        Route::delete('{id}',        [NotificationController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:system_admin,manager,team_lead_l2,team_lead_l1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*-- HR --*/
    Route::middleware('module:hr')->prefix('teams')->name('teams.')->group(function () {
        Route::get('/',            [TeamController::class, 'index'])->name('index');
        Route::post('/',           [TeamController::class, 'store'])->name('store');
        Route::put('{team}',       [TeamController::class, 'update'])->name('update');
        Route::delete('{team}',    [TeamController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('module:hr')->prefix('employees')->name('employees.')->group(function () {
        Route::get('/',               [EmployeeController::class, 'index'])->name('index');
        Route::get('create',          [EmployeeController::class, 'create'])->name('create');
        Route::post('/',              [EmployeeController::class, 'store'])->name('store');
        Route::get('{employee}',      [EmployeeController::class, 'show'])->name('show');
        Route::get('{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('{employee}',      [EmployeeController::class, 'update'])->name('update');
        Route::delete('{employee}',   [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('{employee}/archive', [EmployeeController::class, 'archive'])->name('archive');
        Route::post('{employee}/kpis/{kpi}',   [EmployeeController::class, 'assignKpi'])->name('kpis.assign');
        Route::delete('{employee}/kpis/{kpi}', [EmployeeController::class, 'unassignKpi'])->name('kpis.unassign');
    });

    /* Field management (companies, departments, job titles, leave types, sale types) */
    Route::middleware('module:hr')->prefix('fields')->name('fields.')->group(function () {

        // Companies
        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/',               [FieldsController::class, 'companiesIndex'])->name('index');
            Route::get('create',          [FieldsController::class, 'companiesCreate'])->name('create');
            Route::post('/',              [FieldsController::class, 'companiesStore'])->name('store');
            Route::get('{company}/edit',  [FieldsController::class, 'companiesEdit'])->name('edit');
            Route::put('{company}',       [FieldsController::class, 'companiesUpdate'])->name('update');
            Route::delete('{company}',    [FieldsController::class, 'companiesDestroy'])->name('destroy');
        });

        // Departments
        Route::prefix('departments')->name('departments.')->group(function () {
            Route::get('/',                        [FieldsController::class, 'departmentsIndex'])->name('index');
            Route::post('/',                       [FieldsController::class, 'departmentsStore'])->name('store');
            Route::get('{department}/edit',        [FieldsController::class, 'departmentsEdit'])->name('edit');
            Route::put('{department}',             [FieldsController::class, 'departmentsUpdate'])->name('update');
            Route::delete('{department}',          [FieldsController::class, 'departmentsDestroy'])->name('destroy');
        });

        // Job Titles
        Route::prefix('job-titles')->name('job-titles.')->group(function () {
            Route::get('/',                  [FieldsController::class, 'jobTitlesIndex'])->name('index');
            Route::post('/',                 [FieldsController::class, 'jobTitlesStore'])->name('store');
            Route::get('{jobTitle}/edit',    [FieldsController::class, 'jobTitlesEdit'])->name('edit');
            Route::put('{jobTitle}',         [FieldsController::class, 'jobTitlesUpdate'])->name('update');
            Route::delete('{jobTitle}',      [FieldsController::class, 'jobTitlesDestroy'])->name('destroy');
        });

        // Leave Types
        Route::prefix('leave-types')->name('leave-types.')->group(function () {
            Route::get('/',                   [FieldsController::class, 'leaveTypesIndex'])->name('index');
            Route::post('/',                  [FieldsController::class, 'leaveTypesStore'])->name('store');
            Route::get('{leaveType}/edit',    [FieldsController::class, 'leaveTypesEdit'])->name('edit');
            Route::put('{leaveType}',         [FieldsController::class, 'leaveTypesUpdate'])->name('update');
            Route::delete('{leaveType}',      [FieldsController::class, 'leaveTypesDestroy'])->name('destroy');
        });

        // Leave Groups
        Route::prefix('leave-groups')->name('leave-groups.')->group(function () {
            Route::get('/',                    [FieldsController::class, 'leaveGroupsIndex'])->name('index');
            Route::post('/',                   [FieldsController::class, 'leaveGroupsStore'])->name('store');
            Route::get('{leaveGroup}/edit',    [FieldsController::class, 'leaveGroupsEdit'])->name('edit');
            Route::put('{leaveGroup}',         [FieldsController::class, 'leaveGroupsUpdate'])->name('update');
            Route::delete('{leaveGroup}',      [FieldsController::class, 'leaveGroupsDestroy'])->name('destroy');
        });

        // Sale Types (module:sales gating handled at nav level; routes live here under fields)
        Route::prefix('sale-types')->name('sale-types.')->group(function () {
            Route::get('/',               [FieldsController::class, 'saleTypesIndex'])->name('index');
            Route::get('create',          [FieldsController::class, 'saleTypesCreate'])->name('create');
            Route::post('/',              [FieldsController::class, 'saleTypesStore'])->name('store');
            Route::get('{saleType}/edit', [FieldsController::class, 'saleTypesEdit'])->name('edit');
            Route::put('{saleType}',      [FieldsController::class, 'saleTypesUpdate'])->name('update');
            Route::delete('{saleType}',   [FieldsController::class, 'saleTypesDestroy'])->name('destroy');
        });

        Route::prefix('sale-fields')->name('sale-fields.')->group(function () {
            Route::get('/',                    [FieldsController::class, 'saleFieldsIndex'])->name('index');
            Route::post('/',                   [FieldsController::class, 'saleFieldsStore'])->name('store');
            Route::put('{saleField}',          [FieldsController::class, 'saleFieldsUpdate'])->name('update');
            Route::delete('{saleField}',       [FieldsController::class, 'saleFieldsDestroy'])->name('destroy');
        });

        Route::prefix('employee-fields')->name('employee-fields.')->group(function () {
            Route::get('/',                        [FieldsController::class, 'employeeFieldsIndex'])->name('index');
            Route::post('/',                       [FieldsController::class, 'employeeFieldsStore'])->name('store');
            Route::put('{employeeField}',          [FieldsController::class, 'employeeFieldsUpdate'])->name('update');
            Route::delete('{employeeField}',       [FieldsController::class, 'employeeFieldsDestroy'])->name('destroy');
        });
    });

    /*-- Reports --*/
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',                  [ReportController::class, 'index'])->name('index');
        Route::get('create',             [ReportController::class, 'create'])->name('create');
        Route::post('/',                 [ReportController::class, 'store'])->name('store');
        Route::get('{report}',           [ReportController::class, 'show'])->name('show');
        Route::get('{report}/edit',      [ReportController::class, 'edit'])->name('edit');
        Route::put('{report}',           [ReportController::class, 'update'])->name('update');
        Route::delete('{report}',        [ReportController::class, 'destroy'])->name('destroy');
        Route::get('{report}/export',    [ReportController::class, 'export'])->name('export');
    });

    /*-- Notifications --*/
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                    [NotificationController::class, 'index'])->name('index');
        Route::get('recent',               [NotificationController::class, 'recent'])->name('recent');
        Route::post('mark-all-read',       [NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::post('{id}/read',           [NotificationController::class, 'markRead'])->name('markRead');
        Route::delete('{id}',              [NotificationController::class, 'destroy'])->name('destroy');
    });

    /*-- Schedules --*/
    Route::middleware('module:hr')->prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/',                         [ScheduleController::class, 'index'])->name('index');
        Route::post('/',                        [ScheduleController::class, 'store'])->name('store');
        Route::delete('{schedule}',             [ScheduleController::class, 'destroy'])->name('destroy');

        Route::prefix('templates')->name('templates.')->group(function () {
            Route::post('/',                    [ScheduleController::class, 'storeTemplate'])->name('store');
            Route::put('{template}',            [ScheduleController::class, 'updateTemplate'])->name('update');
            Route::delete('{template}',         [ScheduleController::class, 'destroyTemplate'])->name('destroy');
        });
    });

    /*-- Attendance --*/
    Route::middleware('module:attendance')->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/',             [AttendanceController::class, 'index'])->name('index');
        Route::get('filter',        [AttendanceController::class, 'filter'])->name('filter');
        Route::get('{log}/edit',    [AttendanceController::class, 'edit'])->name('edit');
        Route::put('{log}',         [AttendanceController::class, 'update'])->name('update');
        Route::delete('{log}',      [AttendanceController::class, 'destroy'])->name('destroy');
        Route::post('add-entry',    [AttendanceController::class, 'addEntry'])->name('add');
        Route::post('{log}/approve',[AttendanceController::class, 'approve'])->name('approve');
    });

    /*-- Leaves --*/
    Route::middleware('module:leaves')->prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/',           [LeaveController::class, 'index'])->name('index');
        Route::get('{leave}/edit',[LeaveController::class, 'edit'])->name('edit');
        Route::put('{leave}',     [LeaveController::class, 'update'])->name('update');
        Route::delete('{leave}',  [LeaveController::class, 'destroy'])->name('destroy');
    });

    /*-- Sales --*/
    Route::middleware('module:sales')->prefix('sales')->name('sales.')->group(function () {
        Route::get('/',                [SaleController::class, 'index'])->name('index');
        Route::get('filter',           [SaleController::class, 'filter'])->name('filter');
        Route::get('create',           [SaleController::class, 'create'])->name('create');
        Route::post('/',               [SaleController::class, 'store'])->name('store');
        Route::get('{sale}/edit',      [SaleController::class, 'edit'])->name('edit');
        Route::put('{sale}',           [SaleController::class, 'update'])->name('update');
        Route::delete('{sale}',        [SaleController::class, 'destroy'])->name('destroy');
        Route::post('{sale}/compensate',[SaleController::class, 'markCompensated'])->name('compensate');
    });

    /*-- Payroll --*/
    Route::middleware('module:payroll')->prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                    [PayrollController::class, 'index'])->name('index');
        Route::post('periods',             [PayrollController::class, 'storePeriod'])->name('periods.store');
        Route::post('periods/{period}/run',[PayrollController::class, 'run'])->name('run');
        Route::post('runs/{run}/finalize', [PayrollController::class, 'finalize'])->name('finalize');
        Route::delete('runs/{run}',        [PayrollController::class, 'destroyRun'])->name('run.destroy');
        Route::get('runs/{run}',           [PayrollController::class, 'showRun'])->name('run.show');
        Route::get('slips/{slip}',         [PayrollController::class, 'showSlip'])->name('slip.show');
        Route::get('slips/{slip}/download',[PayrollController::class, 'downloadSlip'])->name('slip.download');
        Route::get('export/{run}',         [PayrollController::class, 'export'])->name('export');
        Route::post('tax-config',          [PayrollController::class, 'storeTax'])->name('tax.store');
        Route::delete('tax-config/{tax}',  [PayrollController::class, 'destroyTax'])->name('tax.destroy');

        Route::get('adjustments',                        [PayrollAdjustmentController::class, 'index'])->name('adjustments.index');
        Route::post('adjustments',                       [PayrollAdjustmentController::class, 'store'])->name('adjustments.store');
        Route::put('adjustments/{adjustment}',           [PayrollAdjustmentController::class, 'update'])->name('adjustments.update');
        Route::delete('adjustments/{adjustment}',        [PayrollAdjustmentController::class, 'destroy'])->name('adjustments.destroy');
    });

    /*-- Performance --*/
    Route::middleware('module:performance')->prefix('performance')->name('performance.')->group(function () {
        Route::get('/',                     [PerformanceController::class, 'index'])->name('index');
        Route::post('compute',              [PerformanceController::class, 'compute'])->name('compute');
        Route::get('kpi/definitions',       [PerformanceController::class, 'kpiIndex'])->name('kpi.index');
        Route::post('kpi/definitions',      [PerformanceController::class, 'kpiStore'])->name('kpi.store');
        Route::put('kpi/definitions/{kpi}', [PerformanceController::class, 'kpiUpdate'])->name('kpi.update');
        Route::get('{employee}',            [PerformanceController::class, 'show'])->name('show');
    });

    /*-- Tasks --*/
    Route::middleware('module:tasks')->prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/',                [TaskController::class, 'index'])->name('index');
        Route::post('/',               [TaskController::class, 'store'])->name('store');
        Route::get('{task}',           [TaskController::class, 'show'])->name('show');
        Route::put('{task}',           [TaskController::class, 'update'])->name('update');
        Route::delete('{task}',        [TaskController::class, 'destroy'])->name('destroy');
        Route::post('{task}/assign',   [TaskController::class, 'assign'])->name('assign');
        Route::post('{task}/comments', [TaskController::class, 'comment'])->name('comment');
        Route::get('projects',         [TaskController::class, 'projects'])->name('projects');
        Route::post('projects',        [TaskController::class, 'storeProject'])->name('projects.store');
    });

    /*-- Users --*/
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',               [UserController::class, 'index'])->name('index');
        Route::post('/',              [UserController::class, 'store'])->name('store');
        Route::get('{user}/edit',     [UserController::class, 'edit'])->name('edit');
        Route::put('{user}',          [UserController::class, 'update'])->name('update');
        Route::delete('{user}',       [UserController::class, 'destroy'])->name('destroy');
        Route::post('{user}/enable',  [UserController::class, 'enable'])->name('enable');
        Route::post('{user}/disable', [UserController::class, 'disable'])->name('disable');
    });

    /*-- Settings --*/
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',            [SettingsController::class, 'index'])->name('index');
        Route::put('/',            [SettingsController::class, 'update'])->name('update');
        Route::post('modules/{module}/toggle', [SettingsController::class, 'toggleModule'])->name('modules.toggle');
    });
});

/*
|--------------------------------------------------------------------------
| Chat (all authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'module:chat'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/',                          [ChatController::class, 'index'])->name('index');
    Route::get('{conversation}',             [ChatController::class, 'show'])->name('show');
    Route::post('/',                         [ChatController::class, 'startConversation'])->name('start');
    Route::post('{conversation}/messages',   [ChatController::class, 'sendMessage'])->name('send');
    Route::get('attachments/{attachment}/download', [ChatController::class, 'downloadAttachment'])->name('attachment.download');
});

/*
|--------------------------------------------------------------------------
| Redirect root based on role
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (! auth()->check()) return redirect()->route('login');
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('my.dashboard');
});
