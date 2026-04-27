<?php

namespace Database\Factories;

use App\Models\Attendance\AttendanceLog;
use App\Models\HR\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceLogFactory extends Factory
{
    protected $model = AttendanceLog::class;

    public function definition(): array
    {
        $clockIn  = Carbon::instance($this->faker->dateTimeBetween('-60 days', '-1 day'))
                          ->setTime($this->faker->numberBetween(7, 9), $this->faker->numberBetween(0, 59));
        $minutes  = $this->faker->numberBetween(240, 600); // 4–10 hours
        $clockOut = $clockIn->copy()->addMinutes($minutes);

        return [
            'employee_id'   => Employee::factory(),
            'log_date'      => $clockIn->toDateString(),
            'clock_in'      => $clockIn,
            'clock_out'     => $clockOut,
            'total_minutes' => $minutes,
            'reason'        => 'Shift',
            'status_in'     => 'In Time',
            'status_out'    => 'On Time',
            'is_approved'   => true,
            'logged_by'     => 'System',
        ];
    }

    /** Log with specific minutes worked — useful for hour-conversion assertions. */
    public function minutes(int $totalMinutes): static
    {
        return $this->state(function () use ($totalMinutes) {
            $clockIn  = Carbon::now()->subMinutes($totalMinutes + 5);
            $clockOut = $clockIn->copy()->addMinutes($totalMinutes);
            return [
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut,
                'total_minutes' => $totalMinutes,
                'log_date'      => $clockIn->toDateString(),
            ];
        });
    }

    /** Open clock-in (not yet clocked out). */
    public function open(): static
    {
        return $this->state([
            'clock_out'     => null,
            'total_minutes' => null,
            'status_out'    => null,
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(function () use ($date) {
            $clockIn  = Carbon::parse($date)->setTime(8, 0);
            $minutes  = 480;
            return [
                'log_date'      => $date,
                'clock_in'      => $clockIn,
                'clock_out'     => $clockIn->copy()->addMinutes($minutes),
                'total_minutes' => $minutes,
            ];
        });
    }
}
