<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed bootstrap data for a fresh BIMS installation.
     *
     * The following data is seeded directly in migrations:
     *   - settings row, modules, roles, permissions, kpi_definitions
     *
     * This seeder adds:
     *   - System admin user
     *   - Primary company + sample departments/job titles/leave group
     *   - Cancel reasons (telecom industry standard codes)
     *   - Sample sale types
     *   - Tax / deduction configurations (Philippines-style example)
     */
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedHrRefData();
        $this->seedCancelReasons();
        $this->seedSaleTypes();
        $this->seedTaxConfigurations();
    }

    // ── Admin user ────────────────────────────────────────────────────────────

    private function seedAdmin(): void
    {
        $systemAdminRoleId = DB::table('roles')->where('slug', 'system_admin')->value('id');

        DB::table('users')->insertOrIgnore([
            'role_id'           => $systemAdminRoleId,
            'employee_id'       => null,
            'name'              => 'System Administrator',
            'email'             => 'admin@mpvinternational.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('changeme2024!'),
            'acc_type'          => 'admin',
            'status'            => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    // ── HR reference data ─────────────────────────────────────────────────────

    private function seedHrRefData(): void
    {
        // Primary company
        DB::table('companies')->insertOrIgnore([
            ['name' => 'MPV International', 'commission_model' => 'sale_type_rate', 'commission_rate' => 0.00, 'is_primary' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Third-Party Center', 'commission_model' => 'company_percentage', 'commission_rate' => 15.00, 'is_primary' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Departments
        DB::table('departments')->insertOrIgnore([
            ['name' => 'Operations', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales',      'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IT',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HR',         'created_at' => now(), 'updated_at' => now()],
        ]);

        // Job titles
        DB::table('job_titles')->insertOrIgnore([
            ['title' => 'Sales Agent',         'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Senior Sales Agent',  'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Team Leader',         'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Operations Manager',  'created_at' => now(), 'updated_at' => now()],
            ['title' => 'HR Coordinator',      'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Finance Officer',     'created_at' => now(), 'updated_at' => now()],
            ['title' => 'IT Specialist',       'created_at' => now(), 'updated_at' => now()],
        ]);

        // Leave groups
        DB::table('leave_groups')->insertOrIgnore([
            ['name' => 'Standard (15 days)',    'annual_days' => 15.0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Probationary (5 days)', 'annual_days' => 5.0,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Senior (20 days)',       'annual_days' => 20.0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    // ── Cancel reasons (telecom) ──────────────────────────────────────────────

    private function seedCancelReasons(): void
    {
        $reasons = [
            // Voluntary / controllable — agent-caused
            ['code' => 'NI',   'label' => 'Not Interested',              'is_voluntary' => true,  'is_controllable' => true,  'description' => 'Customer changed mind after sale.'],
            ['code' => 'AF',   'label' => 'Cannot Afford',               'is_voluntary' => true,  'is_controllable' => true,  'description' => 'Customer states financial constraints.'],
            ['code' => 'MIS',  'label' => 'Misrepresentation',           'is_voluntary' => true,  'is_controllable' => true,  'description' => 'Agent misrepresented the offer.'],
            ['code' => 'DUPE', 'label' => 'Duplicate Order',             'is_voluntary' => true,  'is_controllable' => true,  'description' => 'Customer already has an active order.'],
            // Voluntary / non-controllable — outside agent control
            ['code' => 'MOV',  'label' => 'Customer Moving',             'is_voluntary' => true,  'is_controllable' => false, 'description' => 'Customer is relocating out of service area.'],
            ['code' => 'COMP', 'label' => 'Chose Competitor',            'is_voluntary' => true,  'is_controllable' => false, 'description' => 'Customer selected a different provider.'],
            ['code' => 'DCSD', 'label' => 'Deceased',                    'is_voluntary' => true,  'is_controllable' => false, 'description' => 'Customer is deceased.'],
            // Involuntary — system/carrier initiated
            ['code' => 'NA',   'label' => 'Not Available in Area',       'is_voluntary' => false, 'is_controllable' => false, 'description' => 'Service not available at customer address.'],
            ['code' => 'INS',  'label' => 'Installation Failed',         'is_voluntary' => false, 'is_controllable' => false, 'description' => 'Technician could not complete installation.'],
            ['code' => 'CR',   'label' => 'Credit Check Failed',         'is_voluntary' => false, 'is_controllable' => false, 'description' => 'Customer did not pass carrier credit check.'],
            ['code' => 'BC',   'label' => 'Billing/Collections Issue',   'is_voluntary' => false, 'is_controllable' => false, 'description' => 'Account suspended due to non-payment.'],
        ];

        foreach ($reasons as &$r) {
            $r['created_at'] = now();
            $r['updated_at'] = now();
        }

        DB::table('cancel_reasons')->insertOrIgnore($reasons);
    }

    // ── Sale types ────────────────────────────────────────────────────────────

    private function seedSaleTypes(): void
    {
        $types = [
            ['product_category' => 'Internet',          'portal' => 'Xfinity',   'product_code' => 'INT-XFN-100', 'total_points' => 100, 'points_per_agent' => 100.00],
            ['product_category' => 'Internet + TV',     'portal' => 'Xfinity',   'product_code' => 'INT-TV-XFN',  'total_points' => 150, 'points_per_agent' => 150.00],
            ['product_category' => 'Internet + TV + Phone', 'portal' => 'Xfinity','product_code' => 'TRIPLE-XFN', 'total_points' => 200, 'points_per_agent' => 200.00],
            ['product_category' => 'Internet',          'portal' => 'AT&T',      'product_code' => 'INT-ATT-100', 'total_points' => 90,  'points_per_agent' => 90.00],
            ['product_category' => 'Mobile',            'portal' => 'AT&T',      'product_code' => 'MOB-ATT',     'total_points' => 75,  'points_per_agent' => 75.00],
            ['product_category' => 'Internet',          'portal' => 'Spectrum',  'product_code' => 'INT-SPE-200', 'total_points' => 110, 'points_per_agent' => 110.00],
            ['product_category' => 'Internet + Phone',  'portal' => 'Spectrum',  'product_code' => 'INT-PH-SPE',  'total_points' => 140, 'points_per_agent' => 140.00],
        ];

        foreach ($types as &$t) {
            $t['is_active']  = true;
            $t['created_at'] = now();
            $t['updated_at'] = now();
        }

        DB::table('sale_types')->insertOrIgnore($types);
    }

    // ── Tax / deduction configurations ────────────────────────────────────────

    private function seedTaxConfigurations(): void
    {
        // Example: Philippines-style deductions (SSS, PhilHealth, Pag-IBIG, Withholding Tax)
        // Adjust rates/thresholds to match actual jurisdiction.
        $configs = [
            [
                'name'                   => 'SSS (Employee)',
                'code'                   => 'SSS_EE',
                'rate'                   => 0.0450,  // 4.5% of gross
                'flat_amount'            => null,
                'applies_to'             => 'gross',
                'income_threshold'       => null,
                'is_employer_contribution' => false,
                'is_active'              => true,
            ],
            [
                'name'                   => 'SSS (Employer)',
                'code'                   => 'SSS_ER',
                'rate'                   => 0.0950,  // 9.5% of gross
                'flat_amount'            => null,
                'applies_to'             => 'gross',
                'income_threshold'       => null,
                'is_employer_contribution' => true,
                'is_active'              => true,
            ],
            [
                'name'                   => 'PhilHealth (Employee)',
                'code'                   => 'PHIC_EE',
                'rate'                   => 0.0250,  // 2.5% of gross
                'flat_amount'            => null,
                'applies_to'             => 'gross',
                'income_threshold'       => null,
                'is_employer_contribution' => false,
                'is_active'              => true,
            ],
            [
                'name'                   => 'PhilHealth (Employer)',
                'code'                   => 'PHIC_ER',
                'rate'                   => 0.0250,
                'flat_amount'            => null,
                'applies_to'             => 'gross',
                'income_threshold'       => null,
                'is_employer_contribution' => true,
                'is_active'              => true,
            ],
            [
                'name'                   => 'Pag-IBIG (Employee)',
                'code'                   => 'HDMF_EE',
                'rate'                   => 0.0000,
                'flat_amount'            => 100.00,  // Fixed ₱100
                'applies_to'             => 'gross',
                'income_threshold'       => null,
                'is_employer_contribution' => false,
                'is_active'              => true,
            ],
            [
                'name'                   => 'Pag-IBIG (Employer)',
                'code'                   => 'HDMF_ER',
                'rate'                   => 0.0000,
                'flat_amount'            => 100.00,
                'applies_to'             => 'gross',
                'income_threshold'       => null,
                'is_employer_contribution' => true,
                'is_active'              => true,
            ],
            [
                'name'                   => 'Withholding Tax',
                'code'                   => 'WHT',
                'rate'                   => 0.0000,   // Computed separately by PayrollService
                'flat_amount'            => null,
                'applies_to'             => 'taxable',
                'income_threshold'       => 20833.00, // Monthly exemption threshold
                'is_employer_contribution' => false,
                'is_active'              => true,
            ],
        ];

        foreach ($configs as &$c) {
            $c['created_at'] = now();
            $c['updated_at'] = now();
        }

        DB::table('tax_configurations')->insertOrIgnore($configs);
    }
}
