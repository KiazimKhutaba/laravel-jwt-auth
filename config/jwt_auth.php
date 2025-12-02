<?php

return [
    'user_model' => env('JWT_USER_MODEL', \App\Models\User::class),

    // JWT settings
    'secret' => env('JWT_SECRET'),              // Should be a strong secret
    'algo' => env('JWT_ALGO', 'HS256'),

    'access_ttl' => env('JWT_ACCESS_TTL', 60),      // minutes
    'refresh_ttl' => env('JWT_REFRESH_TTL', 60*24*30), // minutes (e.g. 30 days)

    // Refresh token delivery method: 'cookie' or 'body'
    'refresh_token_method' => env('JWT_REFRESH_METHOD', 'cookie'),

    // cookie name for refresh token (only used if refresh_token_method is 'cookie')
    'refresh_cookie_name' => env('JWT_REFRESH_COOKIE', 'refresh_token'),

    // Authenticated user return type: 'model' (Laravel Auth User) or 'dto' (UserDto)
    'auth_user_type' => env('JWT_AUTH_USER_TYPE', 'dto'),

    // payload defaults
    'default_claims' => [
        'iss' => env('APP_URL'),
        'aud' => env('APP_URL'),
    ],

    // optional: additional payload fields
    'payload_fields' => ['user_id', 'user_role'],

    // revoke refresh token on use?
    'rotate_refresh' => true,
];
