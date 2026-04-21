@extends('layouts.app')
@section('title', 'My Leaves')
@section('page-title', 'My Leaves')

@section('content')

{{-- Leave balance cards --}}
@if($balances->isNotEmpty())
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ now()->year }} Leave Balance</h3>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($balances as $b)
        @php
            $pct  = $b['days_per_year'] > 0 ? round(($b['remaining'] / $b['days_per_year']) * 100) : 0;
            $color = $pct >= 50 ? 'bg-green-500' : ($pct >= 20 ? 'bg-yellow-400' : 'bg-red-400');
        @endphp
        <div class="bg-white rounded-lg border border-gray-200 px-4 py-4">
            <p class="text-xs font-medium text-gray-500 truncate">{{ $b['name'] }}</p>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-2xl font-bold text-gray-900">{{ $b['days_per_year'] > 0 ? $b['remaining'] : '∞' }}</span>
                @if($b['days_per_year'] > 0)
                <span class="text-xs text-gray-400">/ {{ $b['days_per_year'] }} days</span>
                @endif
            </div>
            @if($b['days_per_year'] > 0)
            <div class="mt-2 h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full {{ $color }} rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="mt-1 text-[11px] text-gray-400">{{ $b['used'] }} used · {{ $b['remaining'] }} remaining</p>
            @else
            <p class="mt-1 text-[11px] text-gray-400">Unpaid · no limit</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Leave history --}}
    <div class="lg:col-span-2">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Leave History</h2>
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Type</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Dates</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Cancel</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                            {{ $req->leaveType->name ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                            {{ $req->date_from->format('M j') }}
                            – {{ $req->date_to->format('M j, Y') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @php
                                $c = match($req->status) {
                                    'Approved'  => 'bg-green-50 text-green-700 ring-green-600/20',
                                    'Rejected'  => 'bg-red-50 text-red-700 ring-red-600/20',
                                    'Cancelled' => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                    default     => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                };
                            @endphp
                            <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $c }}">{{ $req->status }}</span>
                        </td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                            @if($req->status === 'Pending')
                            <form method="POST" action="{{ route('my.leaves.cancel', $req) }}"
                                  onsubmit="return confirm('Cancel this request?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs">Cancel</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-10 text-center text-sm text-gray-500">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">{{ $requests->links() }}</div>
        @endif
    </div>

    {{-- Request form --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">File a Leave Request</h3>
        </div>
        <form method="POST" action="{{ route('my.leaves.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-900">Leave Type <span class="text-red-500">*</span></label>
                <select name="leave_type_id" required
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— Select —</option>
                    @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Start <span class="text-red-500">*</span></label>
                    <input type="date" name="date_from" required min="{{ date('Y-m-d') }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">End <span class="text-red-500">*</span></label>
                    <input type="date" name="date_to" required min="{{ date('Y-m-d') }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Reason <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3" required
                          class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"></textarea>
            </div>
            @error('date_from')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            @error('date_to')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <button type="submit"
                    class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Submit Request
            </button>
        </form>
    </div>
</div>
@endsection
