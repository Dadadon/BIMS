@extends('layouts.app')
@section('title', 'Schedules')
@section('page-title', 'Scheduling')

@section('content')
<div class="grid grid-cols-1 gap-8 xl:grid-cols-3">

    {{-- ── Left: Roster + Assignments ───────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-8">

        {{-- Week Roster --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Weekly Roster</h2>
                <div class="flex items-center gap-2 text-sm">
                    <a href="{{ route('admin.schedules.index', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}"
                       class="rounded-md bg-white px-3 py-1.5 font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">← Prev</a>
                    <span class="font-medium text-gray-700">
                        {{ $weekStart->format('M j') }} – {{ $weekStart->copy()->endOfWeek(Carbon\Carbon::SUNDAY)->format('M j, Y') }}
                    </span>
                    <a href="{{ route('admin.schedules.index', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}"
                       class="rounded-md bg-white px-3 py-1.5 font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Next →</a>
                    <a href="{{ route('admin.schedules.index') }}"
                       class="rounded-md bg-indigo-600 px-3 py-1.5 font-semibold text-white hover:bg-indigo-500">Today</a>
                </div>
            </div>

            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase sm:pl-6 min-w-[150px]">Employee</th>
                            @foreach($days as $day)
                            <th class="px-2 py-3 text-center text-xs font-semibold uppercase
                                {{ $day->isToday() ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500' }}">
                                <div>{{ $day->format('D') }}</div>
                                <div class="text-base font-bold mt-0.5">{{ $day->format('j') }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($employees as $emp)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                {{ $emp->display_name }}
                            </td>
                            @foreach($days as $day)
                            @php $sched = $roster[$emp->id][$day->toDateString()] ?? null; @endphp
                            <td class="px-1 py-2 text-center">
                                @if($sched)
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium text-white"
                                      style="background-color: {{ $sched->color() }}">
                                    {{ $sched->label() }}
                                </span>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($sched->shift_in)->format('g:i A') }}
                                </div>
                                @else
                                <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($days) + 1 }}" class="py-10 text-center text-sm text-gray-400">
                                No active employees found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Active Assignments --}}
        <div>
            <h3 class="text-base font-semibold text-gray-900 mb-3">Active Assignments</h3>
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase sm:pl-6">Employee</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Shift</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Time</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Days</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Effective</th>
                            <th class="relative py-3 pl-3 pr-4 sm:pr-6"><span class="sr-only">Remove</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($assignments as $a)
                        @php
                            $dayNames = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            $daysLabel = $a->days_of_week
                                ? collect($a->days_of_week)->sort()->map(fn($d) => $dayNames[$d] ?? $d)->join(', ')
                                : 'Every day';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                {{ $a->employee->display_name ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-sm">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium text-white"
                                      style="background-color: {{ $a->color() }}">
                                    {{ $a->label() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($a->shift_in)->format('g:i A') }}
                                – {{ \Carbon\Carbon::parse($a->shift_out)->format('g:i A') }}
                                @if($a->is_overnight) <span class="text-xs text-amber-600">+1</span>@endif
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-500">{{ $daysLabel }}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                {{ $a->effective_from?->format('M j, Y') ?? '—' }}
                                @if($a->effective_to)
                                → {{ $a->effective_to->format('M j, Y') }}
                                @else
                                → ongoing
                                @endif
                            </td>
                            <td class="whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm sm:pr-6">
                                <form method="POST" action="{{ route('admin.schedules.destroy', $a) }}"
                                      onsubmit="return confirm('Remove this schedule assignment?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-xs">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-8 text-center text-sm text-gray-400">No assignments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Right: New Assignment + Templates ─────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Assign Shift --}}
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Assign Shift</h3>
            </div>
            <form method="POST" action="{{ route('admin.schedules.store') }}"
                  class="px-6 py-5 space-y-4" x-data="{ useTemplate: true }">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-900">Employee <span class="text-red-500">*</span></label>
                    <select name="employee_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                        <option value="">— Select —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center gap-4 mb-2">
                        <label class="text-sm font-medium text-gray-900">Shift</label>
                        <label class="flex items-center gap-1 text-xs text-gray-500 cursor-pointer">
                            <input type="checkbox" x-model="useTemplate" class="rounded border-gray-300 text-indigo-600"> Use template
                        </label>
                    </div>

                    {{-- Template select --}}
                    <div x-show="useTemplate">
                        <select name="shift_template_id"
                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                            <option value="">— None / custom —</option>
                            @foreach($templates as $t)
                            <option value="{{ $t->id }}" data-color="{{ $t->color }}">
                                {{ $t->name }} ({{ \Carbon\Carbon::parse($t->shift_in)->format('g:i A') }} – {{ \Carbon\Carbon::parse($t->shift_out)->format('g:i A') }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Custom times --}}
                    <div x-show="!useTemplate" class="grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <label class="block text-xs text-gray-600">Shift In</label>
                            <input type="time" name="shift_in"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Shift Out</label>
                            <input type="time" name="shift_out"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input type="checkbox" name="is_overnight" value="1" id="overnight"
                                   class="rounded border-gray-300 text-indigo-600">
                            <label for="overnight" class="text-xs text-gray-600">Overnight (crosses midnight)</label>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Break (min)</label>
                            <input type="number" name="break_minutes" value="0" min="0" max="480"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Label</label>
                            <input type="text" name="name" maxlength="100" placeholder="e.g. AM Shift"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Days of week --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-1">Days of Week</label>
                    <p class="text-xs text-gray-500 mb-2">Leave all unchecked to apply every day.</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $num => $label)
                        <label class="flex items-center gap-1 text-xs cursor-pointer">
                            <input type="checkbox" name="days_of_week[]" value="{{ $num }}"
                                   class="rounded border-gray-300 text-indigo-600">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Effective dates --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Effective From</label>
                        <input type="date" name="effective_from" value="{{ now()->toDateString() }}"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Effective To</label>
                        <input type="date" name="effective_to" placeholder="Leave blank for ongoing"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Assign Shift
                </button>
            </form>
        </div>

        {{-- Shift Templates --}}
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Shift Templates</h3>
            </div>

            {{-- Template list --}}
            @if($templates->isNotEmpty())
            <ul class="divide-y divide-gray-100">
                @foreach($templates as $t)
                <li x-data="{ editing: false }" class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $t->color }}"></span>
                            <span class="text-sm font-medium text-gray-900">{{ $t->name }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            <span>{{ \Carbon\Carbon::parse($t->shift_in)->format('g:i A') }} – {{ \Carbon\Carbon::parse($t->shift_out)->format('g:i A') }}</span>
                            <button @click="editing = !editing" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <form method="POST" action="{{ route('admin.schedules.templates.destroy', $t) }}" class="inline"
                                  onsubmit="return confirm('Delete template \'{{ $t->name }}\'?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                    {{-- Inline edit --}}
                    <div x-show="editing" x-cloak class="mt-3 bg-gray-50 rounded-md p-3">
                        <form method="POST" action="{{ route('admin.schedules.templates.update', $t) }}"
                              class="space-y-3">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-2 gap-2">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-700">Name</label>
                                    <input type="text" name="name" value="{{ $t->name }}" required
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">In</label>
                                    <input type="time" name="shift_in" value="{{ \Carbon\Carbon::parse($t->shift_in)->format('H:i') }}" required
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Out</label>
                                    <input type="time" name="shift_out" value="{{ \Carbon\Carbon::parse($t->shift_out)->format('H:i') }}" required
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Break (min)</label>
                                    <input type="number" name="break_minutes" value="{{ $t->break_minutes }}" min="0" max="480"
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Color</label>
                                    <input type="color" name="color" value="{{ $t->color }}"
                                           class="mt-1 h-9 w-full rounded border-gray-300 cursor-pointer">
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-1 text-xs text-gray-600">
                                    <input type="hidden" name="is_overnight" value="0">
                                    <input type="checkbox" name="is_overnight" value="1" {{ $t->is_overnight ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600"> Overnight
                                </label>
                                <label class="flex items-center gap-1 text-xs text-gray-600">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600"> Active
                                </label>
                                <button type="submit"
                                        class="ml-auto rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif

            {{-- New template form --}}
            <div class="px-4 py-4 border-t border-gray-100 bg-gray-50 rounded-b-lg">
                <p class="text-xs font-medium text-gray-700 mb-3">New Template</p>
                <form method="POST" action="{{ route('admin.schedules.templates.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div class="col-span-2">
                            <input type="text" name="name" placeholder="Template name (e.g. Morning Shift)" required
                                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Shift In</label>
                            <input type="time" name="shift_in" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Shift Out</label>
                            <input type="time" name="shift_out" required
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Break (min)</label>
                            <input type="number" name="break_minutes" value="0" min="0" max="480"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Color</label>
                            <input type="color" name="color" value="#6366f1"
                                   class="mt-1 h-9 w-full rounded-md border-gray-300 cursor-pointer">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1 text-xs text-gray-600">
                            <input type="hidden" name="is_overnight" value="0">
                            <input type="checkbox" name="is_overnight" value="1"
                                   class="rounded border-gray-300 text-indigo-600"> Overnight
                        </label>
                        <button type="submit"
                                class="ml-auto rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
