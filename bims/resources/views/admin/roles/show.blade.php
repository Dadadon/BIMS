@extends('layouts.app')
@section('title', 'Role: ' . $role->name)
@section('page-title', 'Roles & Permissions')

@section('content')

{{-- Header --}}
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('admin.roles.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← All Roles</a>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Left: Role details --}}
    <div class="space-y-4">
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Role Details</h3>
            </div>
            @if($role->slug === 'system_admin')
            <div class="px-5 py-4">
                <p class="text-sm font-semibold text-gray-900">{{ $role->name }}</p>
                <p class="text-xs text-gray-500 mt-1 font-mono">{{ $role->slug }}</p>
                <p class="mt-3 text-xs text-amber-700 bg-amber-50 rounded-md px-3 py-2 ring-1 ring-amber-200">
                    System Admin is a protected role. Its name and permissions cannot be edited.
                </p>
            </div>
            @else
            <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="px-5 py-4 space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Slug</label>
                    <p class="mt-1 text-sm text-gray-500 font-mono">{{ $role->slug }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_admin" id="edit_is_admin" value="1"
                           {{ $role->is_admin ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                    <label for="edit_is_admin" class="text-sm text-gray-700">Admin access</label>
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Save Changes
                </button>
            </form>
            @endif
        </div>

        {{-- Stats --}}
        <div class="bg-white shadow rounded-lg px-5 py-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Users with this role</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">
                {{ $role->users()->count() }}
            </p>
        </div>

        {{-- Danger --}}
        @if($role->slug !== 'system_admin')
        <div class="bg-white shadow rounded-lg px-5 py-4 border border-red-100">
            <h4 class="text-sm font-semibold text-red-600 mb-3">Danger Zone</h4>
            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                  onsubmit="return confirm('Permanently delete the role \'{{ $role->name }}\'?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full rounded-md bg-red-50 px-3 py-1.5 text-sm font-semibold text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-100">
                    Delete Role
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Right: Permission matrix --}}
    <div class="lg:col-span-2">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Permissions</h3>
                @if($role->slug !== 'system_admin')
                <span class="text-xs text-gray-500">Check the actions this role may perform per module.</span>
                @else
                <span class="text-xs text-amber-600">Read-only — System Admin has all permissions.</span>
                @endif
            </div>

            @if($role->slug === 'system_admin')
            {{-- System admin: read-only view --}}
            <div class="px-5 py-4">
                @foreach($permissions as $module => $perms)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ ucfirst($module) }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($perms as $perm)
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                            {{ ucfirst(str_replace('_', ' ', $perm->action)) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            @else
            {{-- Editable matrix --}}
            <form method="POST" action="{{ route('admin.roles.permissions', $role) }}">
                @csrf @method('PUT')

                @php
                    $actionLabels = [
                        'view'      => 'View',
                        'view_team' => 'View Team',
                        'view_all'  => 'View All',
                        'create'    => 'Create',
                        'edit'      => 'Edit',
                        'delete'    => 'Delete',
                        'export'    => 'Export',
                        'run'       => 'Run',
                    ];
                    // Collect all action keys that appear in any module
                    $allActions = $permissions->flatten()->pluck('action')->unique()->sort()->values();
                @endphp

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 pl-5 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Module</th>
                                @foreach($allActions as $action)
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    {{ $actionLabels[$action] ?? ucfirst(str_replace('_', ' ', $action)) }}
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($permissions as $module => $perms)
                            @php $permByAction = $perms->keyBy('action'); @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pl-5 pr-3 font-medium text-gray-900 capitalize">{{ ucfirst($module) }}</td>
                                @foreach($allActions as $action)
                                @php $perm = $permByAction->get($action); @endphp
                                <td class="px-3 py-3 text-center">
                                    @if($perm)
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $perm->id }}"
                                           {{ isset($grantedIds[$perm->id]) ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    @else
                                    <span class="text-gray-200">—</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 flex justify-between items-center">
                    <div class="flex gap-4 text-xs text-gray-500">
                        <button type="button" onclick="toggleAll(true)"
                                class="text-indigo-600 hover:text-indigo-800 font-medium">Check all</button>
                        <button type="button" onclick="toggleAll(false)"
                                class="text-gray-500 hover:text-gray-700 font-medium">Uncheck all</button>
                    </div>
                    <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Save Permissions
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
function toggleAll(state) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = state);
}
</script>
@endpush

@endsection
