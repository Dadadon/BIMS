<?php

namespace App\Policies;

use App\Models\Sales\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sales', 'view');
    }

    public function viewAll(User $user): bool
    {
        return $user->hasPermission('sales', 'view_all');
    }

    public function viewTeam(User $user): bool
    {
        return $user->hasPermission('sales', 'view_team');
    }

    public function view(User $user, Sale $sale): bool
    {
        if ($user->hasPermission('sales', 'view_all')) return true;
        if ($user->hasPermission('sales', 'view_team')) {
            // Team leads see their company's sales
            return $sale->employee?->company_id === $user->employee?->company_id;
        }
        // Employees see only their own
        return $sale->employee_id === $user->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sales', 'create');
    }

    public function update(User $user, Sale $sale): bool
    {
        if ($user->hasPermission('sales', 'edit')) return true;
        return false;
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->hasPermission('sales', 'delete');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('sales', 'export');
    }
}
