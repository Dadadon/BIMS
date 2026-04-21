@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- General settings --}}
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white shadow rounded-lg">
            @csrf @method('PUT')
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">General Settings</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" required
                           value="{{ old('company_name', $settings->company_name) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Timezone <span class="text-red-500">*</span></label>
                    <select name="timezone"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        @foreach(\DateTimeZone::listIdentifiers() as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', $settings->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Currency</label>
                    <input type="text" name="currency"
                           value="{{ old('currency', $settings->currency ?? 'PHP') }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Date Format</label>
                    <input type="text" name="date_format"
                           value="{{ old('date_format', $settings->date_format ?? 'Y-m-d') }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">OT Daily Threshold (hours)</label>
                    <input type="number" name="overtime_threshold" step="0.5" min="0"
                           value="{{ old('overtime_threshold', $settings->overtime_config['daily_threshold_hours'] ?? 8) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">OT Pay Multiplier</label>
                    <input type="number" name="overtime_multiplier" step="0.1" min="1"
                           value="{{ old('overtime_multiplier', $settings->overtime_config['multiplier'] ?? 1.5) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900">Allowed IPs (comma-separated, leave blank to allow all)</label>
                    <input type="text" name="allowed_ips"
                           value="{{ old('allowed_ips', $settings->allowed_ips) }}"
                           placeholder="e.g. 192.168.1.1, 10.0.0.1"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end">
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Module toggles --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Module Toggles</h3>
        </div>
        <ul class="divide-y divide-gray-100">
            @foreach($modules as $mod)
            <li class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($mod->name) }}</p>
                    <p class="text-xs text-gray-500">{{ $mod->key }}</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.modules.toggle', $mod->key) }}">
                    @csrf
                    <button type="submit"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none
                                   {{ $mod->is_enabled ? 'bg-indigo-600' : 'bg-gray-200' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition
                                     {{ $mod->is_enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
