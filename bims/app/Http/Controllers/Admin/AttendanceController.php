<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceLog;
use App\Models\HR\Employee;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance) {}

    public function index(): View
    {
        $logs = AttendanceLog::with('employee')
            ->whereDate('log_date', Carbon::today())
            ->orderByDesc('clock_in')
            ->paginate(30);

        return view('admin.attendance.index', compact('logs'));
    }

    public function filter(Request $request): View
    {
        $query = AttendanceLog::with('employee')->orderByDesc('log_date')->orderByDesc('clock_in');

        if ($date = $request->input('date')) {
            $query->whereDate('log_date', $date);
        }

        if ($employeeId = $request->input('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        if ($status = $request->input('status_in')) {
            $query->where('status_in', $status);
        }

        if ($request->input('open_only')) {
            $query->whereNull('clock_out');
        }

        $logs      = $query->paginate(30)->withQueryString();
        $employees = Employee::active()->orderBy('lastname')->get();

        return view('admin.attendance.filter', compact('logs', 'employees'));
    }

    public function edit(AttendanceLog $log): View
    {
        $log->load('employee');
        return view('admin.attendance.edit', compact('log'));
    }

    public function update(Request $request, AttendanceLog $log): RedirectResponse
    {
        $validated = $request->validate([
            'clock_in'      => ['required', 'date_format:Y-m-d\TH:i,Y-m-d H:i'],
            'clock_out'     => ['nullable', 'date_format:Y-m-d\TH:i,Y-m-d H:i', 'after:clock_in'],
            'reason'        => ['required', 'string', 'max:50'],
            'status_in'     => ['nullable', 'string', 'max:30'],
            'status_out'    => ['nullable', 'string', 'max:30'],
            'comment'       => ['nullable', 'string', 'max:255'],
        ]);

        $clockIn  = Carbon::parse($validated['clock_in']);
        $clockOut = isset($validated['clock_out']) ? Carbon::parse($validated['clock_out']) : null;
        $minutes  = $clockOut ? (int) $clockIn->diffInMinutes($clockOut) : null;

        $log->update([
            'clock_in'      => $clockIn,
            'clock_out'     => $clockOut,
            'total_minutes' => $minutes,
            'reason'        => $validated['reason'],
            'status_in'     => $validated['status_in'],
            'status_out'    => $validated['status_out'],
            'comment'       => $validated['comment'],
            'logged_by'     => auth()->user()->name,
        ]);

        return redirect()->route('admin.attendance.filter')
            ->with('success', 'Attendance record updated.');
    }

    public function destroy(AttendanceLog $log): RedirectResponse
    {
        $log->delete();
        return back()->with('success', 'Attendance record deleted.');
    }

    public function addEntry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'log_date'    => ['required', 'date'],
            'clock_in'    => ['required', 'date_format:H:i'],
            'clock_out'   => ['nullable', 'date_format:H:i'],
            'reason'      => ['required', 'string', 'max:50'],
            'comment'     => ['nullable', 'string', 'max:255'],
        ]);

        $date     = $validated['log_date'];
        $clockIn  = Carbon::parse("{$date} {$validated['clock_in']}");
        $clockOut = isset($validated['clock_out']) ? Carbon::parse("{$date} {$validated['clock_out']}") : null;
        $minutes  = $clockOut ? (int) $clockIn->diffInMinutes($clockOut) : null;

        AttendanceLog::create([
            'employee_id'   => $validated['employee_id'],
            'log_date'      => $date,
            'clock_in'      => $clockIn,
            'clock_out'     => $clockOut,
            'total_minutes' => $minutes,
            'reason'        => $validated['reason'],
            'status_in'     => 'Manual',
            'status_out'    => $clockOut ? 'Manual' : null,
            'comment'       => $validated['comment'],
            'logged_by'     => auth()->user()->name,
        ]);

        return redirect()->route('admin.attendance.filter', ['date' => $date])
            ->with('success', 'Entry added.');
    }

    public function approve(Request $request, AttendanceLog $log): RedirectResponse
    {
        $log->update(['is_approved' => true, 'approved_by' => auth()->user()->name]);
        return back()->with('success', 'Record approved.');
    }
}
