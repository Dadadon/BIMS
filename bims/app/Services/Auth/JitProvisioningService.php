<?php

namespace App\Services\Auth;

use App\Models\Role;
use App\Models\User;

class JitProvisioningService
{
    public function __construct(
        private readonly DirectorySyncService $directorySync,
    ) {}

    /**
     * Find an existing user or create one from external auth attributes,
     * then run DirectorySyncService to align team/department metadata.
     *
     * @param  array{name:string,email:string,auth_provider:string,external_id:string,groups:array,department?:string|null,title?:string|null}  $attrs
     */
    public function findOrProvision(array $attrs): User
    {
        $user = User::where('auth_provider', $attrs['auth_provider'])
                    ->where('external_id', $attrs['external_id'])
                    ->first()
            ?? User::where('email', $attrs['email'])->first();

        if ($user) {
            if (! $user->status) {
                abort(403, 'Your account has been disabled. Contact your administrator.');
            }

            $sync = [
                'auth_provider' => $attrs['auth_provider'],
                'external_id'   => $attrs['external_id'],
                'name'          => $attrs['name'],
            ];

            // Re-sync role from directory groups on every login when groups are present
            if (! empty($attrs['groups'])) {
                $role = $this->mapGroupsToRole($attrs['groups']);
                $sync['role_id']  = $role->id;
                $sync['acc_type'] = $role->is_admin ? 'admin' : 'employee';
            }

            $user->fill($sync)->save();

            $this->directorySync->sync($user, $attrs);

            return $user;
        }

        // JIT: provision a new local user record
        $role = $this->mapGroupsToRole($attrs['groups'] ?? []);

        $user = User::create([
            'name'          => $attrs['name'],
            'email'         => $attrs['email'],
            'auth_provider' => $attrs['auth_provider'],
            'external_id'   => $attrs['external_id'],
            'role_id'       => $role->id,
            'acc_type'      => $role->is_admin ? 'admin' : 'employee',
            'status'        => true,
        ]);

        $this->directorySync->sync($user, $attrs);

        return $user;
    }

    /**
     * Map external group names to a BIMS role slug.
     * Reads LDAP_ROLE_MAPPING env JSON, e.g. {"Domain Admins":"system_admin"}
     * Falls back to the 'employee' role.
     */
    private function mapGroupsToRole(array $groups): Role
    {
        $mapping = config('ldap.role_mapping', []);

        foreach ($groups as $group) {
            if (isset($mapping[$group])) {
                $role = Role::where('slug', $mapping[$group])->first();
                if ($role) {
                    return $role;
                }
            }
        }

        return Role::where('slug', 'employee')->firstOrFail();
    }
}
