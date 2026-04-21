<?php

namespace Database\Seeders;

use App\Models\HR\Employee;
use App\Models\HR\Company;
use App\Models\HR\Department;
use App\Models\HR\JobTitle;
use App\Models\HR\LeaveGroup;
use App\Models\Attendance\AttendanceLog;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleType;
use App\Models\Tasks\Project;
use App\Models\Tasks\Task;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedEmployeesAndUsers();
        $this->seedAttendance();
        $this->seedSales();
        $this->seedLeaveRequests();
        $this->seedTasks();
    }

    // ── Employees & Users ─────────────────────────────────────────

    private function seedEmployeesAndUsers(): void
    {
        $company    = Company::where('is_primary', true)->first();
        $salesDept  = Department::where('name', 'Sales')->first();
        $opsDept    = Department::where('name', 'Operations')->first();
        $agentTitle = JobTitle::where('title', 'Sales Agent')->first();
        $tlTitle    = JobTitle::where('title', 'Team Leader')->first();
        $leaveGroup = LeaveGroup::first();
        $empRole    = Role::where('slug', 'employee')->first();
        $tlRole     = Role::where('slug', 'team_lead_l1')->first();

        $people = [
            ['firstname' => 'Maria',   'lastname' => 'Santos',    'gender' => 'Female', 'title' => $tlTitle,    'role' => $tlRole,  'base_rate' => 650.00],
            ['firstname' => 'James',   'lastname' => 'Reyes',     'gender' => 'Male',   'title' => $agentTitle, 'role' => $empRole, 'base_rate' => 520.00],
            ['firstname' => 'Angela',  'lastname' => 'Cruz',      'gender' => 'Female', 'title' => $agentTitle, 'role' => $empRole, 'base_rate' => 520.00],
            ['firstname' => 'Carlos',  'lastname' => 'Mendoza',   'gender' => 'Male',   'title' => $agentTitle, 'role' => $empRole, 'base_rate' => 520.00],
            ['firstname' => 'Trisha',  'lastname' => 'Villanueva','gender' => 'Female', 'title' => $agentTitle, 'role' => $empRole, 'base_rate' => 520.00],
            ['firstname' => 'Kevin',   'lastname' => 'Lim',       'gender' => 'Male',   'title' => $agentTitle, 'role' => $empRole, 'base_rate' => 520.00],
            ['firstname' => 'Rachel',  'lastname' => 'Tan',       'gender' => 'Female', 'title' => $agentTitle, 'role' => $empRole, 'base_rate' => 520.00],
            ['firstname' => 'Mark',    'lastname' => 'Aquino',    'gender' => 'Male',   'title' => $agentTitle, 'role' => $empRole, 'base_rate' => 520.00],
        ];

        foreach ($people as $i => $p) {
            $code = 'EMP-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $employee = Employee::firstOrCreate(['employee_code' => $code], [
                'company_id'        => $company?->id,
                'department_id'     => ($i === 0) ? $opsDept?->id : $salesDept?->id,
                'job_title_id'      => $p['title']?->id,
                'leave_group_id'    => $leaveGroup?->id,
                'firstname'         => $p['firstname'],
                'lastname'          => $p['lastname'],
                'email'             => strtolower($p['firstname'] . '.' . $p['lastname']) . '@mpvinternational.com',
                'company_email'     => strtolower($p['firstname'] . '.' . $p['lastname']) . '@mpvinternational.com',
                'gender'            => $p['gender'],
                'employment_type'   => 'Regular',
                'employment_status' => 'Active',
                'start_date'        => Carbon::now()->subMonths(rand(6, 24)),
                'is_salaried'       => false,
                'base_rate'         => $p['base_rate'],
            ]);

            User::firstOrCreate(['email' => $employee->email], [
                'role_id'           => $p['role']?->id,
                'employee_id'       => $employee->id,
                'name'              => $p['firstname'] . ' ' . $p['lastname'],
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'acc_type'          => $i === 0 ? 'admin' : 'employee',
                'status'            => true,
            ]);
        }
    }

    // ── Attendance (last 30 working days) ────────────────────────

    private function seedAttendance(): void
    {
        $employees = Employee::where('employment_status', 'Active')->get();

        for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);

            // Skip weekends
            if ($date->isWeekend()) continue;

            foreach ($employees as $emp) {
                // 90% attendance rate
                if (rand(1, 10) === 1) continue;

                // Most on time, ~20% late
                $lateMinutes = (rand(1, 5) === 1) ? rand(5, 45) : 0;
                $clockIn  = $date->copy()->setTime(9, 0)->addMinutes($lateMinutes);
                $clockOut = $date->copy()->setTime(18, 0)->addMinutes(rand(-15, 30));

                $totalMinutes = (int) $clockIn->diffInMinutes($clockOut);

                AttendanceLog::firstOrCreate([
                    'employee_id' => $emp->id,
                    'log_date'    => $date->toDateString(),
                    'reason'      => 'Shift',
                ], [
                    'clock_in'      => $clockIn,
                    'clock_out'     => $clockOut,
                    'total_minutes' => $totalMinutes,
                    'status_in'     => $lateMinutes > 0 ? 'Late In' : 'In Time',
                    'status_out'    => 'On Time',
                    'is_approved'   => true,
                ]);
            }
        }
    }

    // ── Sales (last 30 days) ─────────────────────────────────────

    private function seedSales(): void
    {
        $saleTypes = SaleType::where('is_active', true)->get();
        $agents    = Employee::where('employment_status', 'Active')
                        ->whereHas('jobTitle', fn($q) => $q->where('title', 'like', '%Agent%'))
                        ->get();

        if ($saleTypes->isEmpty() || $agents->isEmpty()) return;

        $statuses   = ['Submitted', 'Submitted', 'Approved', 'Approved', 'Approved'];
        $customers  = [
            ['name' => 'John Smith',    'phone' => '555-0101'],
            ['name' => 'Mary Johnson',  'phone' => '555-0102'],
            ['name' => 'Robert Davis',  'phone' => '555-0103'],
            ['name' => 'Linda Wilson',  'phone' => '555-0104'],
            ['name' => 'Michael Brown', 'phone' => '555-0105'],
            ['name' => 'Patricia Moore','phone' => '555-0106'],
            ['name' => 'James Taylor',  'phone' => '555-0107'],
            ['name' => 'Jennifer Anderson','phone'=>'555-0108'],
            ['name' => 'William Thomas','phone' => '555-0109'],
            ['name' => 'Barbara Jackson','phone'=> '555-0110'],
        ];

        for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);
            if ($date->isWeekend()) continue;

            // 2–4 sales per day across all agents
            $dailySales = rand(2, 4);
            for ($s = 0; $s < $dailySales; $s++) {
                $agent    = $agents->random();
                $saleType = $saleTypes->random();
                $customer = $customers[array_rand($customers)];
                $status   = $statuses[array_rand($statuses)];

                Sale::create([
                    'employee_id'           => $agent->id,
                    'sale_type_id'          => $saleType->id,
                    'sale_date'             => $date->toDateString(),
                    'customer_name'         => $customer['name'],
                    'customer_phone'        => $customer['phone'],
                    'status'                => $status,
                    'total_points'          => $saleType->total_points,
                    'agent_points'          => $saleType->points_per_agent,
                    'compensation_received' => $status === 'Approved' && $daysAgo > 14 ? (bool)rand(0,1) : false,
                ]);
            }
        }
    }

    // ── Leave Requests ────────────────────────────────────────────

    private function seedLeaveRequests(): void
    {
        $employees = Employee::where('employment_status', 'Active')->get();
        $leaveType = LeaveType::first();
        if (! $leaveType) return;

        $admin = User::where('acc_type', 'admin')->first();

        $requests = [
            ['daysAgo' => 20, 'duration' => 1, 'status' => 'Approved'],
            ['daysAgo' => 15, 'duration' => 2, 'status' => 'Approved'],
            ['daysAgo' => 10, 'duration' => 1, 'status' => 'Rejected'],
            ['daysAgo' =>  5, 'duration' => 3, 'status' => 'Pending'],
            ['daysAgo' =>  2, 'duration' => 1, 'status' => 'Pending'],
        ];

        foreach ($requests as $i => $req) {
            $emp  = $employees->get($i % $employees->count());
            $from = Carbon::today()->subDays($req['daysAgo']);
            $to   = $from->copy()->addDays($req['duration'] - 1);

            LeaveRequest::create([
                'employee_id'   => $emp->id,
                'leave_type_id' => $leaveType->id,
                'date_from'     => $from->toDateString(),
                'date_to'       => $to->toDateString(),
                'total_days'    => $req['duration'],
                'reason'        => 'Personal matter',
                'status'        => $req['status'],
                'reviewed_by'   => $req['status'] !== 'Pending' ? $admin?->id : null,
                'reviewed_at'   => $req['status'] !== 'Pending' ? now() : null,
            ]);
        }
    }

    // ── Tasks & Projects ──────────────────────────────────────────

    private function seedTasks(): void
    {
        $admin = User::where('acc_type', 'admin')->first();
        if (! $admin) return;

        $project = Project::firstOrCreate(['name' => 'Q2 Campaign'], [
            'description' => 'Second quarter sales campaign for Xfinity & AT&T portals.',
            'owner_id'    => $admin->id,
            'status'      => 'active',
            'due_date'    => Carbon::now()->endOfMonth(),
        ]);

        $tasks = [
            ['title' => 'Update agent training materials',   'status' => 'done',        'priority' => 'high'],
            ['title' => 'Review Q1 sales performance',       'status' => 'done',        'priority' => 'high'],
            ['title' => 'Set monthly team targets',          'status' => 'in_progress', 'priority' => 'high'],
            ['title' => 'Prepare payroll for April',         'status' => 'in_progress', 'priority' => 'medium'],
            ['title' => 'Audit pending compensation sales',  'status' => 'todo',        'priority' => 'medium'],
            ['title' => 'Onboard 2 new agents',              'status' => 'todo',        'priority' => 'low'],
            ['title' => 'Schedule team performance reviews', 'status' => 'review',      'priority' => 'medium'],
        ];

        foreach ($tasks as $t) {
            Task::firstOrCreate(['title' => $t['title'], 'project_id' => $project->id], [
                'project_id'  => $project->id,
                'created_by'  => $admin->id,
                'title'       => $t['title'],
                'status'      => $t['status'],
                'priority'    => $t['priority'],
                'due_date'    => Carbon::now()->addDays(rand(3, 20)),
            ]);
        }
    }
}
