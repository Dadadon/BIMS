<?php

namespace App\Policies;

use App\Models\HR\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hr', 'view');
    }

    public function view(User $user, Employee $employee): bool
    {
        // Employees can only view their own record
        if ($user->isEmployee()) {
            return $user->employee_id === $employee->id;
        }
        return $user->hasPermission('hr', 'view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('hr', 'create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermission('hr', 'edit');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermission('hr', 'delete');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('hr', 'export');
    }
}
