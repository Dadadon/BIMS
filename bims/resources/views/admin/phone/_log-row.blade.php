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
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 font-mono">{{ $log->getRemoteNumber() }}</td>
    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->employee?->display_name ?? '—' }}</td>
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
