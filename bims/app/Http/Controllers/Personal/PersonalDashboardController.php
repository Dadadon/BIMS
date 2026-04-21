<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceLog;
use App\Models\Module;
use App\Models\Sales\Sale;
use Carbon\Carbon;
use Illuminate\View\View;

class PersonalDashboardController extends Controller
{
    public function index(): View
    {
        $user     = auth()->user();
        $employee = $user->employee;

        $monthStart = Carbon::now()->startOfMonth();
        $today      = Carbon::today();

        $stats = [];

        if ($employee) {
            $stats['hours_this_month'] = AttendanceLog::where('employee_id', $employee->id)
                ->where('reason', 'Shift')
                ->whereDate('log_date', '>=', $monthStart)
                ->whereNotNull('total_minutes')
                ->sum('total_minutes');

            $stats['late_this_month'] = AttendanceLog::where('employee_id', $employee->id)
                ->where('status_in', 'Late In')
                ->whereDate('log_date', '>=', $monthStart)
                ->count();

            $stats['open_log'] = AttendanceLog::where('employee_id', $employee->id)
                ->whereNull('clock_out')
                ->latest('clock_in')
                ->first();

            if (Module::isEnabled('sales')) {
                $stats['sales_this_month'] = Sale::where('employee_id', $employee->id)
                    ->whereDate('sale_date', '>=', $monthStart)
                    ->count();
            }
        }

        return view('personal.dashboard', compact('employee', 'stats'));
    }
}
