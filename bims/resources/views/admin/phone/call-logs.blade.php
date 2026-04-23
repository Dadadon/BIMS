@extends('layouts.app')
@section('title', 'Call Logs')
@section('page-title', 'Phone')

@section('content')

<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Call Logs</h2>
    <a href="{{ route('admin.phone.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Integrations</a>
</div>

<form method="GET" class="mb-6 flex flex-wrap gap-3">
    <select name="employee_id" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Agents</option>
        @foreach($employees as $emp)
        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->display_name }}</option>
        @endforeach
    </select>
    <select name="direction" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Directions</option>
        <option value="inbound"  {{ request('direction') === 'inbound'  ? 'selected' : '' }}>Inbound</option>
        <option value="outbound" {{ request('direction') === 'outbound' ? 'selected' : '' }}>Outbound</option>
    </select>
    <select name="disposition" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Dispositions</option>
        @foreach(['answered','no_answer','busy','voicemail','failed'] as $d)
        <option value="{{ $d }}" {{ request('disposition') === $d ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $d)) }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
    <a href="{{ route('admin.phone.call-logs') }}" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
</form>

<div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Direction</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Number</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Agent</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Duration</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Disposition</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Linked Sale</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Time</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($logs as $log)
                @include('admin.phone._log-row', ['log' => $log])
            @empty
            <tr><td colspan="7" class="py-10 text-center text-sm text-gray-500">No call logs found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>

@endsection
