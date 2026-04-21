<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Schedule;
use App\Models\Attendance\ShiftTemplate;
use App\Models\HR\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    // ── Roster ────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $weekStart = $request->input('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $days = collect(CarbonPeriod::create($weekStart, $weekStart->copy()->addDays(6)));

        $employees = Employee::active()
            ->orderBy('lastname')
            ->with(['schedules' => fn ($q) => $q->where('is_archived', false)->with('shiftTemplate')])
            ->get();

        // Build [employee_id][date] => Schedule|null roster map
        $roster = [];
        foreach ($employees as $emp) {
            foreach ($days as $day) {
                $roster[$emp->id][$day->toDateString()] = Schedule::forDate($emp->id, $day);
            }
        }

        $templates = ShiftTemplate::active()->orderBy('name')->get();

        // Unarchived schedule assignments for the assignment list
        $assignments = Schedule::with(['employee', 'shiftTemplate'])
            ->where('is_archived', false)
            ->orderBy('effective_from')
            ->get();

        return view('admin.schedules.index', compact(
            'weekStart', 'days', 'employees', 'roster', 'templates', 'assignments'
        ));
    }

    // ── Assignments ───────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'employee_id'       => ['required', 'exists:employees,id'],
            'shift_template_id' => ['nullable', 'exists:shift_templates,id'],
            'name'              => ['nullable', 'string', 'max:100'],
            'shift_in'          => ['required_without:shift_template_id', 'nullable', 'date_format:H:i'],
            'shift_out'         => ['required_without:shift_template_id', 'nullable', 'date_format:H:i'],
            'is_overnight'      => ['boolean'],
            'break_minutes'     => ['integer', 'min:0', 'max:480'],
            'days_of_week'      => ['nullable', 'array'],
            'days_of_week.*'    => ['integer', 'min:1', 'max:7'],
            'effective_from'    => ['nullable', 'date'],
            'effective_to'      => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        if ($v['shift_template_id'] ?? null) {
            $tpl = ShiftTemplate::findOrFail($v['shift_template_id']);
            $v['shift_in']    = $tpl->shift_in;
            $v['shift_out']   = $tpl->shift_out;
            $v['is_overnight'] = $tpl->is_overnight;
            if (empty($v['break_minutes'])) $v['break_minutes'] = $tpl->break_minutes;
        }

        Schedule::create($v);

        return back()->with('success', 'Schedule assigned.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->update(['is_archived' => true]);
        return back()->with('success', 'Schedule removed.');
    }

    // ── Shift Templates ───────────────────────────────────────────────────

    public function storeTemplate(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'shift_in'      => ['required', 'date_format:H:i'],
            'shift_out'     => ['required', 'date_format:H:i'],
            'is_overnight'  => ['boolean'],
            'break_minutes' => ['integer', 'min:0', 'max:480'],
            'color'         => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        ShiftTemplate::create($v);

        return back()->with('success', "Shift template '{$v['name']}' created.");
    }

    public function updateTemplate(Request $request, ShiftTemplate $template): RedirectResponse
    {
        $v = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'shift_in'      => ['required', 'date_format:H:i'],
            'shift_out'     => ['required', 'date_format:H:i'],
            'is_overnight'  => ['boolean'],
            'break_minutes' => ['integer', 'min:0', 'max:480'],
            'color'         => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active'     => ['boolean'],
        ]);

        $template->update($v);

        return back()->with('success', "Template '{$template->name}' updated.");
    }

    public function destroyTemplate(ShiftTemplate $template): RedirectResponse
    {
        if ($template->schedules()->where('is_archived', false)->exists()) {
            return back()->withErrors(['template' => "Cannot delete '{$template->name}' — it has active assignments. Archive those first."]);
        }

        $template->delete();
        return back()->with('success', "Template deleted.");
    }
}
