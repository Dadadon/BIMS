<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PersonalLeaveController extends Controller
{
    public function index(): View
    {
        $employee   = auth()->user()->employee;
        $requests   = collect();
        $leaveTypes = collect();
        $balances   = collect();

        if ($employee) {
            $requests = LeaveRequest::where('employee_id', $employee->id)
                ->with('leaveType')
                ->orderByDesc('created_at')
                ->paginate(15);

            // Load leave types for this employee's leave group
            $groupId    = $employee->leave_group_id;
            $leaveTypes = LeaveType::when($groupId, fn($q) => $q->where('leave_group_id', $groupId))
                ->orderBy('name')
                ->get();

            // Used days per type (approved requests this calendar year)
            $year    = now()->year;
            $usedMap = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'Approved')
                ->whereYear('date_from', $year)
                ->selectRaw('leave_type_id, COALESCE(SUM(total_days), 0) as used')
                ->groupBy('leave_type_id')
                ->pluck('used', 'leave_type_id');

            $balances = $leaveTypes->map(fn($lt) => [
                'id'           => $lt->id,
                'name'         => $lt->name,
                'is_paid'      => $lt->is_paid,
                'days_per_year'=> $lt->days_per_year,
                'used'         => (float) ($usedMap[$lt->id] ?? 0),
                'remaining'    => max(0, $lt->days_per_year - ($usedMap[$lt->id] ?? 0)),
            ]);
        } else {
            $leaveTypes = LeaveType::orderBy('name')->get();
        }

        return view('personal.leaves', compact('employee', 'requests', 'leaveTypes', 'balances'));
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            return back()->with('error', 'No employee record linked to your account.');
        }

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'date_from'     => ['required', 'date', 'after_or_equal:today'],
            'date_to'       => ['required', 'date', 'after_or_equal:date_from'],
            'reason'        => ['required', 'string', 'max:500'],
        ]);

        $dateFrom = \Carbon\Carbon::parse($validated['date_from']);
        $dateTo   = \Carbon\Carbon::parse($validated['date_to']);
        $totalDays = $dateFrom->diffInWeekdays($dateTo) + 1;

        LeaveRequest::create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'date_from'     => $validated['date_from'],
            'date_to'       => $validated['date_to'],
            'total_days'    => $totalDays,
            'reason'        => $validated['reason'],
            'status'        => 'Pending',
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        $employee = auth()->user()->employee;

        if ($leaveRequest->employee_id !== $employee?->id) {
            abort(403);
        }

        if ($leaveRequest->status !== 'Pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $leaveRequest->update(['status' => 'Cancelled']);

        return back()->with('success', 'Leave request cancelled.');
    }
}
