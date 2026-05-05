<?php

namespace App\Services\Auth;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\User;

class DirectorySyncService
{
    /**
     * Synchronise directory metadata into BIMS after a successful external login.
     *
     * What is synced:
     *   - users.team_id  ← employees.team_id  (cache kept hot)
     *   - employees.department_id  ← directory department name (name-matched)
     *
     * What is NOT overwritten:
     *   - base_rate, dates, payroll, or any field the directory does not own.
     *
     * @param  array{name:string,email:string,department?:string|null,title?:string|null,groups:array}  $directoryAttrs
     */
    public function sync(User $user, array $directoryAttrs): void
    {
        $employee = $user->fresh(['employee'])->employee;

        $this->syncUserTeam($user, $employee);

        if ($employee !== null) {
            $this->syncDepartment($employee, $directoryAttrs['department'] ?? null);
        }
    }

    /**
     * Keep users.team_id aligned with the linked employee's team_id.
     * This allows scopedTeamId() to resolve without an extra join.
     */
    private function syncUserTeam(User $user, ?Employee $employee): void
    {
        $teamId = $employee?->team_id;

        if ($user->team_id !== $teamId) {
            $user->team_id = $teamId;
            $user->saveQuietly();
        }
    }

    /**
     * Match the directory department name to a BIMS department and update
     * the employee record if a match is found and the value has changed.
     */
    private function syncDepartment(Employee $employee, ?string $departmentName): void
    {
        if (blank($departmentName)) {
            return;
        }

        $department = Department::where('name', $departmentName)->first();

        if ($department && $employee->department_id !== $department->id) {
            $employee->update(['department_id' => $department->id]);
        }
    }
}
