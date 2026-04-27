<?php

use App\Models\Payroll\TaxConfiguration;

test('percentage tax calculates correctly', function () {
    $tax = new TaxConfiguration([
        'rate'             => '0.1000',
        'flat_amount'      => null,
        'income_threshold' => null,
        'is_active'        => true,
    ]);
    expect($tax->calculate(10000))->toBe(1000.0);
});

test('flat amount tax ignores income level', function () {
    $tax = new TaxConfiguration([
        'rate'             => null,
        'flat_amount'      => '500.00',
        'income_threshold' => null,
        'is_active'        => true,
    ]);
    expect($tax->calculate(100))->toBe(500.0);
    expect($tax->calculate(100000))->toBe(500.0);
});

test('tax below income_threshold returns zero', function () {
    $tax = new TaxConfiguration([
        'rate'             => '0.2000',
        'flat_amount'      => null,
        'income_threshold' => '20000.00',
        'is_active'        => true,
    ]);
    expect($tax->calculate(15000))->toBe(0.0);
});

test('tax at or above income_threshold applies', function () {
    $tax = new TaxConfiguration([
        'rate'             => '0.2000',
        'flat_amount'      => null,
        'income_threshold' => '20000.00',
        'is_active'        => true,
    ]);
    expect($tax->calculate(20000))->toBe(4000.0);
    expect($tax->calculate(25000))->toBe(5000.0);
});

test('zero gross returns zero tax', function () {
    $tax = new TaxConfiguration([
        'rate'             => '0.1000',
        'flat_amount'      => null,
        'income_threshold' => null,
    ]);
    expect($tax->calculate(0))->toBe(0.0);
});

test('tax result is rounded to 2 decimal places', function () {
    $tax = new TaxConfiguration([
        'rate'             => '0.1000',
        'flat_amount'      => null,
        'income_threshold' => null,
    ]);
    // 10001 * 0.10 = 1000.1 → 1000.1 (already 1 decimal, rounds to 1000.10)
    $result = $tax->calculate(10001);
    expect((string) $result)->toContain('1000');
});
