# Архитектура модуля passport/auth

> Документ описывает слои модуля, порт интеграции с Passport и ключевые классы.

## 1. Слои

```mermaid
graph TD
    AC[AuthController] -->|InitiateSsoLoginHandler / HandleSsoCallbackHandler| APP[application]
    APP -->|PassportAuthPort| HTTP[PassportHttpClient]
    APP -->|SyncUserHandler| CORE[core]
    MW[PassportAuthMiddleware] -->|PassportAuthPort| HTTP
    MW -->|создаёт| ID[YiiIdentity (ядро)]
    AC -->|куки| BR[Браузер]
    HTTP -->|HTTP| PASS[Passport API]
```

- **presentation**: `AuthController` (login/callback/logout), `PassportAuthMiddleware`, HTML-view (redirect/exchange/layout).
- **application**: `InitiateSsoLoginHandler`, `HandleSsoCallbackHandler`, команды, DTO, порт `PassportAuthPort`.
- **infrastructure**: `PassportHttpClient` — HTTP-реализация порта.
- **Внешняя система**: Passport (SSO/OAuth2).

## 2. Порт `PassportAuthPort`

```php
interface PassportAuthPort
{
    public function buildAuthorizationUrl(string $state): string;          // GET /auth/authorize
    public function exchangeAuthorizationCode(string $code): PassportTokenDto; // POST /token
    public function refresh(string $refreshToken): PassportTokenDto;          // POST /token (refresh)
    public function getUser(string $passportId, string $accessToken): PassportUserDto; // GET /users/{id}
    public function logout(?string $cookieHeader, ?string $authorization): array;    // GET /auth/logout
}
```

## 3. Реализация `PassportHttpClient`

**Файл:** `modules/passport/auth/infrastructure/http/PassportHttpClient.php`

### Конфигурация (конструктор)

| Аргумент | Откуда |
|---|---|
| `Client` (yii\httpclient) | DI |
| `baseUrl` | `params.php['passport_url']` |
| `clientId` | `params.php['oauth2_client_id']` |
| `clientSecret` | `params.php['oauth2_client_secret']` |
| `redirectUri` | `params.php['app_url'] . '/auth/callback'` |
| `scopes` | `params.php['scopes']` |
| `userEndpoint` | `/users/{id}` (hardcodе) |

### Методы

| Метод | HTTP | Параметры | Ответ |
|---|---|---|---|
| `buildAuthorizationUrl` | — | state | строка URL |
| `exchangeAuthorizationCode` | POST /token | grant_type=authorization_code, code | PassportTokenDto |
| `refresh` | POST /token | grant_type=refresh_token | PassportTokenDto |
| `getUser` | GET /users/{id} | Bearer | PassportUserDto (с обогащением: post через /posts, owner через /roles) |
| `logout` | GET /auth/logout | Cookie/Authorization | массив status/headers/body |

### Поведение `getUser`

1. Запрос `GET {base}/users/{id}` с Bearer-токеном.
2. Парсит `item`/`data` (фолбэки).
3. `post_id` → доп. запрос `GET {base}/posts?ids={post_id}` для названия должности.
4. `is_owner`:
   - из `data.isOwner` / `data.is_owner`;
   - или по `roles`: если встретили запись с `name === 'owner'` — true;
   - иначе по `role_id`'ам → `GET {base}/roles?ids=...` ищем роль `'owner'`.
5. Возвращает `PassportUserDto`.

### Парсинг токенов (`parseTokenResponse`)

- `data.access_token` → обязательный;
- `data.refresh_token` → опциональный;
- `data.scope|scopes` → массив (строка разбивается по пробелам);
- `data.expires_in` → default 3600;
- `data.token_type` → default 'Bearer'.

## 4. DI (config/di.php)

1. Проверяет обязательность `OAUTH2_CLIENT_ID`, `OAUTH2_CLIENT_SECRET`, `OAUTH2_PUBLIC_KEY_PATH` (иначе `RuntimeException`).
2. `Client` (yii\httpclient\Client c CurlTransport).
3. `PassportAuthPort` → `PassportHttpClient`.
4. `InitiateSsoLoginHandler` → построение authorize URL.
5. `HandleSsoCallbackHandler` → обмен кода + `SyncUserHandler` (ядро).
6. `PassportAuthMiddleware` → JWT-проверка на запрос.

## 5. Обработчики

### `InitiateSsoLoginHandler`

```php
handle(InitiateSsoLoginCommand $command): string
// → passport->buildAuthorizationUrl($command->state)
```

### `HandleSsoCallbackHandler`

```php
handle(HandleSsoCallbackCommand $command): PassportTokenDto
```

1. `code` пустой → RuntimeException.
2. `hash_equals(expectedState, state)` — защита CSRF.
3. `exchangeAuthorizationCode($code)` → токены.
4. `passportClaims($accessToken)` — декодирование payload (без проверки подписи; проверка — в middleware).
5. Берёт `sub`, вызывает `getUser($sub, $accessToken)`.
6. `SyncUserHandler->handle(new SyncUserCommand(...))` — синхронизация локального пользователя (и роли).
7. Возвращает токены (контроллер кладёт их в куки).

## 6. AuthController (presentation)

| Действие | Путь | Поведение |
|---|---|---|
| `actionLogin` | GET /auth/login | state-кука + authorize URL + HTML-редирект |
| `actionCallback` | GET /auth/callback | state-проверка, обмен кода, SyncUser, Set-Cookie токенов, HTML-редирект на фронтенд |
| `actionLogout` | GET /auth/logout | proxy в Passport + чистка кук |

Детали потока — [flow.md](./flow.md), безопасность — [security.md](./security.md).