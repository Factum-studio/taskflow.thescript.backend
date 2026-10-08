# Конфигурация модуля passport/auth

> Документ описывает, откуда модуль берёт параметры, что задаётся через `.env`, что через PHP-параметры, а что жёстко в коде.

## 1. Файлы конфигурации

### 1.1. `config/params.php` — основные URL и OAuth2

```php
return [
    'passport_url'     => rtrim($_ENV['PASSPORT_URL'] ?? 'https://passport.thescript.agency', '/'),
    'frontend_url'     => rtrim($_ENV['FRONTEND_URL'] ?? 'https://taskflow.thescript.agency', '/'),
    'app_url'          => rtrim($_ENV['APP_URL'] ?? 'https://api.taskflow.thescript.agency', '/'),

    'oauth2_client_id'           => (string)($_ENV['OAUTH2_CLIENT_ID'] ?? ''),
    'oauth2_client_secret'       => (string)($_ENV['OAUTH2_CLIENT_SECRET'] ?? ''),
    'oauth2_public_key_path'     => (string)($_ENV['OAUTH2_PUBLIC_KEY_PATH'] ?? ''),
    'oauth2_access_token_ttl'    => (int)($_ENV['OAUTH2_ACCESS_TOKEN_TTL'] ?? 3600),
    'oauth2_refresh_token_ttl'   => (int)($_ENV['OAUTH2_REFRESH_TOKEN_TTL'] ?? 2592000),

    'access_token_cookie_name'   => 'taskflow_access_token',
    'refresh_token_cookie_name'  => 'taskflow_refresh_token',
    'state_cookie_name'          => 'taskflow_oauth_state',

    'scopes' => [
        'user:read', 'user:write',
        'user-educations:read', 'user-educations:write',
        'user-contacts:read', 'user-contacts:write',
    ],

    'public_routes' => [
        'GET /', 'GET /auth/*', 'GET /gii', 'GET /debug',
        'GET /debug/*', 'GET /docs', 'GET /docs/*', 'OPTIONS *',
    ],
];
```

### 1.2. `config/di.php`

Регистрирует:

- `Client` → HTTP-клиент (CurlTransport).
- `PassportAuthPort` → `PassportHttpClient` с URL, OAuth-идентификаторами, скоупами, redirect_uri (`{app_url}/auth/callback`).
- `InitiateSsoLoginHandler` → с `PassportAuthPort`.
- `HandleSsoCallbackHandler` → с `PassportAuthPort` и `SyncUserHandler` (ядро).
- `PassportAuthMiddleware` → с `PassportAuthPort`, public_routes, pubkey_path, client_id, именами кук.

### 1.3. `config/routing.php`

```php
return [
    'GET auth/login'    => 'auth/auth/login',
    'GET auth/callback' => 'auth/auth/callback',
    'GET auth/logout'   => 'auth/auth/logout',
];
```

Подключается в `config/web.php` через `array_merge(require '...routing.php', ...)`.

## 2. Переменные .env, используемые модулем

| .env | Где используется |
|---|---|
| `PASSPORT_URL` | params.php → passport_url |
| `FRONTEND_URL` | params.php → frontend_url |
| `APP_URL` | params.php → app_url + `redirect_uri = app_url/auth/callback` |
| `OAUTH2_CLIENT_ID` | params.php + di.php |
| `OAUTH2_CLIENT_SECRET` | params.php + di.php |
| `OAUTH2_PUBLIC_KEY_PATH` | params.php + di.php (передаётся в middleware) |
| `OAUTH2_ACCESS_TOKEN_TTL` | params.php (дефолт 3600) |
| `OAUTH2_REFRESH_TOKEN_TTL` | params.php (дефолт 2592000) |

Все — обязательны, DI проверяет:

```php
foreach (['oauth2_client_id', 'oauth2_client_secret', 'oauth2_public_key_path'] as $key => $envName) {
    if (($params[$key] ?? '') === '') {
        throw new RuntimeException($envName . ' is required for passport/auth');
    }
}
```

## 3. Что зашито в коде (не конфиг)

| Что | Где | Почему |
|---|---|---|
| Куки флаги: `httpOnly=true, secure=true, sameSite=LAX` | `AuthController`, `PassportAuthMiddleware` | Защита и совместимость |
| Cookie path: `/` для токенов, `/auth` для state | Контроллер, middleware | Безопасность |
| state TTL = 600 секунд | `AuthController::actionLogin` | Защита от stale state |
| Refresh-токен TTL в logout: берётся из `$_ENV['OAUTH2_REFRESH_TOKEN_TTL']` (not из params) | `PassportAuthMiddleware::setTokenCookies` | — |
| Алгоритм JWT: RS256 | `PassportAuthMiddleware::validateJwt` | Требование Passport |
| scopes (список) | params.php — **это PHP-конфиг**, но scopes жёстко заданы массивом | Фиксированный набор |
| public_routes | params.php (PHP-массив) | Настраиваются параметрами модуля |
| redirect_uri = `{app_url}/auth/callback` | di.php (формируется из `app_url`) | Рассчитывается автоматически |
| CookieStorage заголовок | `PassportAuthMiddleware::setTokenCookies` | Использует синглтон |