<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Default permission grants per non-admin role.
     *
     * Admins (system_admin, manager) bypass all permission checks via is_admin=true.
     * Only employee, team_lead_l1, and team_lead_l2 need explicit grants.
     */
    private array $grants = [
        'employee' => [
            ['sales',      'view'],
            ['sales',      'create'],
            ['attendance', 'view'],
            ['leaves',     'view'],
            ['leaves',     'create'],
            ['tasks',      'view'],
            ['tasks',      'create'],
            ['chat',       'view'],
            ['chat',       'create'],
        ],
        'team_lead_l1' => [
            ['sales',       'view_team'],
            ['sales',       'create'],
            ['attendance',  'view_team'],
            ['leaves',      'view_team'],
            ['performance', 'view_team'],
            ['tasks',       'view'],
            ['tasks',       'create'],
            ['tasks',       'edit'],
            ['chat',        'view'],
            ['chat',        'create'],
        ],
        'team_lead_l2' => [
            ['sales',       'view_team'],
            ['sales',       'create'],
            ['sales',       'edit'],
            ['attendance',  'view_team'],
            ['attendance',  'edit'],
            ['leaves',      'view_team'],
            ['leaves',      'create'],
            ['leaves',      'edit'],
            ['performance', 'view_team'],
            ['performance', 'create'],
            ['tasks',       'view'],
            ['tasks',       'create'],
            ['tasks',       'edit'],
            ['tasks',       'delete'],
            ['chat',        'view'],
            ['chat',        'create'],
            ['chat',        'edit'],
            ['reports',     'view'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->grants as $roleSlug => $pairs) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');
            if (! $roleId) continue;

            foreach ($pairs as [$module, $action]) {
                $permId = DB::table('permissions')
                    ->where('module_key', $module)
                    ->where('action', $action)
                    ->value('id');

                if (! $permId) continue;

                DB::table('role_permissions')->insertOrIgnore([
                    'role_id'       => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->grants as $roleSlug => $pairs) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');
            if (! $roleId) continue;

            foreach ($pairs as [$module, $action]) {
                $permId = DB::table('permissions')
                    ->where('module_key', $module)
                    ->where('action', $action)
                    ->value('id');

                if ($permId) {
                    DB::table('role_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $permId)
                        ->delete();
                }
            }
        }
    }
};
