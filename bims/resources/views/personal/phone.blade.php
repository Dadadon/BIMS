@extends('layouts.app')
@section('title', 'My Calls')
@section('page-title', 'My Calls')

@section('content')

@if(! $employee)
<div class="rounded-md bg-yellow-50 p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to an employee profile. Contact your administrator.</p>
</div>
@else

<div class="mb-6 flex items-center justify-between">
    <h2 class="text-xl font-semibold text-gray-900">My Call History</h2>
    @if($employee->sip_extension)
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">Ext. {{ $employee->sip_extension }}</span>
    </div>
    @endif
</div>

@if($logs->isEmpty())
<div class="rounded-lg bg-white border border-gray-200 px-6 py-16 text-center">
    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
    </svg>
    <p class="mt-3 text-sm text-gray-500">No calls logged yet.</p>
</div>
@else
<div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Direction</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Number</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Duration</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Disposition</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Linked Sale</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Time</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @foreach($logs as $log)
            @php
                $dispColor = match($log->disposition ?? '') {
                    'answered'  => 'bg-green-50 text-green-700 ring-green-600/20',
                    'no_answer' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                    'busy'      => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                    'voicemail' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                    'failed'    => 'bg-red-50 text-red-700 ring-red-600/20',
                    default     => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                };
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    @if($log->direction === 'inbound')
                    <span class="inline-flex items-center gap-1 text-blue-600 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25"/></svg>
                        Inbound
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 text-purple-600 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                        Outbound
                    </span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-mono text-gray-700">{{ $log->getRemoteNumber() }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->getDurationLabel() }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @if($log->disposition)
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $dispColor }}">
                        {{ ucfirst(str_replace('_', ' ', $log->disposition)) }}
                    </span>
                    @else
                    <span class="text-gray-400 text-xs">{{ ucfirst($log->status) }}</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    @if($log->sale)
                    <span class="text-indigo-600 text-xs">{{ $log->sale->customer_name }}</span>
                    @else
                    <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">
                    {{ $log->started_at?->diffForHumans() ?? $log->created_at->diffForHumans() }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if(method_exists($logs, 'links'))<div class="mt-4">{{ $logs->links() }}</div>@endif
@endif

@endif
@endsection
