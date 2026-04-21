<?php

namespace App\Services\Performance;

use App\Models\Attendance\AttendanceLog;
use App\Models\HR\Employee;
use App\Models\Performance\KpiDefinition;
use App\Models\Performance\KpiSnapshot;
use App\Models\Sales\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KpiService
{
    /**
     * Compute and upsert all KPI snapshots for all active employees
     * for the given period. Called daily by the scheduler.
     */
    public function computeAll(Carbon $periodStart, Carbon $periodEnd): void
    {
        $employees = Employee::active()->with('kpiDefinitions')->get();

        foreach ($employees as $employee) {
            $kpis = $employee->kpiDefinitions->where('is_active', true);
            foreach ($kpis as $kpi) {
                $this->computeSnapshot($employee, $kpi, $periodStart, $periodEnd);
            }
        }
    }

    public function computeSnapshot(
        Employee $employee,
        KpiDefinition $kpi,
        Carbon $periodStart,
        Carbon $periodEnd
    ): KpiSnapshot {
        $value = $this->measureMetric($employee, $kpi, $periodStart, $periodEnd);
        $score = KpiSnapshot::normalize($value, (float) $kpi->target_value, $kpi->direction);

        return KpiSnapshot::updateOrCreate(
            [
                'employee_id'  => $employee->id,
                'kpi_id'       => $kpi->id,
                'period_start' => $periodStart->toDateString(),
                'period_end'   => $periodEnd->toDateString(),
            ],
            [
                'value'       => $value,
                'score'       => $score,
                'computed_at' => now(),
            ]
        );
    }

    // ── Metric resolvers ─────────────────────────────────────────

    private function measureMetric(Employee $employee, KpiDefinition $kpi, Carbon $from, Carbon $to): float
    {
        return match ($kpi->metric) {
            'late_in_count'      => $this->countLateIns($employee, $from, $to),
            'attendance_rate'    => $this->attendanceRate($employee, $from, $to),
            'total_agent_points' => $this->totalAgentPoints($employee, $from, $to),
            'sale_count'         => $this->saleCount($employee, $from, $to),
            default              => 0.0,
        };
    }

    private function countLateIns(Employee $employee, Carbon $from, Carbon $to): float
    {
        return AttendanceLog::where('employee_id', $employee->id)
            ->forPeriod($from->toDateString(), $to->toDateString())
            ->where('status_in', 'Late In')
            ->count();
    }

    private function attendanceRate(Employee $employee, Carbon $from, Carbon $to): float
    {
        $workdays     = $from->diffInWeekdays($to) + 1;
        $daysPresent  = AttendanceLog::where('employee_id', $employee->id)
            ->forPeriod($from->toDateString(), $to->toDateString())
            ->where('reason', 'Shift')
            ->distinct('log_date')
            ->count('log_date');

        return $workdays > 0 ? round(($daysPresent / $workdays) * 100, 2) : 0;
    }

    private function totalAgentPoints(Employee $employee, Carbon $from, Carbon $to): float
    {
        return (float) Sale::forEmployee($employee->id)
            ->forPeriod($from->toDateString(), $to->toDateString())
            ->sum('agent_points');
    }

    private function saleCount(Employee $employee, Carbon $from, Carbon $to): float
    {
        return Sale::forEmployee($employee->id)
            ->forPeriod($from->toDateString(), $to->toDateString())
            ->count();
    }
}
