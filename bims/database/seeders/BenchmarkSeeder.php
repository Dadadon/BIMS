<?php

namespace Database\Seeders;

use App\Models\Attendance\AttendanceLog;
use App\Models\HR\Company;
use App\Models\HR\Employee;
use App\Models\Role;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Benchmark seeder for load and performance testing.
 *
 * Run with:
 *   php artisan db:seed --class=BenchmarkSeeder
 *
 * Generates:
 *   - 1 shared company
 *   - 120 active employees with linked user accounts
 *   - 1,440 attendance logs (12 per employee over 60 working days)
 *   - 600 confirmed sales (5 per employee)
 */
class BenchmarkSeeder extends Seeder
{
    private const EMPLOYEE_COUNT   = 120;
    private const LOGS_PER_EMPLOYEE = 12;
    private const SALES_PER_EMPLOYEE = 5;

    public function run(): void
    {
        $this->command->info('BenchmarkSeeder: generating ' . self::EMPLOYEE_COUNT . ' employees...');

        $company   = $this->getOrCreateCompany();
        $role      = $this->getEmployeeRole();
        $saleType  = $this->getOrCreateSaleType();

        $bar = $this->command->getOutput()->createProgressBar(self::EMPLOYEE_COUNT);
        $bar->start();

        foreach (range(1, self::EMPLOYEE_COUNT) as $i) {
            $employee = $this->createEmployee($company, $i);
            $this->createUser($employee, $role, $i);
            $this->createAttendanceLogs($employee);
            $this->createSales($employee, $saleType);
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Done. Employees: ' . Employee::count() . ' | Logs: ' . AttendanceLog::count() . ' | Sales: ' . Sale::count());
    }

    private function getOrCreateCompany(): Company
    {
        return Company::firstOrCreate(
            ['name' => 'Benchmark Corp'],
            ['commission_model' => 'sale_type_rate', 'commission_rate' => 0, 'is_primary' => false]
        );
    }

    private function getEmployeeRole(): Role
    {
        return Role::where('slug', 'employee')->firstOrFail();
    }

    private function getOrCreateSaleType(): SaleType
    {
        return SaleType::firstOrCreate(
            ['product_code' => 'BENCH-001'],
            [
                'product_category' => 'Internet',
                'portal'           => 'Benchmark',
                'total_points'     => 100,
                'points_per_agent' => 50,
                'is_active'        => true,
            ]
        );
    }

    private function createEmployee(Company $company, int $index): Employee
    {
        $firstNames = ['Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Rosa', 'Carlos', 'Elena', 'Miguel', 'Sofia'];
        $lastNames  = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Torres', 'Flores', 'Rivera', 'Gomez', 'Lopez', 'Diaz'];

        $first = $firstNames[$index % count($firstNames)];
        $last  = $lastNames[intdiv($index, count($firstNames)) % count($lastNames)] . $index;

        return Employee::create([
            'company_id'        => $company->id,
            'employee_code'     => 'BNC-' . str_pad($index, 5, '0', STR_PAD_LEFT),
            'firstname'         => $first,
            'lastname'          => $last,
            'email'             => "bench.{$index}@benchmark.test",
            'employment_type'   => 'Regular',
            'employment_status' => 'Active',
            'is_salaried'       => $index % 5 === 0,
            'base_rate'         => $index % 5 === 0 ? 15000 : 75.00,
            'start_date'        => Carbon::now()->subYears(2)->subDays($index)->toDateString(),
        ]);
    }

    private function createUser(Employee $employee, Role $role, int $index): void
    {
        User::create([
            'role_id'           => $role->id,
            'employee_id'       => $employee->id,
            'name'              => $employee->fullname,
            'email'             => "bench.{$index}@benchmark.test",
            'email_verified_at' => now(),
            'password'          => Hash::make('benchmark'),
            'acc_type'          => 'employee',
            'status'            => true,
        ]);
    }

    private function createAttendanceLogs(Employee $employee): void
    {
        $base = Carbon::now()->subDays(60);

        for ($day = 0; $day < self::LOGS_PER_EMPLOYEE; $day++) {
            $date    = $base->copy()->addDays($day * 5); // every 5 workdays
            $minutes = random_int(420, 570);             // 7h to 9.5h
            $clockIn = $date->copy()->setTime(8, random_int(0, 30));

            AttendanceLog::create([
                'employee_id'   => $employee->id,
                'log_date'      => $date->toDateString(),
                'clock_in'      => $clockIn,
                'clock_out'     => $clockIn->copy()->addMinutes($minutes),
                'total_minutes' => $minutes,
                'reason'        => 'Shift',
                'status_in'     => $minutes >= 480 ? 'In Time' : 'Late In',
                'status_out'    => 'On Time',
                'is_approved'   => true,
                'logged_by'     => 'BenchmarkSeeder',
            ]);
        }
    }

    private function createSales(Employee $employee, SaleType $saleType): void
    {
        for ($i = 0; $i < self::SALES_PER_EMPLOYEE; $i++) {
            Sale::create([
                'employee_id'           => $employee->id,
                'sale_type_id'          => $saleType->id,
                'customer_name'         => "Customer {$employee->id}-{$i}",
                'customer_phone'        => '09' . str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
                'sale_date'             => Carbon::now()->subDays(random_int(1, 30))->toDateString(),
                'total_points'          => 100,
                'agent_points'          => 50,
                'status'                => 'Confirmed',
                'compensation_received' => true,
            ]);
        }
    }
}
