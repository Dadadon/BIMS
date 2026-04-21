<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceLog;
use App\Models\HR\Employee;
use App\Models\Leave\LeaveRequest;
use App\Models\Module;
use App\Models\Sales\Sale;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today     = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $stats = [
            'total_employees' => Employee::active()->count(),
            'clocked_in_today'=> AttendanceLog::whereDate('log_date', $today)
                                    ->where('reason', 'Shift')
                                    ->whereNull('clock_out')
                                    ->count(),
            'late_today'      => AttendanceLog::whereDate('log_date', $today)
                                    ->where('status_in', 'Late In')
                                    ->count(),
        ];

        $pendingLeaves = Module::isEnabled('leaves')
            ? LeaveRequest::where('status', 'Pending')->count()
            : 0;

        $monthlySales = Module::isEnabled('sales')
            ? Sale::whereDate('sale_date', '>=', $monthStart)->count()
            : 0;

        // Attendance summary for the last 7 days
        $attendanceTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date  = Carbon::today()->subDays($i);
            $count = AttendanceLog::whereDate('log_date', $date)
                        ->where('reason', 'Shift')
                        ->whereNotNull('clock_out')
                        ->count();
            $attendanceTrend->push([
                'date'  => $date->format('D'),
                'count' => $count,
            ]);
        }

        // Recent leave requests
        $recentLeaves = Module::isEnabled('leaves')
            ? LeaveRequest::with(['employee', 'leaveType'])
                ->where('status', 'Pending')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return view('admin.dashboard', compact(
            'stats', 'pendingLeaves', 'monthlySales', 'attendanceTrend', 'recentLeaves'
        ));
    }
}
