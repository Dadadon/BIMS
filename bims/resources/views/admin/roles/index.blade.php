@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Roles</h2>
        <p class="text-sm text-gray-500 mt-1">Click a role to manage its permissions.</p>
    </div>
    <button type="button" onclick="document.getElementById('new-role-modal').classList.remove('hidden')"
            class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + New Role
    </button>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Slug</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Users</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @foreach($roles as $role)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    <a href="{{ route('admin.roles.show', $role) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $role->name }}
                    </a>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono">{{ $role->slug }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @if($role->is_admin)
                    <span class="inline-flex rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Admin</span>
                    @else
                    <span class="inline-flex rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Standard</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $role->users_count }}</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-3">
                    <a href="{{ route('admin.roles.show', $role) }}"
                       class="text-indigo-600 hover:text-indigo-900">Edit Permissions</a>
                    @if($role->slug !== 'system_admin')
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline"
                          onsubmit="return confirm('Delete role \'{{ $role->name }}\'? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- New Role Modal --}}
<div id="new-role-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between">
            <h3 class="text-sm font-semibold text-gray-900">New Role</h3>
            <button onclick="document.getElementById('new-role-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.roles.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-900">
                    Role Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required placeholder="e.g. Billing Manager"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_admin" id="is_admin" value="1"
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                <label for="is_admin" class="text-sm text-gray-700">
                    Admin access <span class="text-gray-400 text-xs">(can reach /admin routes)</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" onclick="document.getElementById('new-role-modal').classList.add('hidden')"
                        class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.getElementById('new-role-modal').classList.remove('hidden')</script>
@endif
@endsection
