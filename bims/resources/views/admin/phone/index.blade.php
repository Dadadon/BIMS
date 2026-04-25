@extends('layouts.app')
@section('title', 'Phone')
@section('page-title', 'Phone Integration')

@section('content')

<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Phone Integration</h2>
        <p class="text-sm text-gray-500 mt-1">One active integration at a time. All call logs are shared.</p>
    </div>
    <div class="flex gap-3 mt-4 sm:mt-0">
        <a href="{{ route('admin.phone.call-logs') }}"
           class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Call Logs
        </a>
        <a href="{{ route('admin.phone.create') }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            + Add Integration
        </a>
    </div>
</div>

{{-- Integrations --}}
@if($integrations->isEmpty())
<div class="rounded-lg bg-white border border-gray-200 px-6 py-16 text-center mb-8">
    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
    </svg>
    <p class="mt-3 text-sm text-gray-500">No integrations yet. Add one to get started.</p>
</div>
@else
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-8">
    @foreach($integrations as $integration)
    @php
        $typeColors = [
            'freepbx'    => 'bg-green-100 text-green-700',
            'vicidial'   => 'bg-orange-100 text-orange-700',
            'custom_sip' => 'bg-purple-100 text-purple-700',
        ];
    @endphp
    <div class="bg-white rounded-lg border {{ $integration->is_active ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-gray-200' }} p-5">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $typeColors[$integration->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $integration->getTypeLabel() }}
                    </span>
                    @if($integration->is_active)
                    <span class="inline-flex items-center gap-1 rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                    </span>
                    @endif
                </div>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ $integration->name }}</h3>
                @if($integration->sip_domain)
                <p class="text-xs text-gray-500 mt-0.5">{{ $integration->sip_domain }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">
                    Webhook: <code class="bg-gray-50 px-1 rounded text-[11px]">{{ route('phone.webhook', $integration) }}</code>
                </p>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-3 flex-wrap">
            @if(! $integration->is_active)
            <form method="POST" action="{{ route('admin.phone.activate', $integration) }}">
                @csrf
                <button class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Set Active</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.phone.deactivate', $integration) }}">
                @csrf
                <button class="text-xs font-medium text-gray-500 hover:text-gray-700">Deactivate</button>
            </form>
            @endif
            <a href="{{ route('admin.phone.edit', $integration) }}" class="text-xs font-medium text-gray-600 hover:text-gray-900">Edit</a>
            <form method="POST" action="{{ route('admin.phone.destroy', $integration) }}"
                  onsubmit="return confirm('Delete this integration?')">
                @csrf @method('DELETE')
                <button class="text-xs font-medium text-red-500 hover:text-red-700">Delete</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Recent call logs --}}
<div class="mb-2 flex items-center justify-between">
    <h3 class="text-base font-semibold text-gray-900">Recent Calls</h3>
    <a href="{{ route('admin.phone.call-logs') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all →</a>
</div>

@if($recentLogs->isEmpty())
<div class="rounded-lg bg-white border border-gray-200 px-6 py-8 text-center">
    <p class="text-sm text-gray-500">No calls logged yet.</p>
</div>
@else
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
            @foreach($recentLogs as $log)
            @include('admin.phone._log-row', ['log' => $log])
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
