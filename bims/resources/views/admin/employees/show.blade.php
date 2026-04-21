@extends('layouts.app')
@section('title', $employee->display_name)
@section('page-title', $employee->display_name)

@section('content')
{{-- Header --}}
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-x-4">
        <a href="{{ route('admin.employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Employees</a>
    </div>
    <div class="flex gap-2">
        @permission('hr', 'edit')
        <a href="{{ route('admin.employees.edit', $employee) }}"
           class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Edit
        </a>
        @if($employee->employment_status !== 'Terminated')
        <form method="POST" action="{{ route('admin.employees.archive', $employee) }}"
              onsubmit="return confirm('Archive this employee?')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                Archive
            </button>
        </form>
        @endif
        @endpermission
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Profile card --}}
    <div class="lg:col-span-1">
        <div class="rounded-lg bg-white shadow p-6 text-center">
            <div class="mx-auto h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-2xl">
                {{ strtoupper(substr($employee->firstname, 0, 1) . substr($employee->lastname, 0, 1)) }}
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-900">{{ $employee->full_name }}</h3>
            <p class="text-sm text-gray-500">{{ $employee->jobTitle->title ?? '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $employee->employee_code }}</p>

            @php
                $color = match($employee->employment_status) {
                    'Active'     => 'bg-green-50 text-green-700 ring-green-600/20',
                    'On Leave'   => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                    'Inactive'   => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                    'Terminated' => 'bg-red-50 text-red-700 ring-red-600/20',
                    default      => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                };
            @endphp
            <span class="mt-3 inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $color }}">
                {{ $employee->employment_status }}
            </span>
        </div>

        {{-- Quick stats --}}
        <div class="mt-4 rounded-lg bg-white shadow divide-y divide-gray-100">
            <div class="px-4 py-3 flex justify-between text-sm">
                <span class="text-gray-500">Company</span>
                <span class="font-medium text-gray-900">{{ $employee->company->name ?? '—' }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between text-sm">
                <span class="text-gray-500">Department</span>
                <span class="font-medium text-gray-900">{{ $employee->department->name ?? '—' }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between text-sm">
                <span class="text-gray-500">Employment</span>
                <span class="font-medium text-gray-900">{{ $employee->employment_type }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between text-sm">
                <span class="text-gray-500">Start Date</span>
                <span class="font-medium text-gray-900">{{ $employee->start_date?->format('M d, Y') ?? '—' }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between text-sm">
                <span class="text-gray-500">Base Rate</span>
                <span class="font-medium text-gray-900">{{ number_format($employee->base_rate, 2) }}</span>
            </div>
            <div class="px-4 py-3 flex justify-between text-sm">
                <span class="text-gray-500">Pay Type</span>
                <span class="font-medium text-gray-900">{{ $employee->is_salaried ? 'Salaried' : 'Hourly' }}</span>
            </div>
        </div>
    </div>

    {{-- Details panel --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Personal info --}}
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">Personal Information</h4>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 px-6 py-5 text-sm">
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Company Email</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->company_email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Gender</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->gender ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Birthday</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->birthday?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Civil Status</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->civil_status ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Home Address</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->home_address ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">National ID</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->national_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Regularization Date</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $employee->regularization_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- System user account --}}
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">System Account</h4>
            </div>
            <div class="px-6 py-5 text-sm">
                @if($employee->user)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $employee->user->name }}</p>
                            <p class="text-gray-500">{{ $employee->user->email }} · {{ $employee->user->role->name ?? '—' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $employee->user->status ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20' }} ring-1 ring-inset">
                            {{ $employee->user->status ? 'Active' : 'Disabled' }}
                        </span>
                    </div>
                @else
                    <p class="text-gray-500">No system account linked.</p>
                    @permission('hr', 'edit')
                    <a href="{{ route('admin.users.index') }}?employee={{ $employee->id }}"
                       class="mt-2 inline-flex text-indigo-600 hover:text-indigo-900 font-medium">
                        Create user account →
                    </a>
                    @endpermission
                @endif
            </div>
        </div>

        {{-- KPI Assignment --}}
        @module('performance')
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h4 class="text-sm font-semibold text-gray-900">Assigned KPIs</h4>
                <a href="{{ route('admin.performance.show', $employee) }}" class="text-xs text-indigo-600 hover:text-indigo-900">View snapshots →</a>
            </div>

            {{-- Assigned list --}}
            @if($employee->kpiDefinitions->isNotEmpty())
            <ul class="divide-y divide-gray-100">
                @foreach($employee->kpiDefinitions as $kpi)
                <li class="px-6 py-3 flex items-center justify-between text-sm">
                    <div>
                        <p class="font-medium text-gray-900">{{ $kpi->name }}</p>
                        <p class="text-xs text-gray-500">{{ $kpi->module_key }} · target {{ $kpi->target_value }} {{ $kpi->unit }}</p>
                    </div>
                    @admin
                    <form method="POST" action="{{ route('admin.employees.kpis.unassign', [$employee, $kpi]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-900">Remove</button>
                    </form>
                    @endadmin
                </li>
                @endforeach
            </ul>
            @else
            <p class="px-6 py-4 text-sm text-gray-500">No KPIs assigned yet.</p>
            @endif

            {{-- Add KPI --}}
            @admin
            @php $unassigned = $allKpis->whereNotIn('id', $employee->kpiDefinitions->pluck('id')); @endphp
            @if($unassigned->isNotEmpty())
            <div class="px-6 py-4 border-t border-gray-100">
                <form method="POST" class="flex items-center gap-3">
                    @csrf
                    <select name="_kpi_id" id="kpi_select"
                            class="flex-1 rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                        <option value="">Select a KPI to assign…</option>
                        @foreach($unassigned as $kpi)
                        <option value="{{ $kpi->id }}">{{ $kpi->name }} ({{ $kpi->module_key }})</option>
                        @endforeach
                    </select>
                    <button type="submit" id="assign_btn"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">
                        Assign
                    </button>
                </form>
                <script>
                    document.getElementById('assign_btn').addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = document.getElementById('kpi_select').value;
                        if (!id) return;
                        const form = this.closest('form');
                        form.action = "{{ route('admin.employees.index') }}".replace('/employees', '/employees/{{ $employee->id }}/kpis/') + id;
                        form.submit();
                    });
                </script>
            </div>
            @endif
            @endadmin
        </div>

        {{-- Recent KPI snapshots --}}
        @if($employee->kpiSnapshots->isNotEmpty())
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">Recent KPI Scores</h4>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($employee->kpiSnapshots as $snap)
                <li class="px-6 py-3 flex justify-between text-sm">
                    <span class="text-gray-700">{{ $snap->kpi->name ?? '—' }}</span>
                    <span class="font-semibold text-gray-900">{{ number_format($snap->score, 1) }}<span class="text-gray-400 font-normal">/100</span></span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @endmodule

    </div>
</div>
@endsection
