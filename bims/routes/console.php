<?php

use App\Jobs\ComputeKpiSnapshotsJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;

// Recompute all KPI snapshots nightly at midnight
Schedule::job(new ComputeKpiSnapshotsJob(
    Carbon::now()->startOfMonth(),
    Carbon::now()->endOfMonth(),
))->dailyAt('00:05')->name('kpi-snapshots');
