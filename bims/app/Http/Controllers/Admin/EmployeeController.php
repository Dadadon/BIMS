<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HR\Company;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeFieldDefinition;
use App\Models\HR\JobTitle;
use App\Models\HR\LeaveGroup;
use App\Models\Performance\KpiDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::with(['company', 'department', 'jobTitle'])
            ->orderBy('lastname')
            ->orderBy('firstname');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('employment_status', $status);
        }

        if ($company = $request->input('company_id')) {
            $query->where('company_id', $company);
        }

        $employees = $query->paginate(20)->withQueryString();
        $companies = Company::orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'companies'));
    }

    public function create(): View
    {
        return view('admin.employees.create', $this->formData(isCreate: true));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEmployee($request);
        $data['metadata'] = $this->extractMetadata($request, EmployeeFieldDefinition::forCreate());
        Employee::create($data);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        $employee->load(['company', 'department', 'jobTitle', 'leaveGroup', 'user',
                         'activeSchedule', 'kpiDefinitions',
                         'kpiSnapshots' => fn($q) => $q->orderByDesc('computed_at')->limit(3)]);

        $allKpis = KpiDefinition::orderBy('name')->get();

        return view('admin.employees.show', compact('employee', 'allKpis'));
    }

    public function assignKpi(Employee $employee, KpiDefinition $kpi): RedirectResponse
    {
        $employee->kpiDefinitions()->syncWithoutDetaching([$kpi->id]);
        return back()->with('success', "KPI \"{$kpi->name}\" assigned.");
    }

    public function unassignKpi(Employee $employee, KpiDefinition $kpi): RedirectResponse
    {
        $employee->kpiDefinitions()->detach($kpi->id);
        return back()->with('success', "KPI \"{$kpi->name}\" removed.");
    }

    public function edit(Employee $employee): View
    {
        return view('admin.employees.edit', array_merge(
            ['employee' => $employee],
            $this->formData()
        ));
    }


    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $this->validateEmployee($request, $employee->id);
        $data['metadata'] = $this->extractMetadata($request, EmployeeFieldDefinition::active());
        $employee->update($data);

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();
        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted.');
    }

    public function archive(Employee $employee): RedirectResponse
    {
        $employee->update(['employment_status' => 'Terminated']);
        return back()->with('success', "Employee {$employee->display_name} has been archived.");
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function formData(bool $isCreate = false): array
    {
        return [
            'companies'     => Company::orderBy('name')->get(),
            'departments'   => Department::orderBy('name')->get(),
            'jobTitles'     => JobTitle::orderBy('title')->get(),
            'leaveGroups'   => LeaveGroup::orderBy('name')->get(),
            'teams'         => \App\Models\HR\Team::active()->orderBy('name')->get(),
            'customFields'  => $isCreate
                ? EmployeeFieldDefinition::forCreate()
                : EmployeeFieldDefinition::active(),
        ];
    }

    private function extractMetadata(Request $request, iterable $fields): ?array
    {
        $meta = [];
        foreach ($fields as $field) {
            $val = $request->input("meta_{$field->key}");
            if ($val !== null && $val !== '') {
                $meta[$field->key] = $field->field_type === 'checkbox' ? (bool) $val : $val;
            }
        }
        return $meta ?: null;
    }

    private function validateEmployee(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'company_id'          => ['required', 'exists:companies,id'],
            'department_id'       => ['required', 'exists:departments,id'],
            'job_title_id'        => ['required', 'exists:job_titles,id'],
            'leave_group_id'      => ['nullable', 'exists:leave_groups,id'],
            'team_id'             => ['nullable', 'exists:teams,id'],
            'employee_code'       => ['required', 'string', 'max:30',
                                      'unique:employees,employee_code,' . ($ignoreId ?? 'NULL')],
            'firstname'           => ['required', 'string', 'max:80'],
            'lastname'            => ['required', 'string', 'max:80'],
            'middle_name'         => ['nullable', 'string', 'max:80'],
            'email'               => ['nullable', 'email', 'max:150'],
            'company_email'       => ['nullable', 'email', 'max:150'],
            'phone'               => ['nullable', 'string', 'max:30'],
            'gender'              => ['nullable', 'in:Male,Female,Other'],
            'civil_status'        => ['nullable', 'in:Single,Married,Widowed,Separated'],
            'birthday'            => ['nullable', 'date'],
            'birthplace'          => ['nullable', 'string', 'max:150'],
            'home_address'        => ['nullable', 'string', 'max:255'],
            'national_id'         => ['nullable', 'string', 'max:50'],
            'employment_type'     => ['required', 'in:Regular,Trainee,Contract,Part-time'],
            'employment_status'   => ['required', 'in:Active,Inactive,Terminated,On Leave'],
            'start_date'          => ['nullable', 'date'],
            'regularization_date' => ['nullable', 'date'],
            'is_salaried'         => ['boolean'],
            'base_rate'           => ['required', 'numeric', 'min:0'],
            'avatar'              => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
