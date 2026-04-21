<?php

namespace Database\Seeders;

use App\Models\Attendance\Schedule;
use App\Models\Attendance\ShiftTemplate;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // ── Shift Templates ────────────────────────────────────────────────

        $templates = [
            [
                'name'          => 'Morning Shift',
                'shift_in'      => '06:00',
                'shift_out'     => '14:00',
                'is_overnight'  => false,
                'break_minutes' => 30,
                'color'         => '#f59e0b',
            ],
            [
                'name'          => 'Day Shift',
                'shift_in'      => '08:00',
                'shift_out'     => '17:00',
                'is_overnight'  => false,
                'break_minutes' => 60,
                'color'         => '#6366f1',
            ],
            [
                'name'          => 'Mid Shift',
                'shift_in'      => '10:00',
                'shift_out'     => '19:00',
                'is_overnight'  => false,
                'break_minutes' => 60,
                'color'         => '#10b981',
            ],
            [
                'name'          => 'Afternoon Shift',
                'shift_in'      => '14:00',
                'shift_out'     => '22:00',
                'is_overnight'  => false,
                'break_minutes' => 30,
                'color'         => '#f97316',
            ],
            [
                'name'          => 'Night Shift',
                'shift_in'      => '22:00',
                'shift_out'     => '06:00',
                'is_overnight'  => true,
                'break_minutes' => 30,
                'color'         => '#8b5cf6',
            ],
            [
                'name'          => 'Weekends',
                'shift_in'      => '09:00',
                'shift_out'     => '15:00',
                'is_overnight'  => false,
                'break_minutes' => 0,
                'color'         => '#ec4899',
            ],
        ];

        $created = [];
        foreach ($templates as $t) {
            $created[$t['name']] = ShiftTemplate::firstOrCreate(['name' => $t['name']], $t);
        }

        // ── Schedule Assignments ───────────────────────────────────────────
        // Days of week: 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat, 7=Sun

        $weekdays    = [1, 2, 3, 4, 5];
        $weekends    = [6, 7];
        $allWeek     = null; // null = every day

        $assignments = [
            // Maria Santos — office manager, standard day shift Mon-Fri
            [
                'employee_id'       => 1,
                'shift_template_id' => $created['Day Shift']->id,
                'shift_in'          => '08:00',
                'shift_out'         => '17:00',
                'break_minutes'     => 60,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-01-01',
            ],

            // James Reyes — sales, mid shift Mon-Fri
            [
                'employee_id'       => 2,
                'shift_template_id' => $created['Mid Shift']->id,
                'shift_in'          => '10:00',
                'shift_out'         => '19:00',
                'break_minutes'     => 60,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-01-01',
            ],

            // Angela Cruz — day shift Mon-Fri + weekend coverage
            [
                'employee_id'       => 3,
                'shift_template_id' => $created['Day Shift']->id,
                'shift_in'          => '08:00',
                'shift_out'         => '17:00',
                'break_minutes'     => 60,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-01-01',
            ],
            [
                'employee_id'       => 3,
                'shift_template_id' => $created['Weekends']->id,
                'shift_in'          => '09:00',
                'shift_out'         => '15:00',
                'break_minutes'     => 0,
                'days_of_week'      => $weekends,
                'effective_from'    => '2026-01-01',
            ],

            // Carlos Mendoza — rotating: morning Jan-Mar, afternoon Apr onwards
            [
                'employee_id'       => 4,
                'shift_template_id' => $created['Morning Shift']->id,
                'shift_in'          => '06:00',
                'shift_out'         => '14:00',
                'break_minutes'     => 30,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-01-01',
                'effective_to'      => '2026-03-31',
            ],
            [
                'employee_id'       => 4,
                'shift_template_id' => $created['Afternoon Shift']->id,
                'shift_in'          => '14:00',
                'shift_out'         => '22:00',
                'break_minutes'     => 30,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-04-01',
            ],

            // Trisha Villanueva — mid shift, Mon-Fri
            [
                'employee_id'       => 5,
                'shift_template_id' => $created['Mid Shift']->id,
                'shift_in'          => '10:00',
                'shift_out'         => '19:00',
                'break_minutes'     => 60,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-01-01',
            ],

            // Kevin Lim — night shift, Mon-Fri
            [
                'employee_id'       => 6,
                'shift_template_id' => $created['Night Shift']->id,
                'shift_in'          => '22:00',
                'shift_out'         => '06:00',
                'is_overnight'      => true,
                'break_minutes'     => 30,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-01-01',
            ],

            // Rachel Tan — day shift Mon-Fri
            [
                'employee_id'       => 7,
                'shift_template_id' => $created['Day Shift']->id,
                'shift_in'          => '08:00',
                'shift_out'         => '17:00',
                'break_minutes'     => 60,
                'days_of_week'      => $weekdays,
                'effective_from'    => '2026-02-01',
            ],

            // Mark Aquino — morning shift, all week (retail/ops)
            [
                'employee_id'       => 8,
                'shift_template_id' => $created['Morning Shift']->id,
                'shift_in'          => '06:00',
                'shift_out'         => '14:00',
                'break_minutes'     => 30,
                'days_of_week'      => $allWeek,
                'effective_from'    => '2026-01-01',
            ],

            // Davian Beroni — flexible day, no specific days restriction
            [
                'employee_id'       => 9,
                'shift_template_id' => $created['Day Shift']->id,
                'shift_in'          => '08:00',
                'shift_out'         => '17:00',
                'break_minutes'     => 60,
                'days_of_week'      => $allWeek,
                'effective_from'    => '2026-01-01',
            ],
        ];

        foreach ($assignments as $a) {
            Schedule::create(array_merge(['is_overnight' => false, 'is_archived' => false], $a));
        }

        $this->command->info('Seeded ' . count($templates) . ' shift templates and ' . count($assignments) . ' schedule assignments.');
    }
}
