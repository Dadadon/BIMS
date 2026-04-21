<?php

namespace App\Listeners;

use App\Events\AttendanceLogged;
use App\Events\SaleRecorded;
use App\Models\Performance\KpiDefinition;
use App\Models\Performance\KpiSnapshot;
use App\Services\Performance\KpiService;
use Carbon\Carbon;
use Illuminate\Events\Dispatcher;

class PerformanceListener
{
    public function __construct(private KpiService $kpiService) {}

    public function handleAttendanceLogged(AttendanceLogged $event): void
    {
        // Only trigger on clock-out (when we have a complete record)
        if ($event->log->clock_out === null) return;
        if ($event->log->reason !== 'Shift') return;

        $employee = $event->log->employee;
        $date     = $event->log->log_date;
        $kpis     = KpiDefinition::where('module_key', 'attendance')->where('is_active', true)->get();

        // Use current month as the snapshot period
        $periodStart = Carbon::parse($date)->startOfMonth();
        $periodEnd   = Carbon::parse($date)->endOfMonth();

        foreach ($kpis as $kpi) {
            $this->kpiService->computeSnapshot($employee, $kpi, $periodStart, $periodEnd);
        }
    }

    public function handleSaleRecorded(SaleRecorded $event): void
    {
        $employee = $event->sale->employee;
        $date     = $event->sale->sale_date;
        $kpis     = KpiDefinition::where('module_key', 'sales')->where('is_active', true)->get();

        $periodStart = Carbon::parse($date)->startOfMonth();
        $periodEnd   = Carbon::parse($date)->endOfMonth();

        foreach ($kpis as $kpi) {
            $this->kpiService->computeSnapshot($employee, $kpi, $periodStart, $periodEnd);
        }
    }

    /** Subscribe to multiple events from a single listener class. */
    public function subscribe(Dispatcher $events): array
    {
        return [
            AttendanceLogged::class => 'handleAttendanceLogged',
            SaleRecorded::class     => 'handleSaleRecorded',
        ];
    }
}
