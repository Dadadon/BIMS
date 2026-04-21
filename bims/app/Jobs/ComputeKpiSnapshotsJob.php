<?php

namespace App\Jobs;

use App\Services\Performance\KpiService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Runs nightly via scheduler to refresh all KPI snapshots for the current month. */
class ComputeKpiSnapshotsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Carbon $periodStart,
        private Carbon $periodEnd,
    ) {}

    public function handle(KpiService $kpiService): void
    {
        $kpiService->computeAll($this->periodStart, $this->periodEnd);
    }
}
