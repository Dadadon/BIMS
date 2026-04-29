<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Third Party Services
     |--------------------------------------------------------------------------
     */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
     |--------------------------------------------------------------------------
     | Microsoft Entra ID / Azure AD (OIDC)
     |--------------------------------------------------------------------------
     | Set OIDC_CLIENT_ID and OIDC_CLIENT_SECRET to enable Microsoft SSO.
     | OIDC_TENANT_ID defaults to "common" (any Microsoft org/personal account).
     | Restrict to your tenant by setting OIDC_TENANT_ID to your tenant GUID.
     */
    'azure' => [
        'client_id'     => env('OIDC_CLIENT_ID'),
        'client_secret' => env('OIDC_CLIENT_SECRET'),
        'redirect'      => env('OIDC_REDIRECT_URI', '/auth/oidc/callback'),
        'tenant'        => env('OIDC_TENANT_ID', 'common'),
    ],

];
