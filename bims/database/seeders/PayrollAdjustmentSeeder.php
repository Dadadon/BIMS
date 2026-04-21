<?php

namespace Database\Seeders;

use App\Models\HR\Employee;
use App\Models\Payroll\PayrollAdjustment;
use Illuminate\Database\Seeder;

class PayrollAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::active()->get()->keyBy(fn($e) => "{$e->firstname} {$e->lastname}");

        $maria   = $employees['Maria Santos']    ?? null;
        $james   = $employees['James Reyes']     ?? null;
        $angela  = $employees['Angela Cruz']     ?? null;
        $carlos  = $employees['Carlos Mendoza']  ?? null;
        $trisha  = $employees['Trisha Villanueva'] ?? null;
        $kevin   = $employees['Kevin Lim']       ?? null;
        $rachel  = $employees['Rachel Tan']      ?? null;
        $mark    = $employees['Mark Aquino']     ?? null;

        $rows = [

            // ── Global additions (all employees) ─────────────────────
            [
                'employee_id'    => null,
                'type'           => 'addition',
                'category'       => 'allowance',
                'description'    => 'Transportation Allowance',
                'amount_type'    => 'fixed',
                'amount'         => 300.00,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-01-01',
                'expires_date'   => null,
            ],
            [
                'employee_id'    => null,
                'type'           => 'addition',
                'category'       => 'allowance',
                'description'    => 'Meal Allowance',
                'amount_type'    => 'fixed',
                'amount'         => 200.00,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-01-01',
                'expires_date'   => null,
            ],

            // ── Team Leader bonus (Maria) ─────────────────────────────
            [
                'employee_id'    => $maria?->id,
                'type'           => 'addition',
                'category'       => 'allowance',
                'description'    => 'Team Leader Allowance',
                'amount_type'    => 'fixed',
                'amount'         => 500.00,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-01-01',
                'expires_date'   => null,
            ],
            [
                'employee_id'    => $maria?->id,
                'type'           => 'addition',
                'category'       => 'bonus',
                'description'    => 'Q1 Performance Bonus',
                'amount_type'    => 'fixed',
                'amount'         => 1500.00,
                'is_recurring'   => false,
                'is_active'      => true,
                'effective_date' => '2026-04-01',
                'expires_date'   => '2026-04-30',
            ],

            // ── Individual bonuses ────────────────────────────────────
            [
                'employee_id'    => $james?->id,
                'type'           => 'addition',
                'category'       => 'bonus',
                'description'    => 'Top Seller Bonus – March',
                'amount_type'    => 'fixed',
                'amount'         => 800.00,
                'is_recurring'   => false,
                'is_active'      => true,
                'effective_date' => '2026-04-01',
                'expires_date'   => '2026-04-30',
            ],
            [
                'employee_id'    => $angela?->id,
                'type'           => 'addition',
                'category'       => 'bonus',
                'description'    => 'Perfect Attendance Bonus',
                'amount_type'    => 'fixed',
                'amount'         => 300.00,
                'is_recurring'   => false,
                'is_active'      => true,
                'effective_date' => '2026-04-01',
                'expires_date'   => '2026-04-30',
            ],

            // ── Global deductions (all employees) ────────────────────
            [
                'employee_id'    => null,
                'type'           => 'deduction',
                'category'       => 'other',
                'description'    => 'SSS Contribution',
                'amount_type'    => 'fixed',
                'amount'         => 581.30,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-01-01',
                'expires_date'   => null,
            ],
            [
                'employee_id'    => null,
                'type'           => 'deduction',
                'category'       => 'other',
                'description'    => 'PhilHealth Contribution',
                'amount_type'    => 'percentage',
                'amount'         => 2.00,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-01-01',
                'expires_date'   => null,
            ],
            [
                'employee_id'    => null,
                'type'           => 'deduction',
                'category'       => 'other',
                'description'    => 'Pag-IBIG Contribution',
                'amount_type'    => 'fixed',
                'amount'         => 100.00,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-01-01',
                'expires_date'   => null,
            ],

            // ── Loan repayments ───────────────────────────────────────
            [
                'employee_id'    => $carlos?->id,
                'type'           => 'deduction',
                'category'       => 'loan_repayment',
                'description'    => 'SSS Salary Loan – monthly repayment',
                'amount_type'    => 'fixed',
                'amount'         => 500.00,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-02-01',
                'expires_date'   => '2026-07-31',
            ],
            [
                'employee_id'    => $kevin?->id,
                'type'           => 'deduction',
                'category'       => 'cash_advance',
                'description'    => 'Cash Advance Recovery',
                'amount_type'    => 'fixed',
                'amount'         => 250.00,
                'is_recurring'   => true,
                'is_active'      => true,
                'effective_date' => '2026-03-01',
                'expires_date'   => '2026-06-30',
            ],

            // ── Absence / late deductions (one-time) ──────────────────
            [
                'employee_id'    => $mark?->id,
                'type'           => 'deduction',
                'category'       => 'absence',
                'description'    => 'AWOL – 2 days (Apr 7–8)',
                'amount_type'    => 'fixed',
                'amount'         => 160.00,
                'is_recurring'   => false,
                'is_active'      => true,
                'effective_date' => '2026-04-01',
                'expires_date'   => '2026-04-30',
            ],
            [
                'employee_id'    => $trisha?->id,
                'type'           => 'deduction',
                'category'       => 'late',
                'description'    => 'Late deductions – April',
                'amount_type'    => 'fixed',
                'amount'         => 85.00,
                'is_recurring'   => false,
                'is_active'      => true,
                'effective_date' => '2026-04-01',
                'expires_date'   => '2026-04-30',
            ],
            [
                'employee_id'    => $rachel?->id,
                'type'           => 'deduction',
                'category'       => 'late',
                'description'    => 'Late deductions – April',
                'amount_type'    => 'fixed',
                'amount'         => 45.00,
                'is_recurring'   => false,
                'is_active'      => true,
                'effective_date' => '2026-04-01',
                'expires_date'   => '2026-04-30',
            ],
        ];

        foreach ($rows as $row) {
            PayrollAdjustment::create($row);
        }
    }
}
