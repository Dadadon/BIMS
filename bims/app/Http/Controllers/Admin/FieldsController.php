<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HR\Company;
use App\Models\HR\Department;
use App\Models\HR\JobTitle;
use App\Models\HR\LeaveGroup;
use App\Models\Leave\LeaveType;
use App\Models\HR\EmployeeFieldDefinition;
use App\Models\Sales\SaleFieldDefinition;
use App\Models\Sales\SaleType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manages all reference/field data tables via a single controller.
 * Each resource (companies, departments, etc.) maps to a method group.
 */
class FieldsController extends Controller
{
    // ── Companies ────────────────────────────────────────────────

    public function companiesIndex(): View
    {
        return view('admin.fields.companies.index', [
            'companies' => Company::withCount('employees')->orderBy('name')->get(),
        ]);
    }

    public function companiesCreate(): View
    {
        return view('admin.fields.companies.form', ['company' => null]);
    }

    public function companiesStore(Request $request): RedirectResponse
    {
        $data = $this->validateCompany($request);
        if ($data['is_primary'] ?? false) {
            Company::where('is_primary', true)->update(['is_primary' => false]);
        }
        Company::create($data);
        return redirect()->route('admin.fields.companies.index')->with('success', 'Company created.');
    }

    public function companiesEdit(Company $company): View
    {
        return view('admin.fields.companies.form', compact('company'));
    }

    public function companiesUpdate(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validateCompany($request, $company->id);
        if ($data['is_primary'] ?? false) {
            Company::where('id', '!=', $company->id)->update(['is_primary' => false]);
        }
        $company->update($data);
        return redirect()->route('admin.fields.companies.index')->with('success', 'Company updated.');
    }

    public function companiesDestroy(Company $company): RedirectResponse
    {
        if ($company->employees()->exists()) {
            return back()->with('error', 'Cannot delete company with existing employees.');
        }
        $company->delete();
        return redirect()->route('admin.fields.companies.index')->with('success', 'Company deleted.');
    }

