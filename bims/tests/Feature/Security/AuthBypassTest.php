<?php

use App\Models\HR\Employee;
use App\Models\Role;
use App\Models\User;

// Roles are seeded by migrations (system_admin, manager, employee, etc.)
// so we fetch them rather than factory-creating them.

test('unauthenticated user is redirected from admin dashboard', function () {
    $this->get(route('admin.dashboard'))
         ->assertRedirect(route('login'));
});

test('employee-role user cannot access admin dashboard', function () {
    $role = Role::where('slug', 'employee')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id, 'acc_type' => 'employee']);

    $this->actingAs($user)
         ->get(route('admin.dashboard'))
         ->assertForbidden();
});

test('unauthenticated user is redirected from admin attendance index', function () {
    $this->withoutMiddleware(\App\Http\Middleware\CheckModuleEnabled::class)
         ->get(route('admin.attendance.index'))
         ->assertRedirect(route('login'));
});

test('employee-role user cannot access admin attendance', function () {
    $role = Role::where('slug', 'employee')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id, 'acc_type' => 'employee']);

    $this->actingAs($user)
         ->withoutMiddleware(\App\Http\Middleware\CheckModuleEnabled::class)
         ->get(route('admin.attendance.index'))
         ->assertForbidden();
});

test('unauthenticated user is redirected from admin sales', function () {
    $this->withoutMiddleware(\App\Http\Middleware\CheckModuleEnabled::class)
         ->get(route('admin.sales.index'))
         ->assertRedirect(route('login'));
});

test('employee-role user cannot access admin sales index', function () {
    $role = Role::where('slug', 'employee')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id, 'acc_type' => 'employee']);

    $this->actingAs($user)
         ->withoutMiddleware(\App\Http\Middleware\CheckModuleEnabled::class)
         ->get(route('admin.sales.index'))
         ->assertForbidden();
});

test('manager can access admin sales index', function () {
    $role = Role::where('slug', 'manager')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id, 'acc_type' => 'admin']);

    $this->actingAs($user)
         ->withoutMiddleware(\App\Http\Middleware\CheckModuleEnabled::class)
         ->get(route('admin.sales.index'))
         ->assertOk();
});

test('system admin can access admin payroll', function () {
    $role = Role::where('slug', 'system_admin')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->id, 'acc_type' => 'admin']);

    $this->actingAs($user)
         ->withoutMiddleware(\App\Http\Middleware\CheckModuleEnabled::class)
         ->get(route('admin.payroll.index'))
         ->assertOk();
});
