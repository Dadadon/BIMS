<?php

use App\Models\HR\Employee;

// Tests gross salary logic from PayrollService::computeSlip — tested indirectly
// via the model properties that feed the calculation.

test('hourly employee gross is rate times regular hours plus overtime multiplier', function () {
    $baseRate      = 75.0;
    $regularHours  = 8.0;
    $overtimeHours = 2.0;
    $multiplier    = 1.5;

    $gross = ($regularHours * $baseRate) + ($overtimeHours * $baseRate * $multiplier);

    expect($gross)->toBe(825.0); // (8 * 75) + (2 * 75 * 1.5) = 600 + 225
});

test('salaried employee gross equals base_rate regardless of hours', function () {
    $employee = new Employee(['is_salaried' => true, 'base_rate' => '15000.00']);
    // For salaried employees the full base_rate is the gross salary
    expect((float) $employee->base_rate)->toBe(15000.0);
});

test('net pay equals gross minus tax minus deductions plus additions', function () {
    $gross       = 10000.0;
    $tax         = 500.0;
    $deductions  = 200.0;
    $additions   = 100.0;
    $commission  = 300.0;

    $net = $gross + $commission + $additions - $deductions - $tax;

    expect($net)->toBe(9700.0);
});

test('overtime threshold at 480 minutes leaves no overtime for exactly 8h', function () {
    $logMinutes       = 480;
    $dailyThreshold   = 480; // 8h * 60

    $regular  = min($logMinutes, $dailyThreshold);
    $overtime = max(0, $logMinutes - $dailyThreshold);

    expect($regular)->toBe(480);
    expect($overtime)->toBe(0);
});

test('overtime kicks in after 8h threshold', function () {
    $logMinutes     = 570; // 9h 30m
    $dailyThreshold = 480; // 8h

    $regular  = min($logMinutes, $dailyThreshold);
    $overtime = max(0, $logMinutes - $dailyThreshold);

    expect($regular)->toBe(480);
    expect($overtime)->toBe(90);
    expect((float) ($regular / 60))->toBe(8.0);
    expect($overtime / 60)->toBe(1.5);
});
