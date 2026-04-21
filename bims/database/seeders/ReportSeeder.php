<?php

namespace Database\Seeders;

use App\Models\Report\SavedReport;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reports = [

            // ── Sales ──────────────────────────────────────────────────────

            [
                'name'            => 'Agent Points by Team',
                'description'     => 'Total agent points earned per team — good for leaderboard comparisons.',
                'data_source'     => 'sales',
                'columns'         => ['team_name', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'team_name',
                'aggregate_field' => 'agent_points',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Monthly Sales Trend',
                'description'     => 'Total agent points per month — tracks growth over time.',
                'data_source'     => 'sales',
                'columns'         => ['sale_month', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'sale_month',
                'aggregate_field' => 'agent_points',
                'chart_type'      => 'line',
            ],

            [
                'name'            => 'Sales by Product Type',
                'description'     => 'Count of approved sales broken down by product category.',
                'data_source'     => 'sales',
                'columns'         => ['sale_type', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'sale_type',
                'aggregate_field' => 'count',
                'chart_type'      => 'pie',
            ],

            [
                'name'            => 'Sales Distribution by Status',
                'description'     => 'How sales are split across Approved, Submitted, Cancelled statuses.',
                'data_source'     => 'sales',
                'columns'         => ['status', 'agent_points'],
                'filters'         => [],
                'group_by'        => 'status',
                'aggregate_field' => 'count',
                'chart_type'      => 'doughnut',
            ],

            [
                'name'            => 'Top Sales by Employee',
                'description'     => 'Total approved agent points per employee — individual performance ranking.',
                'data_source'     => 'sales',
                'columns'         => ['employee_name', 'agent_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'employee_name',
                'aggregate_field' => 'agent_points',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Bonus Points by Employee',
                'description'     => 'Sum of calculated Bonus Points (agent_points × 10%) per employee.',
                'data_source'     => 'sales',
                'columns'         => ['employee_name', 'cf_bonus_points'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'employee_name',
                'aggregate_field' => 'cf_bonus_points_sum',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Performance Score by Employee',
                'description'     => 'Average of the calculated Performance Score field per employee.',
                'data_source'     => 'sales',
                'columns'         => ['employee_name', 'cf_performance_score'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => 'employee_name',
                'aggregate_field' => 'cf_performance_score_avg',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Full Sales Register',
                'description'     => 'Row-level export of all approved sales with employee, type, date, and points.',
                'data_source'     => 'sales',
                'columns'         => ['sale_date', 'customer_name', 'employee_name', 'team_name', 'sale_type', 'agent_points', 'status'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Approved']],
                'group_by'        => null,
                'aggregate_field' => null,
                'chart_type'      => 'table',
            ],

            // ── Payroll ────────────────────────────────────────────────────

            [
                'name'            => 'Net Pay by Employee',
                'description'     => 'Total net pay per employee across all finalized payroll runs.',
                'data_source'     => 'payroll_slips',
                'columns'         => ['employee_name', 'net_pay'],
                'filters'         => [['field' => 'run_status', 'op' => '=', 'value' => 'finalized']],
                'group_by'        => 'employee_name',
                'aggregate_field' => 'net_pay',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Gross vs Net by Pay Period',
                'description'     => 'Total gross pay per pay period — tracks payroll cost over time.',
                'data_source'     => 'payroll_slips',
                'columns'         => ['period_label', 'gross_salary', 'net_pay'],
                'filters'         => [['field' => 'run_status', 'op' => '=', 'value' => 'finalized']],
                'group_by'        => 'period_label',
                'aggregate_field' => 'gross_salary',
                'chart_type'      => 'line',
            ],

            [
                'name'            => 'Payroll Slip Detail',
                'description'     => 'Full payslip breakdown per employee per period — use for audits.',
                'data_source'     => 'payroll_slips',
                'columns'         => ['period_label', 'employee_name', 'gross_salary', 'total_additions', 'total_deductions', 'total_tax', 'commission_earned', 'net_pay'],
                'filters'         => [['field' => 'run_status', 'op' => '=', 'value' => 'finalized']],
                'group_by'        => null,
                'aggregate_field' => null,
                'chart_type'      => 'table',
            ],

            // ── Attendance ─────────────────────────────────────────────────

            [
                'name'            => 'Clock-In Punctuality Breakdown',
                'description'     => 'Count of In Time vs Late In records — punctuality overview.',
                'data_source'     => 'attendance',
                'columns'         => ['status_in', 'total_minutes'],
                'filters'         => [],
                'group_by'        => 'status_in',
                'aggregate_field' => 'count',
                'chart_type'      => 'doughnut',
            ],

            [
                'name'            => 'Hours Worked by Employee',
                'description'     => 'Total hours worked per employee across all logged attendance.',
                'data_source'     => 'attendance',
                'columns'         => ['employee_name', 'total_hours'],
                'filters'         => [],
                'group_by'        => 'employee_name',
                'aggregate_field' => 'total_minutes',
                'chart_type'      => 'bar',
            ],

            [
                'name'            => 'Monthly Hours Trend',
                'description'     => 'Total minutes worked per month — spot dips or spikes in staffing.',
                'data_source'     => 'attendance',
                'columns'         => ['log_month', 'total_minutes'],
                'filters'         => [],
                'group_by'        => 'log_month',
                'aggregate_field' => 'total_minutes',
                'chart_type'      => 'area',
            ],

            [
                'name'            => 'Late Arrivals Log',
                'description'     => 'All Late In records with employee name and date.',
                'data_source'     => 'attendance',
                'columns'         => ['log_date', 'employee_name', 'status_in', 'reason'],
                'filters'         => [['field' => 'status_in', 'op' => '=', 'value' => 'Late In']],
                'group_by'        => null,
                'aggregate_field' => null,
                'chart_type'      => 'table',
            ],

            // ── Employees ──────────────────────────────────────────────────

            [
                'name'            => 'Headcount by Department',
                'description'     => 'Number of active employees per department.',
                'data_source'     => 'employees',
                'columns'         => ['department_name'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Active']],
                'group_by'        => 'department_name',
                'aggregate_field' => 'count',
                'chart_type'      => 'pie',
            ],

            [
                'name'            => 'Headcount by Employment Type',
                'description'     => 'Split of Regular vs Contractual vs Part-time employees.',
                'data_source'     => 'employees',
                'columns'         => ['employment_type'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Active']],
                'group_by'        => 'employment_type',
                'aggregate_field' => 'count',
                'chart_type'      => 'doughnut',
            ],

            [
                'name'            => 'Employee Directory',
                'description'     => 'Full list of active employees with contact and role details.',
                'data_source'     => 'employees',
                'columns'         => ['full_name', 'email', 'department_name', 'job_title_name', 'team_name', 'employment_type', 'hire_date'],
                'filters'         => [['field' => 'status', 'op' => '=', 'value' => 'Active']],
                'group_by'        => null,
                'aggregate_field' => null,
                'chart_type'      => 'table',
            ],

        ];

        $userId = \DB::table('users')->value('id');

        foreach ($reports as $data) {
            SavedReport::updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['created_by' => $userId])
            );
        }

        $this->command->info('Seeded ' . count($reports) . ' demo reports.');
    }
}
