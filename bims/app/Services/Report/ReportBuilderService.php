<?php

namespace App\Services\Report;

use App\Models\Report\SavedReport;
use Illuminate\Support\Facades\DB;

class ReportBuilderService
{
    private ?array $sourcesCache = null;

    // ── Schema definition ──────────────────────────────────────────────────

    protected function sources(): array
    {
        if ($this->sourcesCache !== null) return $this->sourcesCache;

        // Load custom fields once
        $saleCustomCols  = $this->customSaleColumns();
        $empCustomCols   = $this->customEmployeeColumns();
        $saleCustomAggs  = $this->customSaleAggregates();

        $this->sourcesCache = [
            'sales' => [
                'label' => 'Sales',
                'table' => 'sales',
                'joins' => [
                    ['leftJoin', 'employees', 'employees.id', '=', 'sales.employee_id'],
                    ['leftJoin', 'teams',     'teams.id',     '=', 'sales.team_id'],
                    ['leftJoin', 'sale_types','sale_types.id','=', 'sales.sale_type_id'],
                ],
                'columns' => array_merge([
                    'customer_name' => ['label' => 'Customer',    'expr' => 'sales.customer_name',                                   'type' => 'string'],
                    'sale_date'     => ['label' => 'Sale Date',   'expr' => 'sales.sale_date',                                       'type' => 'date'],
                    'sale_month'    => ['label' => 'Month',       'expr' => "DATE_FORMAT(sales.sale_date, '%Y-%m')",                 'type' => 'string', 'virtual' => true],
                    'sale_year'     => ['label' => 'Year',        'expr' => "YEAR(sales.sale_date)",                                'type' => 'string', 'virtual' => true],
                    'agent_points'  => ['label' => 'Agent Points','expr' => 'sales.agent_points',                                   'type' => 'number'],
                    'total_points'  => ['label' => 'Total Points','expr' => 'sales.total_points',                                   'type' => 'number'],
                    'status'        => ['label' => 'Status',      'expr' => 'sales.status',                                         'type' => 'string'],
                    'employee_name' => ['label' => 'Employee',    'expr' => "CONCAT(employees.lastname, ', ', employees.firstname)", 'type' => 'string'],
                    'team_name'     => ['label' => 'Team',        'expr' => 'teams.name',                                           'type' => 'string'],
                    'sale_type'     => ['label' => 'Sale Type',   'expr' => 'sale_types.product_category',                           'type' => 'string'],
                ], $saleCustomCols),
                'groupable' => array_merge(
                    ['employee_name', 'team_name', 'sale_type', 'status', 'sale_month', 'sale_year'],
                    // String/select custom fields are groupable
                    array_keys(array_filter($saleCustomCols, fn($c) => $c['type'] === 'string'))
                ),
                'aggregatable' => array_merge([
                    ['key' => 'count',        'label' => 'Count of Sales',       'fn' => 'COUNT', 'field' => '*'],
                    ['key' => 'agent_points', 'label' => 'Total Agent Points',   'fn' => 'SUM',   'field' => 'sales.agent_points'],
                    ['key' => 'avg_points',   'label' => 'Average Agent Points', 'fn' => 'AVG',   'field' => 'sales.agent_points'],
                ], $saleCustomAggs),
                'default_sort' => 'sales.sale_date',
            ],

            'payroll_slips' => [
                'label' => 'Payroll Slips',
                'table' => 'payroll_slips',
                'joins' => [
                    ['leftJoin', 'employees',   'employees.id',   '=', 'payroll_slips.employee_id'],
                    ['leftJoin', 'payroll_runs', 'payroll_runs.id','=', 'payroll_slips.payroll_run_id'],
                    ['leftJoin', 'pay_periods',  'pay_periods.id', '=', 'payroll_runs.pay_period_id'],
                ],
                'columns' => [
                    'employee_name'     => ['label' => 'Employee',     'expr' => "CONCAT(employees.lastname, ', ', employees.firstname)", 'type' => 'string'],
                    'period_label'      => ['label' => 'Pay Period',   'expr' => 'pay_periods.label',                                    'type' => 'string'],
                    'period_start'      => ['label' => 'Period Start', 'expr' => 'pay_periods.start_date',                               'type' => 'date'],
                    'gross_salary'      => ['label' => 'Gross Pay',    'expr' => 'payroll_slips.gross_salary',                           'type' => 'number'],
                    'total_additions'   => ['label' => 'Additions',    'expr' => 'payroll_slips.total_additions',                        'type' => 'number'],
                    'total_deductions'  => ['label' => 'Deductions',   'expr' => 'payroll_slips.total_deductions',                       'type' => 'number'],
                    'total_tax'         => ['label' => 'Tax Withheld', 'expr' => 'payroll_slips.total_tax',                              'type' => 'number'],
                    'commission_earned' => ['label' => 'Commission',   'expr' => 'payroll_slips.commission_earned',                      'type' => 'number'],
                    'net_pay'           => ['label' => 'Net Pay',      'expr' => 'payroll_slips.net_pay',                                'type' => 'number'],
                    'run_status'        => ['label' => 'Run Status',   'expr' => 'payroll_runs.status',                                  'type' => 'string'],
                ],
                'groupable'    => ['employee_name', 'period_label', 'run_status'],
                'aggregatable' => [
                    ['key' => 'count',        'label' => 'Count of Slips',   'fn' => 'COUNT', 'field' => '*'],
                    ['key' => 'gross_salary', 'label' => 'Total Gross Pay',  'fn' => 'SUM',   'field' => 'payroll_slips.gross_salary'],
                    ['key' => 'net_pay',      'label' => 'Total Net Pay',    'fn' => 'SUM',   'field' => 'payroll_slips.net_pay'],
                    ['key' => 'total_tax',    'label' => 'Total Tax',        'fn' => 'SUM',   'field' => 'payroll_slips.total_tax'],
                    ['key' => 'avg_net',      'label' => 'Average Net Pay',  'fn' => 'AVG',   'field' => 'payroll_slips.net_pay'],
                ],
                'default_sort' => 'pay_periods.start_date',
            ],

            'attendance' => [
                'label' => 'Attendance',
                'table' => 'attendance_logs',
                'joins' => [
                    ['leftJoin', 'employees', 'employees.id', '=', 'attendance_logs.employee_id'],
                ],
                'columns' => [
                    'employee_name' => ['label' => 'Employee',        'expr' => "CONCAT(employees.lastname, ', ', employees.firstname)", 'type' => 'string'],
                    'log_date'      => ['label' => 'Date',            'expr' => 'attendance_logs.log_date',                             'type' => 'date'],
                    'log_month'     => ['label' => 'Month',           'expr' => "DATE_FORMAT(attendance_logs.log_date, '%Y-%m')",       'type' => 'string', 'virtual' => true],
                    'total_minutes' => ['label' => 'Minutes Worked',  'expr' => 'attendance_logs.total_minutes',                       'type' => 'number'],
                    'total_hours'   => ['label' => 'Hours Worked',    'expr' => 'ROUND(attendance_logs.total_minutes / 60, 2)',        'type' => 'number', 'virtual' => true],
                    'status_in'     => ['label' => 'Clock-In Status', 'expr' => 'attendance_logs.status_in',                          'type' => 'string'],
                    'status_out'    => ['label' => 'Clock-Out Status','expr' => 'attendance_logs.status_out',                         'type' => 'string'],
                    'reason'        => ['label' => 'Reason',          'expr' => 'attendance_logs.reason',                             'type' => 'string'],
                ],
                'groupable'    => ['employee_name', 'status_in', 'reason', 'log_month'],
                'aggregatable' => [
                    ['key' => 'count',        'label' => 'Count of Records', 'fn' => 'COUNT', 'field' => '*'],
                    ['key' => 'total_minutes','label' => 'Total Minutes',    'fn' => 'SUM',   'field' => 'attendance_logs.total_minutes'],
                    ['key' => 'avg_minutes',  'label' => 'Average Minutes',  'fn' => 'AVG',   'field' => 'attendance_logs.total_minutes'],
                ],
                'default_sort' => 'attendance_logs.log_date',
            ],

            'employees' => [
                'label' => 'Employees',
                'table' => 'employees',
                'joins' => [
                    ['leftJoin', 'departments', 'departments.id', '=', 'employees.department_id'],
                    ['leftJoin', 'job_titles',  'job_titles.id',  '=', 'employees.job_title_id'],
                    ['leftJoin', 'teams',       'teams.id',        '=', 'employees.team_id'],
                ],
                'columns' => array_merge([
                    'full_name'       => ['label' => 'Full Name',       'expr' => "CONCAT(employees.lastname, ', ', employees.firstname)", 'type' => 'string'],
                    'email'           => ['label' => 'Email',           'expr' => 'employees.email',                                      'type' => 'string'],
                    'department_name' => ['label' => 'Department',      'expr' => 'departments.name',                                     'type' => 'string'],
                    'job_title_name'  => ['label' => 'Job Title',       'expr' => 'job_titles.title',                                     'type' => 'string'],
                    'team_name'       => ['label' => 'Team',            'expr' => 'teams.name',                                           'type' => 'string'],
                    'status'          => ['label' => 'Status',          'expr' => 'employees.employment_status',                          'type' => 'string'],
                    'employment_type' => ['label' => 'Employment Type', 'expr' => 'employees.employment_type',                            'type' => 'string'],
                    'hire_date'       => ['label' => 'Hire Date',       'expr' => 'employees.start_date',                                 'type' => 'date'],
                ], $empCustomCols),
                'groupable' => array_merge(
                    ['department_name', 'job_title_name', 'team_name', 'status', 'employment_type'],
                    array_keys(array_filter($empCustomCols, fn($c) => $c['type'] === 'string'))
                ),
                'aggregatable' => [
                    ['key' => 'count', 'label' => 'Headcount', 'fn' => 'COUNT', 'field' => '*'],
                ],
                'default_sort' => 'employees.lastname',
            ],

            'leaves' => [
                'label' => 'Leave Requests',
                'table' => 'leave_requests',
                'joins' => [
                    ['leftJoin', 'employees',   'employees.id',   '=', 'leave_requests.employee_id'],
                    ['leftJoin', 'leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id'],
                ],
                'columns' => [
                    'employee_name'   => ['label' => 'Employee',   'expr' => "CONCAT(employees.lastname, ', ', employees.firstname)", 'type' => 'string'],
                    'leave_type_name' => ['label' => 'Leave Type', 'expr' => 'leave_types.name',                                    'type' => 'string'],
                    'date_from'       => ['label' => 'From Date',  'expr' => 'leave_requests.date_from',                            'type' => 'date'],
                    'date_to'         => ['label' => 'To Date',    'expr' => 'leave_requests.date_to',                              'type' => 'date'],
                    'total_days'      => ['label' => 'Days',       'expr' => 'leave_requests.total_days',                           'type' => 'number'],
                    'status'          => ['label' => 'Status',     'expr' => 'leave_requests.status',                               'type' => 'string'],
                    'reason'          => ['label' => 'Reason',     'expr' => 'leave_requests.reason',                               'type' => 'string'],
                ],
                'groupable'    => ['employee_name', 'leave_type_name', 'status'],
                'aggregatable' => [
                    ['key' => 'count',      'label' => 'Count of Requests', 'fn' => 'COUNT', 'field' => '*'],
                    ['key' => 'total_days', 'label' => 'Total Days',        'fn' => 'SUM',   'field' => 'leave_requests.total_days'],
                ],
                'default_sort' => 'leave_requests.date_from',
            ],
        ];

        return $this->sourcesCache;
    }

