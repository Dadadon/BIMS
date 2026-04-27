<?php

use App\Events\SaleRecorded;
use App\Models\HR\Employee;
use App\Models\Performance\KpiDefinition;
use App\Models\Performance\KpiSnapshot;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleType;
use Carbon\Carbon;

// Verify that when a Sale is created and SaleRecorded is fired,
// KpiSnapshot records are upserted for the employee's sales KPIs.

test('SaleRecorded event triggers KPI snapshot for employee', function () {
    $employee = Employee::factory()->create(['employment_status' => 'Active']);
    $saleType = SaleType::factory()->create();

    // Create a sales KPI definition and assign to the employee
    $kpi = KpiDefinition::create([
        'name'         => 'Monthly Sales Count',
        'module_key'   => 'sales',
        'metric'       => 'sale_count',
        'target_value' => 20,
        'unit'         => 'sales',
        'direction'    => 'higher_is_better',
        'is_active'    => true,
    ]);
    $employee->kpiDefinitions()->attach($kpi->id);

    $sale = Sale::create([
        'employee_id'           => $employee->id,
        'sale_type_id'          => $saleType->id,
        'customer_name'         => 'Test Customer',
        'sale_date'             => now()->toDateString(),
        'total_points'          => 100,
        'agent_points'          => 50,
        'status'                => 'Confirmed',
        'compensation_received' => false,
    ]);

    // Fire the event (normally done by ClockController::logSale)
    event(new SaleRecorded($sale));

    // A KPI snapshot should now exist for this employee + KPI for the current month
    $snapshot = KpiSnapshot::where('employee_id', $employee->id)
                            ->where('kpi_id', $kpi->id)
                            ->first();

    expect($snapshot)->not->toBeNull();
    expect((float) $snapshot->value)->toBeGreaterThanOrEqual(1.0);
});

test('second sale in same period upserts the same KPI snapshot row', function () {
    $employee = Employee::factory()->create(['employment_status' => 'Active']);
    $saleType = SaleType::factory()->create();

    $kpi = KpiDefinition::create([
        'name'         => 'Monthly Agent Points',
        'module_key'   => 'sales',
        'metric'       => 'total_agent_points',
        'target_value' => 1000,
        'unit'         => 'pts',
        'direction'    => 'higher_is_better',
        'is_active'    => true,
    ]);
    $employee->kpiDefinitions()->attach($kpi->id);

    $periodStart = Carbon::now()->startOfMonth()->toDateString();
    $periodEnd   = Carbon::now()->endOfMonth()->toDateString();

    // First sale
    $sale1 = Sale::create([
        'employee_id'           => $employee->id,
        'sale_type_id'          => $saleType->id,
        'customer_name'         => 'Alice',
        'sale_date'             => now()->toDateString(),
        'total_points'          => 100,
        'agent_points'          => 50,
        'status'                => 'Confirmed',
        'compensation_received' => false,
    ]);
    event(new SaleRecorded($sale1));

    $firstSnapshotCount = KpiSnapshot::where('employee_id', $employee->id)
                                     ->where('kpi_id', $kpi->id)
                                     ->count();

    // Second sale — should upsert, not insert a new row
    $sale2 = Sale::create([
        'employee_id'           => $employee->id,
        'sale_type_id'          => $saleType->id,
        'customer_name'         => 'Bob',
        'sale_date'             => now()->toDateString(),
        'total_points'          => 200,
        'agent_points'          => 100,
        'status'                => 'Confirmed',
        'compensation_received' => false,
    ]);
    event(new SaleRecorded($sale2));

    $secondSnapshotCount = KpiSnapshot::where('employee_id', $employee->id)
                                      ->where('kpi_id', $kpi->id)
                                      ->count();

    expect($firstSnapshotCount)->toBe(1);
    expect($secondSnapshotCount)->toBe(1); // same row, not two rows
});

test('KPI snapshot value reflects cumulative sales for the period', function () {
    $employee = Employee::factory()->create(['employment_status' => 'Active']);
    $saleType = SaleType::factory()->create();

    $kpi = KpiDefinition::create([
        'name'         => 'Sale Count',
        'module_key'   => 'sales',
        'metric'       => 'sale_count',
        'target_value' => 10,
        'unit'         => 'count',
        'direction'    => 'higher_is_better',
        'is_active'    => true,
    ]);
    $employee->kpiDefinitions()->attach($kpi->id);

    // Create 3 sales and fire the event each time
    foreach (range(1, 3) as $i) {
        $sale = Sale::create([
            'employee_id'           => $employee->id,
            'sale_type_id'          => $saleType->id,
            'customer_name'         => "Customer $i",
            'sale_date'             => now()->toDateString(),
            'total_points'          => 100,
            'agent_points'          => 50,
            'status'                => 'Confirmed',
            'compensation_received' => false,
        ]);
        event(new SaleRecorded($sale));
    }

    $snapshot = KpiSnapshot::where('employee_id', $employee->id)
                            ->where('kpi_id', $kpi->id)
                            ->first();

    expect((float) $snapshot->value)->toBe(3.0);
});
