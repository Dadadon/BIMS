@extends('layouts.app')
@section('title', 'SmartClock')
@section('page-title', 'SmartClock')

@section('content')
@if(! $employee)
<div class="rounded-md bg-yellow-50 p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to an employee record. Please contact your administrator.</p>
</div>
@else

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-start">

    {{-- Left column: clock + actions --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Clock + status combined --}}
        <div class="rounded-xl bg-gray-900 text-white overflow-hidden">
            {{-- Time --}}
            <div class="px-6 pt-6 pb-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-widest">{{ now()->format('l') }}</p>
                <p class="text-sm text-gray-400 mt-0.5">{{ now()->format('F j, Y') }}</p>
                <p class="mt-2 text-5xl font-bold tabular-nums tracking-tight" id="live-clock">{{ now()->format('g:i:s A') }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ config('app.timezone') }}</p>
            </div>

            {{-- Status pill --}}
            <div class="px-6 pb-5">
                @if($openLog)
                <div class="flex items-center gap-2.5 rounded-lg bg-green-500/15 border border-green-500/30 px-3 py-2.5">
                    <span class="flex h-2.5 w-2.5 relative flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-400"></span>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-green-300">Clocked In</p>
                        <p class="text-xs text-green-400/80">Since {{ $openLog->clock_in->format('g:i A') }} · {{ $openLog->reason }}</p>
                    </div>
                </div>
                @else
                <div class="flex items-center gap-2.5 rounded-lg bg-white/5 border border-white/10 px-3 py-2.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-gray-500 flex-shrink-0"></span>
                    <p class="text-xs text-gray-400">Not clocked in</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Clock In / Out --}}
        @if(! $openLog)
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Clock In</h3>
            <form method="POST" action="{{ route('clock.in') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                    <select name="reason"
                            class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                        <option value="Shift">Shift</option>
                        <option value="Lunch">Lunch Return</option>
                        <option value="Break">Break Return</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Comment <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="comment" placeholder="e.g. WFH today"
                           class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                </div>
                <button type="submit"
                        class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors">
                    Clock In
                </button>
            </form>
        </div>
        @else
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Clock Out</h3>
            <form method="POST" action="{{ route('clock.out') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                    <select name="reason"
                            class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                        <option value="Shift">End of Shift</option>
                        <option value="Lunch">Lunch Break</option>
                        <option value="Break">Short Break</option>
                    </select>
                </div>
                <button type="submit"
                        class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                    Clock Out
                </button>
            </form>
        </div>
        @endif

        {{-- Log a Sale --}}
        @module('sales')
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex w-full items-center justify-between px-5 py-4 text-sm font-semibold text-gray-900 hover:bg-gray-50 transition-colors">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Log a Sale
                </span>
                <svg class="h-4 w-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="border-t border-gray-100 px-5 py-4">
                <form method="POST" action="{{ route('clock.sale') }}" class="space-y-3"
                      x-data="clockSaleForm()" x-init="init()">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sale Type <span class="text-red-500">*</span></label>
                        <select name="sale_type_id" required x-model="saleTypeId"
                                class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                            <option value="">— Select —</option>
                            @foreach($saleTypes as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" required
                               class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Customer Phone</label>
                        <input type="text" name="customer_phone"
                               class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sale Date <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" required value="{{ now()->format('Y-m-d') }}"
                               class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>

                    {{-- Dynamic custom fields --}}
                    <template x-for="field in visibleFields" :key="field.key">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                <span x-text="field.label"></span>
                                <span x-show="field.is_required" class="text-red-500">*</span>
                                <span x-show="field.field_type === 'calculated'" class="ml-1 text-xs font-normal text-purple-600">auto</span>
                            </label>
                            <template x-if="field.field_type === 'calculated'">
                                <input type="text" readonly
                                       :value="calcValues[field.key] !== undefined ? calcValues[field.key] : '—'"
                                       class="block w-full rounded-lg border-0 py-2 px-3 bg-purple-50 text-purple-800 shadow-sm ring-1 ring-inset ring-purple-200 text-sm cursor-not-allowed">
                            </template>
                            <template x-if="field.field_type === 'select'">
                                <select :name="'meta_' + field.key" :required="field.is_required"
                                        @change="metaValues[field.key] = $event.target.value; recompute()"
                                        class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                                    <option value="">— Select —</option>
                                    <template x-for="opt in field.options" :key="opt">
                                        <option :value="opt" x-text="opt"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="field.field_type === 'textarea'">
                                <textarea :name="'meta_' + field.key" :required="field.is_required" rows="2"
                                          @input="metaValues[field.key] = $event.target.value; recompute()"
                                          class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm"></textarea>
                            </template>
                            <template x-if="field.field_type === 'checkbox'">
                                <input type="checkbox" :name="'meta_' + field.key" value="1"
                                       @change="metaValues[field.key] = $event.target.checked; recompute()"
                                       class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600">
                            </template>
                            <template x-if="!['select','textarea','checkbox','calculated'].includes(field.field_type)">
                                <input :type="field.field_type === 'number' ? 'number' : (field.field_type === 'date' ? 'date' : 'text')"
                                       :name="'meta_' + field.key" :required="field.is_required"
                                       @input="metaValues[field.key] = $event.target.value; recompute()"
                                       class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                            </template>
                        </div>
                    </template>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Remarks</label>
                        <input type="text" name="remarks"
                               class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        Submit Sale
                    </button>
                </form>
            </div>
        </div>
        @endmodule

    </div>

    {{-- Right column: Today's log --}}
    <div class="lg:col-span-2">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Today's Log</h3>
                <span class="text-xs text-gray-400">{{ now()->format('F j, Y') }}</span>
            </div>

            @if($todayLogs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <svg class="h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-gray-500">No records for today yet.</p>
                <p class="text-xs text-gray-400 mt-1">Clock in to start tracking your time.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Clock In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Clock Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @foreach($todayLogs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap px-6 py-3.5 text-sm font-medium text-gray-900">{{ $log->reason }}</td>
                            <td class="whitespace-nowrap px-6 py-3.5 text-sm text-gray-600">{{ $log->clock_in->format('g:i A') }}</td>
                            <td class="whitespace-nowrap px-6 py-3.5 text-sm text-gray-600">
                                {{ $log->clock_out ? $log->clock_out->format('g:i A') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-3.5 text-sm text-gray-600">
                                {{ $log->clock_out ? $log->duration : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-3.5 text-sm">
                                @php
                                    $statusLabel = $log->status_out ?? $log->status_in;
                                    $statusColor = match(true) {
                                        in_array($statusLabel, ['In Time', 'On Time']) => 'bg-green-50 text-green-700 ring-green-600/20',
                                        in_array($statusLabel, ['Late In'])            => 'bg-red-50 text-red-700 ring-red-600/20',
                                        in_array($statusLabel, ['Early Out'])          => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                        default                                        => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                    };
                                @endphp
                                <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $statusColor }}">
                                    {{ $statusLabel ?? 'Open' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($todayLogs->whereNotNull('clock_out')->isNotEmpty())
            @php
                $totalMinutes = $todayLogs->whereNotNull('clock_out')->where('reason', 'Shift')->sum('total_minutes');
                $hours = intdiv($totalMinutes, 60);
                $mins  = $totalMinutes % 60;
            @endphp
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500">Shift entries only</p>
                <p class="text-sm text-gray-700">
                    Total: <span class="font-semibold text-gray-900">{{ $hours }}h {{ $mins }}m</span>
                </p>
            </div>
            @endif
            @endif
        </div>
    </div>

</div>
@endif

@push('scripts')
<script>
    function updateClock() {
        const el = document.getElementById('live-clock');
        if (!el) return;
        const now = new Date();
        const h = now.getHours() % 12 || 12;
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        el.textContent = `${h}:${m}:${s} ${now.getHours() >= 12 ? 'PM' : 'AM'}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
<script>
function clockSaleForm() {
    const allFields = @json($saleFieldsAll);
    const byType    = @json($saleFieldsByType->map->values());

    return {
        saleTypeId: '',
        visibleFields: [],
        metaValues: {},
        calcValues: {},

        init() {
            this.$watch('saleTypeId', () => this.updateFields());
            this.updateFields();
        },

        updateFields() {
            const id = String(this.saleTypeId);
            const typeFields = byType[id] ?? [];
            this.visibleFields = [...allFields, ...typeFields];
            this.metaValues = {};
            this.calcValues = {};
            this.recompute();
        },

        recompute() {
            this.visibleFields.filter(f => f.field_type === 'calculated').forEach(field => {
                if (!field.formula) return;
                this.calcValues[field.key] = this.evalFormula(field.formula);
            });
        },

        evalFormula(formula) {
            const ctx = {};
            Object.assign(ctx, this.metaValues);
            let expr = formula;
            Object.keys(ctx).sort((a, b) => b.length - a.length).forEach(k => {
                const v = ctx[k];
                const safe = (v === null || v === undefined || v === '') ? 0
                           : (typeof v === 'boolean') ? (v ? 1 : 0)
                           : isNaN(Number(v)) ? JSON.stringify(String(v))
                           : Number(v);
                expr = expr.replaceAll(k, safe);
            });
            try {
                const result = Function('"use strict"; return (' + expr + ')')();
                return (typeof result === 'number' && !isNaN(result))
                    ? Math.round(result * 10000) / 10000 : result;
            } catch { return '—'; }
        },
    };
}
</script>
@endpush
@endsection
