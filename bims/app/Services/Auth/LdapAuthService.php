<?php

namespace App\Services\Auth;

class LdapAuthService
{
    /**
     * Attempt LDAP authentication using PHP's native ldap extension.
     *
     * Returns:
     *   array  — auth succeeded; contains name, email, dn, groups
     *   false  — wrong credentials (user found but password incorrect)
     *   null   — LDAP unreachable or not configured (caller should fall back to local auth)
     */
    public function attempt(string $email, string $password): array|false|null
    {
        $host = config('ldap.host');

        if (! $host || ! extension_loaded('ldap')) {
            return null;
        }

        // Blank passwords would bind anonymously on many servers — reject immediately
        if ($password === '' || $password === null) {
            return false;
        }

        try {
            $conn = @ldap_connect($host, (int) config('ldap.port', 389));
            if (! $conn) {
                return null;
            }

            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 3);

            $bindDn = config('ldap.bind_dn');
            $bindPw = config('ldap.bind_password');

            if (! @ldap_bind($conn, $bindDn, $bindPw)) {
                return null;
            }

            $baseDn    = config('ldap.base_dn');
            $filterTpl = config('ldap.user_filter', '(mail=%s)');
            $filter    = sprintf($filterTpl, ldap_escape($email, '', LDAP_ESCAPE_FILTER));

            $search = @ldap_search(
                $conn, $baseDn, $filter,
                ['dn', 'cn', 'displayname', 'mail', 'memberof', 'department', 'title']
            );

            if (! $search) {
                return null;
            }

            $entries = ldap_get_entries($conn, $search);
            if ($entries['count'] === 0) {
                return false;
            }

            $entry    = $entries[0];
            $userDn   = $entry['dn'];
            $userName = $entry['displayname'][0] ?? $entry['cn'][0] ?? $email;
            $groups   = $this->extractGroups($entry['memberof'] ?? []);

            if (! @ldap_bind($conn, $userDn, $password)) {
                return false;
            }

            @ldap_close($conn);

            return [
                'name'       => $userName,
                'email'      => $email,
                'dn'         => $userDn,
                'groups'     => $groups,
                'department' => $entry['department'][0] ?? null,
                'title'      => $entry['title'][0] ?? null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /** Extract CN values from a memberOf attribute array. */
    private function extractGroups(array $memberOf): array
    {
        $groups = [];
        $count  = $memberOf['count'] ?? 0;

        for ($i = 0; $i < $count; $i++) {
            if (preg_match('/^CN=([^,]+)/i', $memberOf[$i], $m)) {
                $groups[] = $m[1];
            }
        }

        return $groups;
    }
}
