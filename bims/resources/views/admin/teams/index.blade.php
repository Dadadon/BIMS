@extends('layouts.app')
@section('title', 'Teams')
@section('page-title', 'Teams')

@section('content')
<div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

    {{-- Teams list --}}
    <div class="lg:col-span-2 space-y-4">
        <h2 class="text-xl font-semibold text-gray-900">All Teams</h2>

        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Team</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Leader</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Members</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Total Sales</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Status</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody x-data class="divide-y divide-gray-200 bg-white">
                    @forelse($teams as $team)
                    <tr x-data="{ editing: false }" class="hover:bg-gray-50">
                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <p class="font-medium text-gray-900">{{ $team->name }}</p>
                            @if($team->description)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $team->description }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-700">
                            {{ $team->leader?->display_name ?? '—' }}
                        </td>
                        <td class="px-3 py-4 text-sm text-center text-gray-700">
                            {{ $team->members->count() }}
                        </td>
                        <td class="px-3 py-4 text-sm text-center text-gray-700">
                            {{ number_format($team->sales_count) }}
                        </td>
                        <td class="px-3 py-4 text-sm text-center">
                            @if($team->is_active)
                            <span class="inline-flex rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                            @else
                            <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactive</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6 space-x-2">
                            <button @click="editing = !editing" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" class="inline"
                                  onsubmit="return confirm('Delete {{ $team->name }}? Members will be unassigned.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    {{-- Edit row --}}
                    <tr x-show="editing" x-cloak class="bg-indigo-50">
                        <td colspan="6" class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.teams.update', $team) }}"
                                  class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                @csrf @method('PUT')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Name</label>
                                    <input type="text" name="name" value="{{ $team->name }}" required
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Leader</label>
                                    <select name="leader_id" class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                        <option value="">— None —</option>
                                        @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ $team->leader_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->lastname }}, {{ $emp->firstname }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Description</label>
                                    <input type="text" name="description" value="{{ $team->description }}" maxlength="255"
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                </div>
                                <div class="sm:col-span-3 flex items-center gap-6">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" {{ $team->is_active ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600">
                                        Active
                                    </label>
                                    <button type="submit" class="ml-auto rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-sm text-gray-400">No teams yet. Create one using the form.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Member breakdown --}}
        @if($teams->where('members_count', '>', 0)->isNotEmpty() || $teams->count())
        <div class="mt-6 space-y-4">
            <h3 class="text-base font-semibold text-gray-900">Team Rosters</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach($teams as $team)
                <div class="rounded-lg bg-white shadow p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-900 text-sm">{{ $team->name }}</h4>
                        <a href="{{ route('admin.sales.filter', ['team_id' => $team->id]) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-900">View sales →</a>
                    </div>
                    @if($team->members->isEmpty())
                    <p class="text-xs text-gray-400 italic">No members assigned.</p>
                    @else
                    <ul class="space-y-1">
                        @foreach($team->members as $member)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-700">
                                {{ $member->display_name }}
                                @if($member->id === $team->leader_id)
                                <span class="ml-1 text-xs text-amber-600 font-medium">Leader</span>
                                @endif
                            </span>
                            <span class="text-xs text-gray-400">{{ $member->jobTitle?->title }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Create team --}}
    <div>
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">New Team</h3>
            </div>
            <form method="POST" action="{{ route('admin.teams.store') }}" class="px-6 py-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-900">Team Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Alpha Team"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Team Leader</label>
                    <select name="leader_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                        <option value="">— None —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('leader_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->lastname }}, {{ $emp->firstname }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" maxlength="255"
                           placeholder="Optional short description"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Create Team
                </button>
            </form>
        </div>

        <div class="mt-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-xs text-blue-700 space-y-1">
            <p class="font-semibold">How team assignment works</p>
            <p>Assign employees to teams from the <a href="{{ route('admin.employees.index') }}" class="underline">Employees</a> edit page.</p>
            <p>When a sale is logged, the employee's current team is stamped on it — switching teams later doesn't affect past sales.</p>
        </div>
    </div>
</div>
@endsection
