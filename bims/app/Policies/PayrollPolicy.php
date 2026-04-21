<?php

namespace App\Policies;

use App\Models\Payroll\PayrollRun;
use App\Models\Payroll\PayrollSlip;
use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('payroll', 'view');
    }

    public function viewTeam(User $user): bool
    {
        return $user->hasPermission('payroll', 'view_team');
    }

    public function view(User $user, PayrollSlip $slip): bool
    {
        // Employees only see their own payslip
        if ($user->isEmployee()) {
            return $slip->employee_id === $user->employee_id;
        }
        return $user->hasPermission('payroll', 'view');
    }

    public function run(User $user): bool
    {
        return $user->hasPermission('payroll', 'run');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('payroll', 'export');
    }
}
