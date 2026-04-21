<?php

namespace App\Services\Attendance;

use App\Events\AttendanceLogged;
use App\Models\Attendance\AttendanceLog;
use App\Models\Attendance\Schedule;
use App\Models\HR\Employee;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Clock an employee in.
     *
     * @throws \RuntimeException if the employee has an open clock-in
     */
    public function clockIn(
        Employee $employee,
        string $reason = 'Shift',
        ?string $comment = null,
        ?string $loggedBy = null
    ): AttendanceLog {
        // Guard: must not have an open session
        $open = AttendanceLog::where('employee_id', $employee->id)
                             ->whereNull('clock_out')
                             ->exists();

        if ($open) {
            throw new \RuntimeException('Employee has an open clock-in. Please clock out first.');
        }

        $now      = Carbon::now();
        $schedule = $employee->activeSchedule;
        $statusIn = $this->resolveStatusIn($reason, $now, $schedule);

        return DB::transaction(function () use ($employee, $reason, $comment, $loggedBy, $now, $statusIn) {
            $log = AttendanceLog::create([
                'employee_id' => $employee->id,
                'log_date'    => $now->toDateString(),
                'clock_in'    => $now,
                'reason'      => $reason,
                'status_in'   => $statusIn,
                'comment'     => $comment,
                'logged_by'   => $loggedBy ?? 'System',
            ]);

            event(new AttendanceLogged($log));
            return $log;
        });
    }

    /**
     * Clock an employee out, computing total_minutes.
     */
    public function clockOut(
        Employee $employee,
        string $reason = 'Shift',
        ?string $loggedBy = null
    ): AttendanceLog {
        $log = AttendanceLog::where('employee_id', $employee->id)
                            ->whereNull('clock_out')
                            ->latest('clock_in')
                            ->firstOrFail();

        $now       = Carbon::now();
        $schedule  = $employee->activeSchedule;
        $statusOut = $this->resolveStatusOut($reason, $now, $schedule);
        $minutes   = (int) $log->clock_in->diffInMinutes($now);

        $log->update([
            'clock_out'     => $now,
            'total_minutes' => $minutes,
            'status_out'    => $statusOut,
            'logged_by'     => $loggedBy ?? $log->logged_by,
        ]);

        event(new AttendanceLogged($log->fresh()));
        return $log;
    }

    // ── Private helpers ──────────────────────────────────────────

    private function resolveStatusIn(string $reason, Carbon $now, ?Schedule $schedule): string
    {
        return match ($reason) {
            'Lunch' => 'Lunch In',
            'Break' => 'Break In',
            default => $this->evaluateShiftIn($now, $schedule),
        };
    }

    private function resolveStatusOut(string $reason, Carbon $now, ?Schedule $schedule): string
    {
        return match ($reason) {
            'Lunch' => 'Lunch Out',
            'Break' => 'Break Out',
            default => $this->evaluateShiftOut($now, $schedule),
        };
    }

    private function evaluateShiftIn(Carbon $now, ?Schedule $schedule): string
    {
        if (! $schedule) return 'Ok';

        $shiftIn = Carbon::today()->setTimeFromTimeString($schedule->getRawOriginal('shift_in'));
        return $now->lte($shiftIn) ? 'In Time' : 'Late In';
    }

    private function evaluateShiftOut(Carbon $now, ?Schedule $schedule): string
    {
        if (! $schedule) return 'Ok';

        $shiftOut = Carbon::today()->setTimeFromTimeString($schedule->getRawOriginal('shift_out'));
        if ($schedule->is_overnight) {
            $shiftOut->addDay();
        }
        return $now->gte($shiftOut) ? 'On Time' : 'Early Out';
    }
}
