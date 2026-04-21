<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\HR\Employee;
use App\Models\User;
use App\Notifications\LeaveStatusUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(): View
    {
        $leaves = LeaveRequest::with(['employee', 'leaveType'])
            ->where('status', 'Pending')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.leaves.index', compact('leaves'));
    }

    public function edit(LeaveRequest $leave): View
    {
        $leave->load(['employee', 'leaveType']);
        return view('admin.leaves.edit', compact('leave'));
    }

    public function update(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Approved,Rejected,Cancelled'],
        ]);

        $leave->update([
            'status'      => $validated['status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $leave->load('leaveType');
        $user = User::where('employee_id', $leave->employee_id)->first();
        $user?->notify(new LeaveStatusUpdated($leave));

        return redirect()->route('admin.leaves.index')
            ->with('success', "Leave request {$validated['status']}.");
    }

    public function destroy(LeaveRequest $leave): RedirectResponse
    {
        $leave->delete();
        return back()->with('success', 'Leave request removed.');
    }
}