    private function validateCompany(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:150', 'unique:companies,name,' . ($ignoreId ?? 'NULL')],
            'commission_model' => ['required', 'in:sale_type_rate,company_percentage'],
            'commission_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
            'is_primary'       => ['boolean'],
        ]);
    }

    // ── Departments ──────────────────────────────────────────────

    public function departmentsIndex(): View
    {
        return view('admin.fields.departments.index', [
            'departments' => Department::withCount('employees')->orderBy('name')->get(),
        ]);
    }

    public function departmentsCreate(): View
    {
        return view('admin.fields.departments.form', ['department' => null]);
    }

    public function departmentsStore(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:departments,name']]);
        Department::create($request->only('name'));
        return redirect()->route('admin.fields.departments.index')->with('success', 'Department created.');
    }

    public function departmentsEdit(Department $department): View
    {
        return view('admin.fields.departments.form', compact('department'));
    }

    public function departmentsUpdate(Request $request, Department $department): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:departments,name,' . $department->id]]);
        $department->update($request->only('name'));
        return redirect()->route('admin.fields.departments.index')->with('success', 'Department updated.');
    }

    public function departmentsDestroy(Department $department): RedirectResponse
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Cannot delete department with existing employees.');
        }
        $department->delete();
        return redirect()->route('admin.fields.departments.index')->with('success', 'Department deleted.');
    }

    // ── Job Titles ───────────────────────────────────────────────

    public function jobTitlesIndex(): View
    {
        return view('admin.fields.job-titles.index', [
            'jobTitles' => JobTitle::withCount('employees')->orderBy('title')->get(),
        ]);
    }

    public function jobTitlesCreate(): View
    {
        return view('admin.fields.job-titles.form', ['jobTitle' => null]);
    }

    public function jobTitlesStore(Request $request): RedirectResponse
    {
        $request->validate(['title' => ['required', 'string', 'max:100', 'unique:job_titles,title']]);
        JobTitle::create($request->only('title'));
        return redirect()->route('admin.fields.job-titles.index')->with('success', 'Job title created.');
    }

    public function jobTitlesEdit(JobTitle $jobTitle): View
    {
        return view('admin.fields.job-titles.form', compact('jobTitle'));
    }

    public function jobTitlesUpdate(Request $request, JobTitle $jobTitle): RedirectResponse
    {
        $request->validate(['title' => ['required', 'string', 'max:100', 'unique:job_titles,title,' . $jobTitle->id]]);
        $jobTitle->update($request->only('title'));
        return redirect()->route('admin.fields.job-titles.index')->with('success', 'Job title updated.');
    }

    public function jobTitlesDestroy(JobTitle $jobTitle): RedirectResponse
    {
        if ($jobTitle->employees()->exists()) {
            return back()->with('error', 'Cannot delete job title with existing employees.');
        }
        $jobTitle->delete();
        return redirect()->route('admin.fields.job-titles.index')->with('success', 'Job title deleted.');
    }

    // ── Leave Types ──────────────────────────────────────────────

    public function leaveTypesIndex(): View
    {
        return view('admin.fields.leave-types.index', [
            'leaveTypes' => LeaveType::orderBy('name')->get(),
        ]);
    }

    public function leaveTypesCreate(): View
    {
        return view('admin.fields.leave-types.form', ['leaveType' => null]);
    }

    public function leaveTypesStore(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'is_paid'       => ['boolean'],
            'leave_group_id'=> ['nullable', 'exists:leave_groups,id'],
        ]);
        LeaveType::create($request->only('name', 'is_paid', 'leave_group_id'));
        return redirect()->route('admin.fields.leave-types.index')->with('success', 'Leave type created.');
    }

    public function leaveTypesEdit(LeaveType $leaveType): View
    {
        return view('admin.fields.leave-types.form', compact('leaveType'));
    }

    public function leaveTypesUpdate(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'is_paid'        => ['boolean'],
            'leave_group_id' => ['nullable', 'exists:leave_groups,id'],
        ]);
        $leaveType->update($request->only('name', 'is_paid', 'leave_group_id'));
        return redirect()->route('admin.fields.leave-types.index')->with('success', 'Leave type updated.');
    }

    public function leaveTypesDestroy(LeaveType $leaveType): RedirectResponse
    {
        $leaveType->delete();
        return redirect()->route('admin.fields.leave-types.index')->with('success', 'Leave type deleted.');
    }

    // ── Leave Groups ─────────────────────────────────────────────

    public function leaveGroupsIndex(): View
    {
        return view('admin.fields.leave-groups.index', [
            'leaveGroups' => LeaveGroup::withCount('employees')->orderBy('name')->get(),
        ]);
    }

    public function leaveGroupsCreate(): View
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        return view('admin.fields.leave-groups.form', ['leaveGroup' => null, 'leaveTypes' => $leaveTypes]);
    }

    public function leaveGroupsStore(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:leave_groups,name']]);
        LeaveGroup::create($request->only('name'));
        return redirect()->route('admin.fields.leave-groups.index')->with('success', 'Leave group created.');
    }

    public function leaveGroupsEdit(LeaveGroup $leaveGroup): View
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        return view('admin.fields.leave-groups.form', compact('leaveGroup', 'leaveTypes'));
    }

    public function leaveGroupsUpdate(Request $request, LeaveGroup $leaveGroup): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:leave_groups,name,' . $leaveGroup->id]]);
        $leaveGroup->update($request->only('name'));
        return redirect()->route('admin.fields.leave-groups.index')->with('success', 'Leave group updated.');
    }

    public function leaveGroupsDestroy(LeaveGroup $leaveGroup): RedirectResponse
    {
        if ($leaveGroup->employees()->exists()) {
            return back()->with('error', 'Cannot delete leave group with assigned employees.');
        }
        $leaveGroup->delete();
        return redirect()->route('admin.fields.leave-groups.index')->with('success', 'Leave group deleted.');
    }

    // ── Sale Types ───────────────────────────────────────────────

    public function saleTypesIndex(): View
    {
        return view('admin.fields.sale-types.index', [
            'saleTypes' => SaleType::orderBy('product_category')->orderBy('portal')->get(),
        ]);
    }

    public function saleTypesCreate(): View
    {
        return view('admin.fields.sale-types.form', ['saleType' => null]);
    }

    public function saleTypesStore(Request $request): RedirectResponse
    {
        $request->validate([
            'product_category' => ['required', 'string', 'max:100'],
            'portal'           => ['nullable', 'string', 'max:100'],
            'product_code'     => ['nullable', 'string', 'max:100'],
            'total_points'     => ['required', 'integer', 'min:0'],
            'points_per_agent' => ['required', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
        ]);
        SaleType::create($request->only('product_category', 'portal', 'product_code', 'total_points', 'points_per_agent', 'is_active'));
        return redirect()->route('admin.fields.sale-types.index')->with('success', 'Sale type created.');
    }

    public function saleTypesEdit(SaleType $saleType): View
    {
        return view('admin.fields.sale-types.form', compact('saleType'));
    }

    public function saleTypesUpdate(Request $request, SaleType $saleType): RedirectResponse
    {
        $request->validate([
            'product_category' => ['required', 'string', 'max:100'],
            'portal'           => ['nullable', 'string', 'max:100'],
            'product_code'     => ['nullable', 'string', 'max:100'],
            'total_points'     => ['required', 'integer', 'min:0'],
            'points_per_agent' => ['required', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
        ]);
        $saleType->update($request->only('product_category', 'portal', 'product_code', 'total_points', 'points_per_agent', 'is_active'));
        return redirect()->route('admin.fields.sale-types.index')->with('success', 'Sale type updated.');
    }

    public function saleTypesDestroy(SaleType $saleType): RedirectResponse
    {
        $saleType->delete();
        return redirect()->route('admin.fields.sale-types.index')->with('success', 'Sale type deleted.');
    }

    // ── Sale Field Definitions ───────────────────────────────────

    public function saleFieldsIndex(): View
    {
        $fields = SaleFieldDefinition::with('saleType')->orderBy('sort_order')->orderBy('label')->get();

        return view('admin.fields.sale-fields.index', [
            'fields'      => $fields,
            'saleTypes'   => SaleType::where('is_active', true)->orderBy('product_category')->get(),
            'builtinKeys' => ['total_points', 'agent_points', 'status'],
            'fieldKeys'   => $fields->where('field_type', '!=', 'calculated')->pluck('key')->values(),
        ]);
    }

    public function saleFieldsStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key'          => ['required', 'string', 'max:80', 'alpha_dash', 'unique:sale_field_definitions,key'],
            'label'        => ['required', 'string', 'max:150'],
            'field_type'   => ['required', 'in:text,number,date,select,textarea,checkbox,calculated'],
            'options'      => ['nullable', 'string'],
            'formula'      => ['nullable', 'string', 'max:500'],
            'sale_type_id' => ['nullable', 'exists:sale_types,id'],
            'is_required'   => ['boolean'],
            'show_in_table' => ['boolean'],
            'show_on_create'=> ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
        ]);

        $data['options']        = $this->parseOptions($data['options'] ?? null);
        $data['formula']        = $data['field_type'] === 'calculated' ? ($data['formula'] ?? null) : null;
        $data['is_active']      = true;
        $data['is_required']    = $data['is_required'] ?? false;
        $data['show_in_table']  = $data['show_in_table'] ?? false;
        $data['show_on_create'] = $data['show_on_create'] ?? false;

        SaleFieldDefinition::create($data);
        return redirect()->route('admin.fields.sale-fields.index')->with('success', 'Field created.');
    }

    public function saleFieldsUpdate(Request $request, SaleFieldDefinition $saleField): RedirectResponse
    {
        $data = $request->validate([
            'label'        => ['required', 'string', 'max:150'],
            'field_type'   => ['required', 'in:text,number,date,select,textarea,checkbox,calculated'],
            'options'      => ['nullable', 'string'],
            'formula'      => ['nullable', 'string', 'max:500'],
            'sale_type_id' => ['nullable', 'exists:sale_types,id'],
            'is_required'   => ['boolean'],
            'is_active'     => ['boolean'],
            'show_in_table' => ['boolean'],
            'show_on_create'=> ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
        ]);

        $data['options']        = $this->parseOptions($data['options'] ?? null);
        $data['formula']        = $data['field_type'] === 'calculated' ? ($data['formula'] ?? null) : null;
        $data['is_required']    = $data['is_required'] ?? false;
        $data['is_active']      = $data['is_active'] ?? false;
        $data['show_in_table']  = $data['show_in_table'] ?? false;
        $data['show_on_create'] = $data['show_on_create'] ?? false;

        $saleField->update($data);
        return redirect()->route('admin.fields.sale-fields.index')->with('success', 'Field updated.');
    }

    public function saleFieldsDestroy(SaleFieldDefinition $saleField): RedirectResponse
    {
        $saleField->delete();
        return redirect()->route('admin.fields.sale-fields.index')->with('success', 'Field deleted.');
    }

    // ── Employee Field Definitions ────────────────────────────────

    public function employeeFieldsIndex(): View
    {
        return view('admin.fields.employee-fields.index', [
            'fields' => EmployeeFieldDefinition::orderBy('sort_order')->orderBy('label')->get(),
        ]);
    }

    public function employeeFieldsStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key'           => ['required', 'string', 'max:80', 'alpha_dash', 'unique:employee_field_definitions,key'],
            'label'         => ['required', 'string', 'max:150'],
            'field_type'    => ['required', 'in:text,number,date,select,textarea,checkbox'],
            'options'       => ['nullable', 'string'],
            'is_required'   => ['boolean'],
            'show_on_create'=> ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
        ]);

        $data['options']        = $this->parseOptions($data['options'] ?? null);
        $data['is_active']      = true;
        $data['is_required']    = $data['is_required'] ?? false;
        $data['show_on_create'] = $data['show_on_create'] ?? true;

        EmployeeFieldDefinition::create($data);
        return redirect()->route('admin.fields.employee-fields.index')->with('success', 'Field created.');
    }

    public function employeeFieldsUpdate(Request $request, EmployeeFieldDefinition $employeeField): RedirectResponse
    {
        $data = $request->validate([
            'label'         => ['required', 'string', 'max:150'],
            'field_type'    => ['required', 'in:text,number,date,select,textarea,checkbox'],
            'options'       => ['nullable', 'string'],
            'is_required'   => ['boolean'],
            'is_active'     => ['boolean'],
            'show_on_create'=> ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
        ]);

        $data['options']        = $this->parseOptions($data['options'] ?? null);
        $data['is_required']    = $data['is_required'] ?? false;
        $data['is_active']      = $data['is_active'] ?? false;
        $data['show_on_create'] = $data['show_on_create'] ?? false;

        $employeeField->update($data);
        return redirect()->route('admin.fields.employee-fields.index')->with('success', 'Field updated.');
    }

    public function employeeFieldsDestroy(EmployeeFieldDefinition $employeeField): RedirectResponse
    {
        $employeeField->delete();
        return redirect()->route('admin.fields.employee-fields.index')->with('success', 'Field deleted.');
    }

    private function parseOptions(?string $raw): ?array
    {
        if (! $raw) return null;
        return array_values(array_filter(
            array_map(fn($line) => trim($line), explode("\n", $raw))
        ));
    }
}
