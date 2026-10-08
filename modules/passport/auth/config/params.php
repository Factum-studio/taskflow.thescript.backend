<?php

declare(strict_types=1);

return [
    'passport_url' => rtrim((string)($_ENV['PASSPORT_URL'] ?? 'https://passport.thescript.agency'), '/'),
    'frontend_url' => rtrim((string)($_ENV['FRONTEND_URL'] ?? 'https://taskflow.thescript.agency'), '/'),
    'app_url' => rtrim((string)($_ENV['APP_URL'] ?? 'https://api.taskflow.thescript.agency'), '/'),

    'oauth2_client_id' => (string)($_ENV['OAUTH2_CLIENT_ID'] ?? ''),
    'oauth2_client_secret' => (string)($_ENV['OAUTH2_CLIENT_SECRET'] ?? ''),
    'oauth2_public_key_path' => (string)($_ENV['OAUTH2_PUBLIC_KEY_PATH'] ?? ''),
    'oauth2_access_token_ttl' => (int)($_ENV['OAUTH2_ACCESS_TOKEN_TTL'] ?? 3600),
    'oauth2_refresh_token_ttl' => (int)($_ENV['OAUTH2_REFRESH_TOKEN_TTL'] ?? 2592000),

    'access_token_cookie_name' => 'taskflow_access_token',
    'refresh_token_cookie_name' => 'taskflow_refresh_token',
    'state_cookie_name' => 'taskflow_oauth_state',

    'scopes' => [
        'user:read',
        'user:write',
        'user-educations:read',
        'user-educations:write',
        'user-contacts:read',
        'user-contacts:write',
    ],

    'public_routes' => [
        'GET /',
        'GET /auth/*',
        'GET /gii',
        'GET /debug',
        'GET /debug/*',
        'GET /docs',
        'GET /docs/*',
        'OPTIONS *',
    ],
];