    // ── Custom field loaders ───────────────────────────────────────────────

    private function customSaleColumns(): array
    {
        $cols = [];

        $fields = DB::table('sale_field_definitions')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['key', 'label', 'field_type', 'formula']);

        foreach ($fields as $field) {
            $isCalc = $field->field_type === 'calculated';
            $isNum  = in_array($field->field_type, ['number', 'calculated']);

            $expr = $isCalc && $field->formula
                ? $this->formulaToSql($field->formula, 'sales')
                : "JSON_UNQUOTE(JSON_EXTRACT(sales.metadata, '$.\"{$field->key}\"'))";

            $cols['cf_' . $field->key] = [
                'label'      => $field->label,
                'expr'       => $expr,
                'type'       => $isNum ? 'number' : 'string',
                'calculated' => $isCalc,
                'cf_key'     => $field->key,
            ];
        }

        return $cols;
    }

    private function customEmployeeColumns(): array
    {
        $cols = [];

        $fields = DB::table('employee_field_definitions')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['key', 'label', 'field_type']);

        foreach ($fields as $field) {
            $isNum = $field->field_type === 'number';

            $cols['cf_' . $field->key] = [
                'label'  => $field->label,
                'expr'   => "JSON_UNQUOTE(JSON_EXTRACT(employees.metadata, '$.\"{$field->key}\"'))",
                'type'   => $isNum ? 'number' : 'string',
                'cf_key' => $field->key,
            ];
        }

        return $cols;
    }

    private function customSaleAggregates(): array
    {
        $aggs = [];

        $fields = DB::table('sale_field_definitions')
            ->where('is_active', true)
            ->whereIn('field_type', ['number', 'calculated'])
            ->orderBy('sort_order')
            ->get(['key', 'label', 'field_type', 'formula']);

        foreach ($fields as $field) {
            $isCalc = $field->field_type === 'calculated';
            $expr   = $isCalc && $field->formula
                ? $this->formulaToSql($field->formula, 'sales')
                : "CAST(JSON_UNQUOTE(JSON_EXTRACT(sales.metadata, '$.\"{$field->key}\"')) AS DECIMAL(15,4))";

            $aggs[] = [
                'key'   => 'cf_' . $field->key . '_sum',
                'label' => 'Sum of ' . $field->label,
                'fn'    => 'SUM',
                'field' => $expr,
                'raw'   => true,
            ];
            $aggs[] = [
                'key'   => 'cf_' . $field->key . '_avg',
                'label' => 'Avg of ' . $field->label,
                'fn'    => 'AVG',
                'field' => $expr,
                'raw'   => true,
            ];
        }

        return $aggs;
    }

    // ── Formula → SQL translator ───────────────────────────────────────────

    private function formulaToSql(string $formula, string $table): string
    {
        $builtins = [
            'agent_points' => "CAST({$table}.agent_points AS DECIMAL(15,4))",
            'total_points' => "CAST({$table}.total_points AS DECIMAL(15,4))",
            'status'       => "{$table}.status",
        ];

        // SQL function names that should pass through unchanged
        $sqlFunctions = ['POW', 'ROUND', 'ABS', 'FLOOR', 'CEIL', 'CAST', 'COALESCE', 'NULLIF', 'IF', 'DECIMAL'];

        // Step 1: convert ** to POW(a, b) — handles numeric literals and identifiers
        $formula = preg_replace_callback(
            '/(\([^()]*\)|\b[a-zA-Z_]\w*\b|\d+(?:\.\d+)?)\s*\*\*\s*(\([^()]*\)|\b[a-zA-Z_]\w*\b|\d+(?:\.\d+)?)/',
            fn($m) => 'POW(' . $m[1] . ', ' . $m[2] . ')',
            $formula
        );

        // Step 2: replace variable tokens with SQL expressions
        $formula = preg_replace_callback('/\b([a-zA-Z_][a-zA-Z0-9_]*)\b/', function ($m) use ($builtins, $sqlFunctions, $table) {
            $token = $m[1];

            if (in_array(strtoupper($token), $sqlFunctions)) {
                return $token; // pass SQL functions through unchanged
            }

            if (isset($builtins[$token])) {
                return $builtins[$token];
            }

            // Unknown token → treat as a custom field in metadata
            return "CAST(JSON_UNQUOTE(JSON_EXTRACT({$table}.metadata, '$.\"{$token}\"')) AS DECIMAL(15,4))";
        }, $formula);

        return 'CAST((' . $formula . ') AS DECIMAL(15,4))';
    }

    // ── Public API ─────────────────────────────────────────────────────────

    public function schema(): array
    {
        return $this->sources();
    }

    public function schemaForFrontend(): array
    {
        $out = [];
        foreach ($this->sources() as $key => $src) {
            $out[$key] = [
                'label'        => $src['label'],
                'columns'      => array_map(fn($c) => [
                    'label'      => $c['label'],
                    'type'       => $c['type'],
                    'calculated' => $c['calculated'] ?? false,
                ], $src['columns']),
                'groupable'    => $src['groupable'],
                'aggregatable' => $src['aggregatable'],
            ];
        }
        return $out;
    }

    public function run(SavedReport $report, int $limit = 500): array
    {
        $src = $this->sources()[$report->data_source] ?? null;
        if (! $src) return ['rows' => [], 'chart' => null, 'columns' => []];

        $cols  = $src['columns'];
        $query = DB::table($src['table']);

        foreach ($src['joins'] as [$method, $table, $first, $op, $second]) {
            $query->$method($table, $first, $op, $second);
        }

        $this->applyFilters($query, $cols, $report->filters ?? []);

        return $report->isGrouped()
            ? $this->runGrouped($query, $src, $cols, $report, $limit)
            : $this->runRows($query, $src, $cols, $report, $limit);
    }

    // ── Private query helpers ──────────────────────────────────────────────

    private function runRows($query, array $src, array $cols, SavedReport $report, int $limit): array
    {
        $selects = [];
        $headers = [];

        foreach ($report->columns as $colKey) {
            if (! isset($cols[$colKey])) continue;
            $selects[] = DB::raw($cols[$colKey]['expr'] . ' as `' . $colKey . '`');
            $headers[] = ['key' => $colKey, 'label' => $cols[$colKey]['label'], 'type' => $cols[$colKey]['type']];
        }

        if (empty($selects)) $selects = [DB::raw('1')];

        $rows = $query->select($selects)
            ->orderBy(DB::raw($src['default_sort']), 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        return ['rows' => $rows, 'columns' => $headers, 'chart' => null];
    }

    private function runGrouped($query, array $src, array $cols, SavedReport $report, int $limit): array
    {
        $groupKey  = $report->group_by;
        $groupExpr = $cols[$groupKey]['expr'] ?? $groupKey;
        $aggKey    = $report->aggregate_field ?? 'count';

        $aggDef = collect($src['aggregatable'])->firstWhere('key', $aggKey)
            ?? ['fn' => 'COUNT', 'field' => '*'];

        // raw:true means the field is already a full SQL expression (e.g. calculated formula)
        if ($aggDef['field'] === '*') {
            $aggExpr = DB::raw('COUNT(*) as aggregate_value');
        } elseif ($aggDef['raw'] ?? false) {
            $aggExpr = DB::raw($aggDef['fn'] . '(' . $aggDef['field'] . ') as aggregate_value');
        } else {
            $aggExpr = DB::raw($aggDef['fn'] . '(' . $aggDef['field'] . ') as aggregate_value');
        }

        $rows = $query
            ->select([DB::raw($groupExpr . ' as group_label'), $aggExpr])
            ->groupBy(DB::raw($groupExpr))
            ->orderBy(DB::raw($groupExpr))
            ->limit($limit)
            ->get();

        $aggLabel   = $aggDef['label'] ?? 'Value';
        $groupLabel = $cols[$groupKey]['label'] ?? $groupKey;

        $headers = [
            ['key' => 'group_label',     'label' => $groupLabel,  'type' => 'string'],
            ['key' => 'aggregate_value', 'label' => $aggLabel,    'type' => 'number'],
        ];

        $labels = $rows->pluck('group_label')->map(fn($v) => $v ?? '(none)')->values()->all();
        $values = $rows->pluck('aggregate_value')->map(fn($v) => (float) $v)->values()->all();

        return [
            'rows'    => $rows->toArray(),
            'columns' => $headers,
            'chart'   => [
                'type'     => $report->chart_type,
                'labels'   => $labels,
                'datasets' => [['label' => $aggLabel, 'data' => $values]],
            ],
        ];
    }

    private function applyFilters($query, array $cols, array $filters): void
    {
        foreach ($filters as $filter) {
            $field = $filter['field'] ?? null;
            $op    = $filter['op']    ?? '=';
            $value = $filter['value'] ?? null;

            if (! $field || ! isset($cols[$field]) || $value === null || $value === '') continue;

            $expr = $cols[$field]['expr'];

            match ($op) {
                'contains'    => $query->where(DB::raw($expr), 'LIKE', '%' . $value . '%'),
                'starts_with' => $query->where(DB::raw($expr), 'LIKE', $value . '%'),
                '!='          => $query->where(DB::raw($expr), '!=', $value),
                '>'           => $query->where(DB::raw($expr), '>', $value),
                '<'           => $query->where(DB::raw($expr), '<', $value),
                '>='          => $query->where(DB::raw($expr), '>=', $value),
                '<='          => $query->where(DB::raw($expr), '<=', $value),
                default       => $query->where(DB::raw($expr), $value),
            };
        }
    }
}
