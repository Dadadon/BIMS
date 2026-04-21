<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $employee = auth()->user()->employee;

        $month = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now()->startOfMonth();

        $logs = collect();
        if ($employee) {
            $logs = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('log_date', '>=', $month->copy()->startOfMonth())
                ->whereDate('log_date', '<=', $month->copy()->endOfMonth())
                ->orderByDesc('log_date')
                ->orderBy('clock_in')
                ->get();
        }

        $totalMinutes = $logs->where('reason', 'Shift')->whereNotNull('total_minutes')->sum('total_minutes');
        $lateCount    = $logs->where('status_in', 'Late In')->count();

        return view('personal.attendance', compact('employee', 'logs', 'month', 'totalMinutes', 'lateCount'));
    }
}
