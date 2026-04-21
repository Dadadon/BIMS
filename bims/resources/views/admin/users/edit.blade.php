@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)
@section('page-title', 'Edit User')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Users</a>
</div>
<div class="max-w-lg">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white shadow rounded-lg">
        @csrf @method('PUT')
        <div class="px-6 py-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-900">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Role</label>
                    <select name="role_id"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Account Type</label>
                    <select name="acc_type"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        <option value="employee" {{ old('acc_type', $user->acc_type) === 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="admin" {{ old('acc_type', $user->acc_type) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Link to Employee (optional)</label>
                <select name="employee_id"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— Not linked —</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id', $user->employee_id) == $emp->id ? 'selected' : '' }}>{{ $emp->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="border-t border-gray-100 pt-4">
                <p class="text-sm text-gray-500 mb-3">Leave blank to keep current password.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-900">New Password</label>
                        <input type="password" name="password"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Confirm</label>
                        <input type="password" name="password_confirmation"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.users.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
