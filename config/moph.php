<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MOPH Provider ID & Health ID Single Sign-On (SSO) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for authenticating medical & health personnel via Health ID
    | (moph.id.th) and Provider ID (provider.id.th) services.
    |
    */

    'provider_id_active' => env('MOPH_PROVIDER_ID_ACTIVE', 'Y'),

    'health_id' => [
        'client_id' => env('MOPH_HEALTH_ID_CLIENT_ID', ''),
        'client_secret' => env('MOPH_HEALTH_ID_CLIENT_SECRET', ''),
        'auth_url' => env('MOPH_HEALTH_ID_AUTH_URL', 'https://moph.id.th/oauth/redirect'),
        'token_url' => env('MOPH_HEALTH_ID_TOKEN_URL', 'https://moph.id.th/api/v1/token'),
    ],

    'provider_id' => [
        'client_id' => env('MOPH_PROVIDER_ID_CLIENT_ID', ''),
        'secret_key' => env('MOPH_PROVIDER_ID_SECRET_KEY', ''),
        'token_url' => env('MOPH_PROVIDER_ID_TOKEN_URL', 'https://provider.id.th/api/v1/services/token'),
        'profile_url' => env('MOPH_PROVIDER_ID_PROFILE_URL', 'https://provider.id.th/api/v1/services/profile'),
    ],

    'alert' => [
        'active' => env('MOPH_ALERT_ACTIVE', 'N'),
        'client_id' => env('MOPH_ALERT_CLIENT_ID', ''),
        'client_secret' => env('MOPH_ALERT_CLIENT_SECRET', ''),
        'url' => env('MOPH_ALERT_URL', 'https://morpromt2c.moph.go.th/alert/v3.1/messages'),
    ],
];
