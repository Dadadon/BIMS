<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Universal bootstrap seeder — safe to run for every client deployment.
     *
     * The following data is seeded directly in migrations:
     *   - settings row, modules, roles, permissions, kpi_definitions
     *
     * This seeder adds only client-agnostic essentials:
     *   - System admin user
     *   - Placeholder company + common departments/job titles/leave groups
     *   - Cancel reasons (telecom industry standard codes)
     *
     * DO NOT add sale types or tax configurations here — use a country seeder:
     *   php artisan db:seed --class=JamaicaCallCenterSeeder
     *
     * Dev-only seeders (DemoSeeder, SalesSeeder, BenchmarkSeeder) must NEVER
     * run against a production or client database.
     */
    public function run(): void
    {
        // Admin user is created by the setup wizard — not seeded here.
        $this->seedHrRefData();
        $this->seedCancelReasons();
    }

    // ── HR reference data ─────────────────────────────────────────────────────

    private function seedHrRefData(): void
    {
        // Placeholder company — admin renames this via Settings after setup
        DB::table('companies')->insertOrIgnore([
            ['name' => 'My Company', 'commission_model' => 'sale_type_rate', 'commission_rate' => 0.00, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],
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

}
