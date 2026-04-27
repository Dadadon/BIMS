<?php

use App\Models\Attendance\AttendanceLog;
use App\Models\HR\Employee;
use App\Services\Attendance\AttendanceService;

test('50 employees can clock in without duplicate open logs', function () {
    $employees = Employee::factory()->count(50)->create();
    $service   = app(AttendanceService::class);
    $errors    = [];

    foreach ($employees as $employee) {
        try {
            $service->clockIn($employee, 'Shift', null, 'test');
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    expect($errors)->toBeEmpty();

    foreach ($employees as $employee) {
        $openCount = AttendanceLog::where('employee_id', $employee->id)
                                  ->whereNull('clock_out')
                                  ->count();
        expect($openCount)->toBe(1, "Employee {$employee->id} has {$openCount} open logs");
    }

    expect(AttendanceLog::whereNull('clock_out')->count())->toBe(50);
});

test('second clock-in for the same employee throws RuntimeException', function () {
    $employee = Employee::factory()->create();
    $service  = app(AttendanceService::class);

    $service->clockIn($employee, 'Shift', null, 'test');

    expect(fn() => $service->clockIn($employee, 'Shift', null, 'test'))
        ->toThrow(\RuntimeException::class, 'Employee has an open clock-in');
});

test('clock-in followed by clock-out records correct total_minutes', function () {
    $employee = Employee::factory()->create();
    $service  = app(AttendanceService::class);

    $clockIn = now()->subMinutes(510);
    AttendanceLog::create([
        'employee_id' => $employee->id,
        'log_date'    => $clockIn->toDateString(),
        'clock_in'    => $clockIn,
        'clock_out'   => null,
        'reason'      => 'Shift',
        'logged_by'   => 'test',
    ]);

    $log = $service->clockOut($employee, 'Shift', 'test');

    expect($log->total_minutes)->toBeGreaterThanOrEqual(509);
    expect($log->decimal_hours)->toBeGreaterThanOrEqual(8.48);
});

test('50 employees clock in and out without data corruption', function () {
    $service   = app(AttendanceService::class);
    $employees = Employee::factory()->count(50)->create();

    foreach ($employees as $employee) {
        $service->clockIn($employee, 'Shift', null, 'test');
    }

    AttendanceLog::whereNull('clock_out')->update([
        'clock_in' => now()->subMinutes(480),
    ]);

    foreach ($employees as $employee) {
        $service->clockOut($employee, 'Shift', 'test');
    }

    expect(AttendanceLog::whereNull('clock_out')->count())->toBe(0);

    $invalidLogs = AttendanceLog::whereNull('total_minutes')->count();
    expect($invalidLogs)->toBe(0);

    $negativeLogs = AttendanceLog::where('total_minutes', '<=', 0)->count();
    expect($negativeLogs)->toBe(0);
});
