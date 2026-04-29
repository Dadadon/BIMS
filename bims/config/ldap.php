<?php

return [
    /*
     | LDAP server connection details.
     | Leave LDAP_HOST empty to disable LDAP authentication.
     */
    'host'          => env('LDAP_HOST'),
    'port'          => (int) env('LDAP_PORT', 389),
    'base_dn'       => env('LDAP_BASE_DN'),

    /*
     | Service-account credentials used to search the directory.
     | Example bind DN: "cn=bims-svc,dc=company,dc=local"
     */
    'bind_dn'       => env('LDAP_BIND_DN'),
    'bind_password' => env('LDAP_BIND_PASSWORD'),

    /*
     | Filter used to locate a user by email address.
     | %s is replaced with the escaped email value.
     | AD example: (mail=%s)   or   (userPrincipalName=%s)
     */
    'user_filter'   => env('LDAP_USER_FILTER', '(mail=%s)'),

    /*
     | Map LDAP group CN names to BIMS role slugs.
     | Set via LDAP_ROLE_MAPPING as a JSON string:
     |   LDAP_ROLE_MAPPING={"Domain Admins":"system_admin","Managers":"manager"}
     | Unmatched users receive the "employee" role.
     */
    'role_mapping'  => json_decode(env('LDAP_ROLE_MAPPING', '{}'), true) ?? [],
];
