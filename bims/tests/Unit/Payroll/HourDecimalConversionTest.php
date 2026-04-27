<?php

use App\Models\Attendance\AttendanceLog;

// The core regression: "8h 30m" must be 8.5 decimal hours, never 8.3
test('510 minutes converts to 8.5 decimal hours', function () {
    $log = new AttendanceLog(['total_minutes' => 510]);
    expect($log->decimal_hours)->toBe(8.5);
});

test('480 minutes converts to 8.0 decimal hours', function () {
    $log = new AttendanceLog(['total_minutes' => 480]);
    expect($log->decimal_hours)->toBe(8.0);
});

test('90 minutes converts to 1.5 decimal hours', function () {
    $log = new AttendanceLog(['total_minutes' => 90]);
    expect($log->decimal_hours)->toBe(1.5);
});

test('495 minutes converts to 8.25 decimal hours', function () {
    $log = new AttendanceLog(['total_minutes' => 495]);
    expect($log->decimal_hours)->toBe(8.25);
});

test('null total_minutes returns 0.0 decimal hours', function () {
    $log = new AttendanceLog(['total_minutes' => null]);
    expect($log->decimal_hours)->toBe(0.0);
});

test('decimal_hours is never calculated as raw string division (8.30 bug)', function () {
    // 8h 30m = 510 minutes.  If someone divides "8.30" / 60 they get 0.138.
    // The correct path is: total_minutes / 60 = 510 / 60 = 8.5.
    $log = new AttendanceLog(['total_minutes' => 510]);
    expect($log->decimal_hours)->not->toBe(8.3);
    expect($log->decimal_hours)->not->toBe(0.138);
    expect($log->decimal_hours)->toBe(8.5);
});

test('duration accessor returns human readable string', function () {
    $log = new AttendanceLog(['total_minutes' => 510]);
    expect($log->duration)->toBe('8h 30m');
});

test('duration accessor returns Active for null clock_out', function () {
    $log = new AttendanceLog(['total_minutes' => null]);
    expect($log->duration)->toBe('Active');
});
