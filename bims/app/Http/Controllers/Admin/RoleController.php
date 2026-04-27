<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles       = Role::withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('module_key')->orderBy('action')->get()
                        ->groupBy('module_key');

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function show(Role $role): View
    {
        $permissions    = Permission::orderBy('module_key')->orderBy('action')->get()
                            ->groupBy('module_key');
        $grantedIds     = $role->permissions()->pluck('permissions.id')->flip();

        return view('admin.roles.show', compact('role', 'permissions', 'grantedIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:80', 'unique:roles,name'],
            'is_admin' => ['boolean'],
        ]);

        $slug = Str::slug($validated['name'], '_');
        if (Role::where('slug', $slug)->exists()) {
            $slug .= '_' . substr(uniqid(), -4);
        }

        $role = Role::create([
            'name'     => $validated['name'],
            'slug'     => $slug,
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()->route('admin.roles.show', $role)
            ->with('success', "Role \"{$role->name}\" created. Now assign its permissions below.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->slug === 'system_admin') {
            return back()->with('error', 'The System Admin role cannot be renamed.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:80', 'unique:roles,name,' . $role->id],
            'is_admin' => ['boolean'],
        ]);

        $role->update([
            'name'     => $validated['name'],
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return back()->with('success', 'Role updated.');
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        if ($role->slug === 'system_admin') {
            return back()->with('error', 'System Admin always has all permissions — nothing to change.');
        }

        $ids = collect($request->input('permissions', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->toArray();

        // Validate that all submitted IDs actually exist
        $validIds = Permission::whereIn('id', $ids)->pluck('id')->toArray();
        $role->permissions()->sync($validIds);

        return back()->with('success', 'Permissions saved.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->slug === 'system_admin') {
            return back()->with('error', 'The System Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', "Cannot delete \"{$role->name}\" — {$role->users()->count()} user(s) are assigned to it.");
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" deleted.");
    }
}
