<?php

namespace Database\Seeders;

use App\Models\Performance\KpiDefinition;
use App\Models\Report\SavedReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Prepares a fresh BIMS installation for a Jamaican call-centre client.
 *
 * Safe to run on a WIP database — uses updateOrCreate / insertOrIgnore.
 *
 * Run with:
 *   php artisan db:seed --class=JamaicaCallCenterSeeder
 *
 * What it does:
 *   1. Seeds Jamaican statutory deductions (NIS, NHT, Ed Tax, PAYE, Heart Trust)
 *   2. Seeds KPI definitions for a sales call centre
 *   3. Seeds call-centre focused saved reports
 *
 * Sale types are intentionally omitted — clients define these via Admin > Sale Types.
 *
 * Run after the base seeder on a fresh client database:
 *   php artisan migrate
 *   php artisan db:seed
 *   php artisan db:seed --class=JamaicaCallCenterSeeder
 */
class JamaicaCallCenterSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedJamaicaTaxConfigs();
        $this->seedKpiDefinitions();
        $this->seedReports();

        $this->command?->info('JamaicaCallCenterSeeder complete.');
    }

    // ── 1. Jamaica statutory tax / deduction configurations ───────────────────

    private function seedJamaicaTaxConfigs(): void
    {
        // Jamaica 2025/2026 statutory rates.
        // income_threshold is monthly; PAYE threshold ≈ J$125,008/month (annual J$1,500,096).
        // calculate() applies rate to full gross when gross > threshold (standard simplified treatment).

        $configs = [

            // ── National Insurance Scheme (NIS) ──────────────────────────────
            [
                'name'                    => 'NIS (Employee)',
                'code'                    => 'NIS_EE',
                'rate'                    => 0.0300,    // 3% of gross
                'flat_amount'             => null,
                'applies_to'              => 'gross',
                'income_threshold'        => null,
                'is_employer_contribution' => false,
                'is_active'               => true,
            ],
            [
                'name'                    => 'NIS (Employer)',
                'code'                    => 'NIS_ER',
                'rate'                    => 0.0300,    // 3% of gross
                'flat_amount'             => null,
                'applies_to'              => 'gross',
                'income_threshold'        => null,
                'is_employer_contribution' => true,
                'is_active'               => true,
            ],

            // ── National Housing Trust (NHT) ─────────────────────────────────
            [
                'name'                    => 'NHT (Employee)',
                'code'                    => 'NHT_EE',
                'rate'                    => 0.0200,    // 2% of gross
                'flat_amount'             => null,
                'applies_to'              => 'gross',
                'income_threshold'        => null,
                'is_employer_contribution' => false,
                'is_active'               => true,
            ],
            [
                'name'                    => 'NHT (Employer)',
                'code'                    => 'NHT_ER',
                'rate'                    => 0.0300,    // 3% of gross
                'flat_amount'             => null,
                'applies_to'              => 'gross',
                'income_threshold'        => null,
                'is_employer_contribution' => true,
                'is_active'               => true,
            ],

            // ── Education Tax ─────────────────────────────────────────────────
            [
                'name'                    => 'Education Tax (Employee)',
                'code'                    => 'EDTAX_EE',
                'rate'                    => 0.0225,    // 2.25% of taxable income
                'flat_amount'             => null,
                'applies_to'              => 'gross',
                'income_threshold'        => null,
                'is_employer_contribution' => false,
                'is_active'               => true,
            ],
            [
                'name'                    => 'Education Tax (Employer)',
                'code'                    => 'EDTAX_ER',
                'rate'                    => 0.0350,    // 3.5% of gross payroll
                'flat_amount'             => null,
                'applies_to'              => 'gross',
                'income_threshold'        => null,
                'is_employer_contribution' => true,
                'is_active'               => true,
            ],

            // ── PAYE Income Tax ────────────────────────────────────────────────
            // Simplified: 25% on gross income above J$125,008/month threshold.
            // Employees earning below threshold pay no PAYE.
            // (Progressive upper band of 30% on > J$500K/month requires custom logic.)
            [
                'name'                    => 'PAYE Income Tax',
                'code'                    => 'PAYE',
                'rate'                    => 0.2500,    // 25%
                'flat_amount'             => null,
                'applies_to'             => 'gross',
                'income_threshold'        => 125008.00, // J$ — update to match your pay currency
                'is_employer_contribution' => false,
                'is_active'               => true,
            ],

            // ── Heart Trust / NTA ─────────────────────────────────────────────
            // Employer only — 3% of gross payroll.
            [
                'name'                    => 'Heart Trust/NTA (Employer)',
                'code'                    => 'HEART_ER',
                'rate'                    => 0.0300,    // 3%
                'flat_amount'             => null,
                'applies_to'              => 'gross',
                'income_threshold'        => null,
                'is_employer_contribution' => true,
                'is_active'               => true,
            ],
        ];

        foreach ($configs as &$c) {
            $c['created_at'] = now();
            $c['updated_at'] = now();
        }

        DB::table('tax_configurations')->insertOrIgnore($configs);

        $this->command?->info('Seeded ' . count($configs) . ' Jamaica tax configurations.');
    }

    // ── 3. KPI definitions ────────────────────────────────────────────────────

    private function seedKpiDefinitions(): void
    {
        // module_key values: 'sales' | 'attendance'
        // direction: 'up' (higher is better) | 'down' (lower is better)

        $kpis = [

            // Sales
            [
                'name'         => 'Monthly Sales Count',
                'module_key'   => 'sales',
                'metric'       => 'sales_count',
                'target_value' => 40,
                'unit'         => 'sales',
                'direction'    => 'higher_is_better',
                'is_active'    => true,
            ],
            [
                'name'         => 'Monthly Agent Points',
                'module_key'   => 'sales',
                'metric'       => 'agent_points_sum',
                'target_value' => 4000,
                'unit'         => 'pts',
                'direction'    => 'higher_is_better',
                'is_active'    => true,
            ],
            [
                'name'         => 'Approval Rate',
                'module_key'   => 'sales',
                'metric'       => 'approval_rate',
                'target_value' => 85,
                'unit'         => '%',
                'direction'    => 'higher_is_better',
                'is_active'    => true,
            ],
            [
                'name'         => 'Cancellation Rate',
                'module_key'   => 'sales',
                'metric'       => 'cancellation_rate',
                'target_value' => 10,
                'unit'         => '%',
                'direction'    => 'lower_is_better',
                'is_active'    => true,
            ],
            [
                'name'         => 'Daily Sales Average',
                'module_key'   => 'sales',
                'metric'       => 'daily_sales_avg',
                'target_value' => 2,
                'unit'         => 'sales/day',
                'direction'    => 'higher_is_better',
                'is_active'    => true,
            ],

            // Attendance
            [
                'name'         => 'Attendance Rate',
                'module_key'   => 'attendance',
                'metric'       => 'attendance_rate',
                'target_value' => 95,
                'unit'         => '%',
                'direction'    => 'higher_is_better',
                'is_active'    => true,
            ],
            [
                'name'         => 'Punctuality Rate',
                'module_key'   => 'attendance',
                'metric'       => 'punctuality_rate',
                'target_value' => 90,
                'unit'         => '%',
                'direction'    => 'higher_is_better',
                'is_active'    => true,
            ],
            [
                'name'         => 'Average Hours Worked',
                'module_key'   => 'attendance',
                'metric'       => 'avg_hours_worked',
                'target_value' => 8,
                'unit'         => 'hrs/day',
                'direction'    => 'higher_is_better',
                'is_active'    => true,
            ],
        ];

        foreach ($kpis as $data) {
            KpiDefinition::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command?->info('Seeded ' . count($kpis) . ' KPI definitions.');
    }

    // ── 4. Call-centre saved reports ─────────────────────────────────────────

    private function seedReports(): void
    {
        $userId = DB::table('users')->value('id');

        $reports = [

            [
                'name'            => 'Agent Sales Leaderboard',
                'description'     => 'Total approved agent points per employee — ranked highest to lowest.',
                'data_source'     => 'sales',
                'columns'         => ['employee_name', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'employee_name',
                'aggregate_field' => 'agent_points',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Campaign Performance',
                'description'     => 'Approved sales count and points broken down by portal / campaign.',
                'data_source'     => 'sales',
                'columns'         => ['sale_type', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'sale_type',
                'aggregate_field' => 'count',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Daily Sales Volume',
                'description'     => 'Number of sales submitted each day — spot spikes or drop-offs.',
                'data_source'     => 'sales',
                'columns'         => ['sale_date', 'agent_points'],
                'filters'         => [],
                'group_by'        => 'sale_date',
                'aggregate_field' => 'count',
                'chart_type'      => 'line',
            ],

            [
                'name'            => 'Cancellation Analysis',
                'description'     => 'Count of cancelled sales — helps identify quality issues.',
                'data_source'     => 'sales',
                'columns'         => ['employee_name', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Cancelled']],
                'group_by'        => 'employee_name',
                'aggregate_field' => 'count',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Team Points Summary',
                'description'     => 'Total approved agent points per team this month.',
                'data_source'     => 'sales',
                'columns'         => ['team_name', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'team_name',
                'aggregate_field' => 'agent_points',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Sales Register — All',
                'description'     => 'Full row-level log of all sales with agent, type, date, status, and points.',
                'data_source'     => 'sales',
                'columns'         => ['sale_date', 'customer_name', 'employee_name', 'team_name', 'sale_type', 'agent_points', 'status'],
                'filters'         => [],
                'group_by'        => null,
                'aggregate_field' => null,
                'chart_type'      => 'table',
            ],

            [
                'name'            => 'Payroll Cost by Period',
                'description'     => 'Total gross and net payroll per pay period — tracks labour cost over time.',
                'data_source'     => 'payroll_slips',
                'columns'         => ['period_label', 'gross_salary', 'net_pay'],
                'filters'         => [['field' => 'run_status', 'op' => '=', 'value' => 'finalized']],
                'group_by'        => 'period_label',
                'aggregate_field' => 'gross_salary',
                'chart_type'      => 'line',
            ],

            [
                'name'            => 'Agent Hours vs Points',
                'description'     => 'Hours worked and sales points side-by-side per employee.',
                'data_source'     => 'employees',
                'columns'         => ['full_name', 'department_name', 'employment_type', 'hire_date'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Active']],
                'group_by'        => 'department_name',
                'aggregate_field' => 'count',
                'chart_type'      => 'table',
            ],

            [
                'name'            => 'Punctuality Report',
                'description'     => 'Late arrivals per employee — supports coaching conversations.',
                'data_source'     => 'attendance',
                'columns'         => ['log_date', 'employee_name', 'status_in', 'reason'],
                'filters'         => [['field' => 'status_in', 'op' => '=', 'value' => 'Late In']],
                'group_by'        => null,
                'aggregate_field' => null,
                'chart_type'      => 'table',
            ],

        ];

        foreach ($reports as $data) {
            SavedReport::updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['created_by' => $userId])
            );
        }

        $this->command?->info('Seeded ' . count($reports) . ' call-centre reports.');
    }
}
