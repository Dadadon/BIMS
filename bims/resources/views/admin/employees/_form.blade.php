@php $emp = $employee ?? null; @endphp

{{-- Section: Employment --}}
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">Employment Details</h3>
    </div>
    <div class="px-6 py-6 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6">

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Employee Code <span class="text-red-500">*</span></label>
            <input type="text" name="employee_code" required
                   value="{{ old('employee_code', $emp?->employee_code) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            @error('employee_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Company <span class="text-red-500">*</span></label>
            <select name="company_id" required
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— Select —</option>
                @foreach($companies as $c)
                <option value="{{ $c->id }}" {{ old('company_id', $emp?->company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('company_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Department <span class="text-red-500">*</span></label>
            <select name="department_id" required
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— Select —</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ old('department_id', $emp?->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
            @error('department_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Team</label>
            <select name="team_id"
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— No team —</option>
                @foreach($teams as $t)
                <option value="{{ $t->id }}" {{ old('team_id', $emp?->team_id) == $t->id ? 'selected' : '' }}>
                    {{ $t->name }}
                </option>
                @endforeach
            </select>
            @error('team_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Job Title <span class="text-red-500">*</span></label>
            <select name="job_title_id" required
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— Select —</option>
                @foreach($jobTitles as $jt)
                <option value="{{ $jt->id }}" {{ old('job_title_id', $emp?->job_title_id) == $jt->id ? 'selected' : '' }}>{{ $jt->title }}</option>
                @endforeach
            </select>
            @error('job_title_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Leave Group</label>
            <select name="leave_group_id"
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— None —</option>
                @foreach($leaveGroups as $lg)
                <option value="{{ $lg->id }}" {{ old('leave_group_id', $emp?->leave_group_id) == $lg->id ? 'selected' : '' }}>{{ $lg->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Employment Type <span class="text-red-500">*</span></label>
            <select name="employment_type" required
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                @foreach(['Regular','Trainee','Contract','Part-time'] as $t)
                <option value="{{ $t }}" {{ old('employment_type', $emp?->employment_type) === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Status <span class="text-red-500">*</span></label>
            <select name="employment_status" required
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                @foreach(['Active','Inactive','Terminated','On Leave'] as $s)
                <option value="{{ $s }}" {{ old('employment_status', $emp?->employment_status ?? 'Active') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Start Date</label>
            <input type="date" name="start_date"
                   value="{{ old('start_date', $emp?->start_date?->format('Y-m-d')) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Regularization Date</label>
            <input type="date" name="regularization_date"
                   value="{{ old('regularization_date', $emp?->regularization_date?->format('Y-m-d')) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Base Rate <span class="text-red-500">*</span></label>
            <input type="number" name="base_rate" step="0.01" min="0" required
                   value="{{ old('base_rate', $emp?->base_rate) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            @error('base_rate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2 flex items-center gap-3 mt-6">
            <input type="hidden" name="is_salaried" value="0">
            <input type="checkbox" name="is_salaried" id="is_salaried" value="1"
                   {{ old('is_salaried', $emp?->is_salaried) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
            <label for="is_salaried" class="text-sm font-medium text-gray-900">Salaried (fixed monthly)</label>
        </div>
    </div>
</div>

{{-- Section: Personal --}}
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">Personal Information</h3>
    </div>
    <div class="px-6 py-6 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6">

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">First Name <span class="text-red-500">*</span></label>
            <input type="text" name="firstname" required
                   value="{{ old('firstname', $emp?->firstname) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            @error('firstname')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Last Name <span class="text-red-500">*</span></label>
            <input type="text" name="lastname" required
                   value="{{ old('lastname', $emp?->lastname) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            @error('lastname')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Middle Name</label>
            <input type="text" name="middle_name"
                   value="{{ old('middle_name', $emp?->middle_name) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Personal Email</label>
            <input type="email" name="email"
                   value="{{ old('email', $emp?->email) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Company Email</label>
            <input type="email" name="company_email"
                   value="{{ old('company_email', $emp?->company_email) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Phone</label>
            <input type="text" name="phone"
                   value="{{ old('phone', $emp?->phone) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Gender</label>
            <select name="gender" class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— Select —</option>
                @foreach(['Male','Female','Other'] as $g)
                <option value="{{ $g }}" {{ old('gender', $emp?->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Civil Status</label>
            <select name="civil_status" class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— Select —</option>
                @foreach(['Single','Married','Widowed','Separated'] as $cs)
                <option value="{{ $cs }}" {{ old('civil_status', $emp?->civil_status) === $cs ? 'selected' : '' }}>{{ $cs }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Birthday</label>
            <input type="date" name="birthday"
                   value="{{ old('birthday', $emp?->birthday?->format('Y-m-d')) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Birthplace</label>
            <input type="text" name="birthplace"
                   value="{{ old('birthplace', $emp?->birthplace) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">National ID</label>
            <input type="text" name="national_id"
                   value="{{ old('national_id', $emp?->national_id) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="sm:col-span-6">
            <label class="block text-sm font-medium text-gray-900">Home Address</label>
            <input type="text" name="home_address"
                   value="{{ old('home_address', $emp?->home_address) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>

    </div>
</div>

{{-- Section: Phone / SIP --}}
@module('phone')
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">Phone / Softphone</h3>
    </div>
    <div class="px-6 py-6 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">SIP Extension</label>
            <input type="text" name="sip_extension"
                   value="{{ old('sip_extension', $emp?->sip_extension) }}"
                   placeholder="e.g. 1001"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm font-mono">
            <p class="mt-1 text-xs text-gray-500">Leave blank if this employee does not use the softphone.</p>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">
                SIP Password
                @if($emp?->exists) <span class="font-normal text-gray-400">(leave blank to keep)</span> @endif
            </label>
            <input type="password" name="sip_password" autocomplete="new-password"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
        </div>
    </div>
</div>
@endmodule

{{-- Section: Additional Information (custom fields) --}}
@if($customFields->isNotEmpty())
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">Additional Information</h3>
    </div>
    <div class="px-6 py-6 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6">
        @foreach($customFields as $field)
        @php $val = old("meta_{$field->key}", $emp?->getMeta($field->key)); @endphp
        <div class="{{ $field->field_type === 'textarea' ? 'sm:col-span-6' : 'sm:col-span-2' }}">
            <label class="block text-sm font-medium text-gray-900">
                {{ $field->label }}
                @if($field->is_required)<span class="text-red-500">*</span>@endif
            </label>
            @if($field->field_type === 'select')
            <select name="meta_{{ $field->key }}" {{ $field->is_required ? 'required' : '' }}
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">— Select —</option>
                @foreach($field->options ?? [] as $opt)
                <option value="{{ $opt }}" {{ $val === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            @elseif($field->field_type === 'textarea')
            <textarea name="meta_{{ $field->key }}" {{ $field->is_required ? 'required' : '' }} rows="3"
                      class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">{{ $val }}</textarea>
            @elseif($field->field_type === 'checkbox')
            <div class="mt-2 flex items-center gap-2">
                <input type="hidden" name="meta_{{ $field->key }}" value="">
                <input type="checkbox" name="meta_{{ $field->key }}" value="1" id="meta_{{ $field->key }}"
                       {{ $val ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                <label for="meta_{{ $field->key }}" class="text-sm text-gray-600">Yes</label>
            </div>
            @else
            <input type="{{ $field->field_type === 'number' ? 'number' : ($field->field_type === 'date' ? 'date' : 'text') }}"
                   name="meta_{{ $field->key }}" value="{{ $val }}" {{ $field->is_required ? 'required' : '' }}
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
