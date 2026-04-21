<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\Performance\KpiDefinition;
use App\Models\Performance\KpiSnapshot;
use App\Services\Performance\KpiService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function __construct(private KpiService $kpiService) {}

    public function index(): View
    {
        $month      = Carbon::now()->startOfMonth();
        $employees  = Employee::active()->with(['kpiSnapshots' => function ($q) use ($month) {
            $q->where('period_start', $month->toDateString());
        }])->orderBy('lastname')->get();

        return view('admin.performance.index', compact('employees', 'month'));
    }

    public function show(Employee $employee): View
    {
        $snapshots = KpiSnapshot::where('employee_id', $employee->id)
            ->with('kpi')
            ->orderByDesc('period_start')
            ->paginate(20);

        return view('admin.performance.show', compact('employee', 'snapshots'));
    }

    public function compute(Request $request): RedirectResponse
    {
        $month = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfDay();
        $this->kpiService->computeAll($month, $end);

        return redirect()->route('admin.performance.index')
            ->with('success', 'KPI scores computed for ' . $month->format('F Y') . '.');
    }

    public function kpiIndex(): View
    {
        $definitions = KpiDefinition::orderBy('module_key')->orderBy('name')->get();
        return view('admin.performance.kpi-index', compact('definitions'));
    }

    public function kpiStore(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'module_key' => ['required', 'in:attendance,sales,tasks'],
            'metric_key' => ['required', 'string', 'max:60'],
            'weight'     => ['required', 'numeric', 'min:0', 'max:100'],
            'target'     => ['required', 'numeric', 'min:0'],
            'is_active'  => ['boolean'],
        ]);

        KpiDefinition::create($request->only('name', 'module_key', 'metric_key', 'weight', 'target', 'is_active'));

        return redirect()->route('admin.performance.kpi.index')
            ->with('success', 'KPI definition created.');
    }

    public function kpiUpdate(Request $request, KpiDefinition $kpi): RedirectResponse
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'weight'    => ['required', 'numeric', 'min:0', 'max:100'],
            'target'    => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $kpi->update($request->only('name', 'weight', 'target', 'is_active'));

        return redirect()->route('admin.performance.kpi.index')
            ->with('success', 'KPI definition updated.');
    }
}
