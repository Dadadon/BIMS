<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Performance\KpiSnapshot;
use Carbon\Carbon;
use Illuminate\View\View;

class PersonalPerformanceController extends Controller
{
    public function index(): View
    {
        $employee  = auth()->user()->employee;

        $snapshots = KpiSnapshot::where('employee_id', $employee?->id ?? 0)
            ->with('kpi')
            ->orderByDesc('period_start')
            ->paginate(20);

        return view('personal.performance', compact('employee', 'snapshots'));
    }
}
