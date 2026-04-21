<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['role', 'employee'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id'     => ['required', 'exists:roles,id'],
            'acc_type'    => ['required', 'in:admin,employee'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role_id'     => $validated['role_id'],
            'acc_type'    => $validated['acc_type'],
            'employee_id' => $validated['employee_id'] ?? null,
            'status'      => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $roles     = Role::orderBy('name')->get();
        $employees = Employee::active()->orderBy('lastname')->get();
        return view('admin.users.edit', compact('user', 'roles', 'employees'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'unique:users,email,' . $user->id],
            'role_id'     => ['required', 'exists:roles,id'],
            'acc_type'    => ['required', 'in:admin,employee'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'password'    => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $updateData = [
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'role_id'     => $validated['role_id'],
            'acc_type'    => $validated['acc_type'],
            'employee_id' => $validated['employee_id'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function enable(User $user): RedirectResponse
    {
        $user->update(['status' => true]);
        return back()->with('success', 'User enabled.');
    }

    public function disable(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }
        $user->update(['status' => false]);
        return back()->with('success', 'User disabled.');
    }
}
