@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">System Users</h2>
    <button type="button" onclick="document.getElementById('new-user-modal').classList.remove('hidden')"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + Add User
    </button>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-sm font-semibold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                    </div>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $user->role->name ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                 {{ $user->acc_type === 'admin' ? 'bg-purple-50 text-purple-700 ring-purple-600/20' : 'bg-gray-50 text-gray-600 ring-gray-500/10' }}">
                        {{ ucfirst($user->acc_type) }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                 {{ $user->status ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20' }}">
                        {{ $user->status ? 'Active' : 'Disabled' }}
                    </span>
                </td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    @if($user->id !== auth()->id())
                    @if($user->status)
                    <form method="POST" action="{{ route('admin.users.disable', $user) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">Disable</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.users.enable', $user) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-900">Enable</button>
                    </form>
                    @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>

{{-- Add User Modal --}}
<div id="new-user-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Create User Account</h3>
            <button onclick="document.getElementById('new-user-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-900">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-900">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Confirm <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" required
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Account Type</label>
                    <select name="acc_type"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        <option value="employee">Employee</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('new-user-modal').classList.add('hidden')"
                        class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
