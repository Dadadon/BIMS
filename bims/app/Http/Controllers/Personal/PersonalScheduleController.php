<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Schedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\View\View;

class PersonalScheduleController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user()->employee;

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $week = [];
        if ($employee) {
            foreach (CarbonPeriod::create($weekStart, $weekEnd) as $date) {
                $week[] = [
                    'date'     => $date->copy(),
                    'schedule' => Schedule::forDate($employee->id, $date),
                ];
            }
        }

        // Upcoming: next 4 weeks beyond current week, unique active assignments
        $upcoming = $employee
            ? Schedule::with('shiftTemplate')
                ->where('employee_id', $employee->id)
                ->where('is_archived', false)
                ->where(function ($q) use ($weekEnd) {
                    $q->whereNull('effective_to')->orWhere('effective_to', '>', $weekEnd->toDateString());
                })
                ->orderBy('effective_from')
                ->get()
            : collect();

        return view('personal.schedule', compact('employee', 'week', 'weekStart', 'upcoming'));
    }
}
