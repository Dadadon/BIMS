<?php

namespace App\Services\Sales;

use App\Models\HR\Company;
use App\Models\HR\Employee;
use App\Models\Sales\SaleType;

class CommissionCalculator
{
    /**
     * Calculate agent_points for a sale.
     *
     * Logic (SDD §6.1):
     *   - Company model 'sale_type_rate'   → use sale_types.points_per_agent directly
     *   - Company model 'company_percentage' → total_points × (commission_rate / 100)
     *
     * @param  int    $employeeId
     * @param  int    $saleTypeId
     * @return array{total_points: float, agent_points: float}
     */
    public function calculate(int $employeeId, int $saleTypeId): array
    {
        $employee = Employee::with('company')->findOrFail($employeeId);
        $saleType = SaleType::findOrFail($saleTypeId);

        $totalPoints = (float) $saleType->total_points;
        $company     = $employee->company;

        if (! $company) {
            // No company assigned — no commission
            return ['total_points' => $totalPoints, 'agent_points' => 0.00];
        }

        $agentPoints = $company->calculateAgentPoints($totalPoints, (float) $saleType->points_per_agent);

        return [
            'total_points' => $totalPoints,
            'agent_points' => $agentPoints,
        ];
    }
}
