<?php

namespace App\Services\Auth;

use App\Models\Role;
use App\Models\User;

class JitProvisioningService
{
    /**
     * Find an existing user or create one from external auth attributes.
     *
     * @param  array{name:string,email:string,provider:string,provider_id:string,groups:array}  $attrs
     */
    public function findOrProvision(array $attrs): User
    {
        // Prefer matching on provider+provider_id; fall back to email
        $user = User::where('provider', $attrs['provider'])
                    ->where('provider_id', $attrs['provider_id'])
                    ->first()
            ?? User::where('email', $attrs['email'])->first();

        if ($user) {
            if (! $user->status) {
                abort(403, 'Your account has been disabled. Contact your administrator.');
            }

            $sync = [
                'provider'    => $attrs['provider'],
                'provider_id' => $attrs['provider_id'],
                'name'        => $attrs['name'],
            ];

            // Re-sync role from directory groups on every login when groups are present
            if (! empty($attrs['groups'])) {
                $role = $this->mapGroupsToRole($attrs['groups']);
                $sync['role_id']  = $role->id;
                $sync['acc_type'] = $role->is_admin ? 'admin' : 'employee';
            }

            $user->fill($sync)->save();

            return $user;
        }

        // JIT: provision a new local user record
        $role = $this->mapGroupsToRole($attrs['groups'] ?? []);

        return User::create([
            'name'        => $attrs['name'],
            'email'       => $attrs['email'],
            'provider'    => $attrs['provider'],
            'provider_id' => $attrs['provider_id'],
            'role_id'     => $role->id,
            'acc_type'    => $role->is_admin ? 'admin' : 'employee',
            'status'      => true,
        ]);
    }

    /**
     * Map external group names to a BIMS role slug.
     * Reads LDAP_ROLE_MAPPING env JSON, e.g. {"Domain Admins":"system_admin","IT Staff":"manager"}
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
