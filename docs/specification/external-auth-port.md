# Локальная копия "External Authentication Port — взаимодействие внешних сервисов с PASSPORT"

> **Версия:** 1.1  
> **Дата:** 2026-09-16  
> **Статус:** Утверждена  
> **Проект:** PASSPORT User Service (MOPS)

---

## Содержание

1. [Архитектура аутентификации](#1-архитектура-аутентификации)
2. [Способы аутентификации](#2-способы-аутентификации)
   - [2.1. Логин пользователя (Password Grant)](#21-логин-пользователя-password-grant)
   - [2.2. Регистрация пользователя](#22-регистрация-пользователя)
   - [2.3. Machine-to-Machine (M2M)](#23-machine-to-machine-m2m)
   - [2.4. SSO (Single Sign-On)](#24-sso-single-sign-on)
3. [Формат токена (JWT)](#3-формат-токена-jwt)
4. [Валидация токена внешним сервисом](#4-валидация-токена-внешним-сервисом)
   - [4.1. Синхронная валидация через PASSPORT](#41-синхронная-валидация-через-passport)
   - [4.2. Локальная валидация (JWT через public key)](#42-локальная-валидация-jwt-через-public-key)
   - [4.3. Асинхронная валидация](#43-асинхронная-валидация)
5. [Scopes (Области доступа)](#5-scopes-области-доступа)
6. [RBAC (Ролевая модель)](#6-rbac-ролевая-модель)
7. [Права, необходимые внешнему сервису](#7-права-необходимые-внешнему-сервису)
8. [Примеры кода](#8-примеры-кода)
   - [8.1. Логин (curl/JS/PHP/Python)](#81-логин-curljsphppython)
   - [8.2. Регистрация](#82-регистрация)
   - [8.3. Обновление токена](#83-обновление-токена)
   - [8.4. Валидация токена (локальная через публичный ключ)](#84-валидация-токена-локальная-через-публичный-ключ)
   - [8.5. Запрос к API с токеном](#85-запрос-к-api-с-токеном)
   - [8.6. M2M-запрос между бэкендами](#86-m2m-запрос-между-бэкендами)
9. [Карта маршрутов](#9-карта-маршрутов)
10. [Рабочий процесс аутентификации (диаграмма)](#10-рабочий-процесс-аутентификации-диаграмма)

---

## 1. Архитектура аутентификации

PASSPORT реализует **OAuth 2.0** поверх **JWT (RS256)** с помощью библиотеки `league/oauth2-server`.  
Архитектура слоёная:

```
┌─────────────────────────────────────────────────┐
│              Внешний сервис                     │
│  (Frontend, CRM, CMS, Mobile App, ...)          │
└──────────────────┬──────────────────────────────┘
                   │ Bearer JWT (Authorization header / HttpOnly cookie)
                   ▼
┌─────────────────────────────────────────────────┐
│         OAuthMiddleware (проверка токена)       │
│  ┌───────────────────────────────────────────┐  │
│  │  ResourceServer (league/oauth2-server)    │  │
│  │  → Проверяет подпись JWT (RS256)          │  │
│  │  → Проверяет срок действия                │  │
│  │  → Проверяет не отозван ли токен (БД)     │  │
│  │  → Извлекает claims: userId, clientId,    │  │
│  │    tokenId, scopes                        │  │
│  └───────────────────────────────────────────┘  │
└──────────────────┬──────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────┐
│   RbacMiddleware (проверка бизнес-разрешений)   │
│  ┌───────────────────────────────────────────┐  │
│  │  AuthorizationService                     │  │
│  │  → Проверяет OAuth scope (верхняя граница)│  │
│  │  → Проверяет RBAC permission              │  │
│  │  → Учитывает контекст (self-or-any,       │  │
│  │    owner-only, subject resolution)        │  │
│  └───────────────────────────────────────────┘  │
└──────────────────┬──────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────┐
│         Контроллер / Бизнес-логика              │
└─────────────────────────────────────────────────┘
```

**Ключевые компоненты:**

| Компонент             | Путь                                                        | Описание                                           |
|-----------------------|-------------------------------------------------------------|----------------------------------------------------|
| `OAuthMiddleware`     | `modules/oauth/presentation/middleware/OAuthMiddleware.php` | Проверяет JWT, заполняет YiiIdentity               |
| `TokenService`        | `modules/oauth/application/service/TokenService.php`        | Выпуск, обновление, валидация, отзыв токенов       |
| `AuthorizationServer` | DI-контейнер (oauth/config/di.php)                          | Конфигурация grant types (password, refresh_token) |
| `ResourceServer`      | DI-контейнер (oauth/config/di.php)                          | Валидация JWT через публичный ключ                 |
| `RbacMiddleware`      | `modules/rbac/presentation/middleware/RbacMiddleware.php`   | Проверка бизнес-разрешений                         |
| `YiiIdentity`         | `core/security/YiiIdentity.php`                             | Identity-объект с id, clientId, tokenId, scopes    |

---

## 2. Способы аутентификации

### 2.1. Логин пользователя (Password Grant)

Пользователь отправляет **username** (email или телефон) и **password**.  
Сервер проверяет credentials через `DbOAuthUserRepository`, выпускает пару токенов:

- **Access token** — короткоживущий JWT (по умолчанию 3600 сек / 1 час)
- **Refresh token** — долгоживущий токен (по умолчанию 2 592 000 сек / 30 дней)

**HTTP-запрос:**

```
POST /auth/login
Content-Type: application/json

{
    "username": "user@example.com",
    "password": "secret123",
    "scope": "user:read user-educations:read"  // опционально
}
```

**Ответ (200 OK):**
```json
{
    "data": {
        "scopes": ["user:read", "user-educations:read"],
        "expires_in": 3600
    },
    "message": "Login successful"
}
```

**Важно:** Сами токены передаются в **HttpOnly cookies**:
- `access_token` (HttpOnly, Secure, SameSite=Strict)
- `refresh_token` (HttpOnly, Secure, SameSite=Strict)

Это защищает от XSS-кражи токенов. Если внешний сервис не использует браузер (backend-to-backend), он может получить токены из ответа через заголовок `Authorization`, либо через выделенный M2M-клиент (см. раздел 2.3).

### 2.2. Регистрация пользователя

Регистрация доступна **без аутентификации** и является публичным маршрутом:

```
POST /users
Content-Type: multipart/form-data

email: user@example.com
phone: +79991234567
password: secret123
surname: Иванов
name: Иван
patronymic: Иванович
birthDate: 1995-05-17
gender: man
stack: PHP, Yii2
preferredContact: telegram
cityId: 1
languageId: 1
consent: true
consentSource: web
```

**Ответ (200 OK):**
```json
{
    "item": {
        "id": 42,
        "email": "user@example.com",
        "phone": "+79991234567",
        ...
    }
}
```

После регистрации пользователю отправляется письмо со ссылкой для подтверждения email.

**Подтверждение email:**

```
POST /auth/verify-email
Content-Type: application/json

{
    "token": "..."
}
```

### 2.3. Machine-to-Machine (M2M)

M2M-взаимодействие реализовано через **Client Credentials Grant** (`client_credentials`).

**Как это работает:**

Сервис аутентифицируется от своего имени (без участия пользователя), используя `client_id` и `client_secret`. Для M2M access token `sub` отсутствует (`null`), а в introspection-ответе `sub_type=client`. `sub_type` не является стандартным JWT claim и не добавляется в access token.

**Предварительные условия:**

1. Создать OAuth-клиента с grant type `client_credentials` через консольную команду:
   ```bash
   php yii oauth-client --name="My Service" --grant_types="client_credentials" --scope="user:read user-contacts:read"
   ```

2. Сохранить `client_id` и `client_secret` в `.env` внешнего сервиса.

**HTTP-запрос (получение токена):**

```
POST /token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
&client_id=my-service
&client_secret=generated-secret
&scope=user:read user-contacts:read
```

**Ответ (200 OK):**
```json
{
    "data": {
        "access_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
        "refresh_token": null,
        "expires_in": 3600,
        "token_type": "Bearer",
        "scope": "user:read user-contacts:read"
    },
    "message": "Token issued"
}
```

**Важно:**
- M2M-токен не привязан к конкретному пользователю — `sub` = `null`, `sub_type` в introspection = `client`
- RBAC-проверки для M2M выполняются от имени client-subject: OAuth scope ограничивает доступ, а ресурсные permissions `self-or-any` разрешаются как `.any`. Роли пользователя при M2M не используются.
- Для обновления M2M/SSO токена используется `grant_type=refresh_token` на `/token`; клиент обязан передать свои `client_id` и `client_secret`.

**Альтернативный подход — системный пользователь:**
Можно создать выделенного пользователя с ролью `owner` / `admin` и использовать Password Grant (см. раздел 2.1). Это даёт полный RBAC-контекст.

### 2.4. SSO (Single Sign-On)

SSO реализовано через **Authorization Code Grant** (`authorization_code`).

**Как это работает:**

1. Внешний сервис перенаправляет браузер пользователя на PASSPORT `/auth/authorize`
2. PASSPORT валидирует OAuth-запрос и проверяет, есть ли уже действующий Passport access token в HttpOnly cookie.
3. Если пользователь уже вошёл в PASSPORT, форма логина не показывается: authorization code генерируется сразу и браузер перенаправляется обратно на `redirect_uri`.
4. Если Passport-сессии нет, PASSPORT показывает защищённую CSRF-формой страницу логина.
5. После успешного входа PASSPORT генерирует authorization code и редиректит обратно на `redirect_uri`.
6. Внешний сервис обменивает code на access/refresh tokens через `/token`.
7. После истечения access token внешний сервис обновляет пару через `/token` с `grant_type=refresh_token`, используя собственные client credentials.

**Шаг 1. Редирект на PASSPORT:**

```
GET /auth/authorize?
  response_type=code
  &client_id=my-service
  &redirect_uri=https://my-service.example.com/oauth/callback
  &scope=user:read user-contacts:read
  &state=random-csrf-state
```

**Шаг 2. Если пользователь ещё не вошёл в PASSPORT, он вводит credentials на странице PASSPORT:**

![Login form shown to user]

После успешного входа браузер редиректится:
```
302 → https://my-service.example.com/oauth/callback?code=abc123...&state=random-csrf-state
```

**Шаг 3. Обмен code на токены (backend-to-backend):**

```
POST /token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code
&client_id=my-service
&client_secret=generated-secret
&code=abc123...
&redirect_uri=https://my-service.example.com/oauth/callback
```

**Ответ (200 OK):**
```json
{
    "data": {
        "access_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
        "refresh_token": null,
        "expires_in": 3600,
        "token_type": "Bearer",
        "scope": "user:read user-contacts:read"
    },
    "message": "Token issued"
}
```

**Параметры authorization code:**

| Параметр               | Значение по умолчанию | Описание                                  |
|------------------------|-----------------------|-------------------------------------------|
| `OAUTH2_AUTH_CODE_TTL` | 600 (10 минут)        | Время жизни authorization code в секундах |

**Важно:**
- Authorization code одноразовый — после обмена на токены он отзывается
- `state` параметр обязателен для предотвращения CSRF-атак
- `redirect_uri` должен совпадать с зарегистрированным в OAuth-клиенте
- Для публичных клиентов (без `client_secret`) секрет можно опустить в `/token`

**Предварительные условия:**

```bash
# Создать OAuth-клиента для внешнего сервиса
php yii oauth-client \
  --name="My Web Service" \
  --grant_types="authorization_code refresh_token" \
  --redirect_uri="https://my-service.example.com/oauth/callback" \
  --scope="user:read user-contacts:read"
```

---

## 3. Формат токена (JWT)

PASSPORT использует **JWT (JSON Web Token)**, подписанный через **RS256** (асимметричная подпись).

**Структура JWT:**

```json
{
    "typ": "JWT",
    "alg": "RS256",
    "kid": "..."
}
.
{
    "jti": "abc123def456",           // Уникальный ID токена (tokenId)
    "sub": "42",                      // User ID (null для M2M client_credentials)
    "sub_type": "user",               // extension introspection: "user" или "client"
    "scopes": ["user:read", "user:write"],
    "client_id": "script-web",
    "exp": 1712345678,                // Unix timestamp истечения
    "iat": 1712342078,                // Unix timestamp выпуска
    "nbf": 1712342078,                // Not before
    "aud": "script-web"               // Audience
}
<RS256 signature>
```

**Параметры:**

| Параметр     | Значение                                                |
|--------------|---------------------------------------------------------|
| Алгоритм     | RS256 (RSA PKCS#1 v1.5 with SHA-256)                    |
| Размер ключа | 2048 бит (рекомендуется)                                |
| Тип ключа    | `openssl_pkey` (приватный) / `openssl_pkey` (публичный) |
| Формат ключа | PEM                                                     |

**Claims JWT:**

| Claim       | Тип       | Описание                                                                                                |
|-------------|-----------|---------------------------------------------------------------------------------------------------------|
| `jti`       | string    | Уникальный ID токена (JWT ID)                                                                           |
| `sub`       | string    | User ID (строка); `null` для M2M-токенов (`client_credentials`)                                         |
| `sub_type`  | string    | Extension-поле introspection: `"user"` — пользователь, `"client"` — M2M-сервис; в JWT claim отсутствует |
| `scopes`    | string[]  | OAuth scopes, выданные токену                                                                           |
| `client_id` | string    | ID OAuth-клиента, выпустившего токен                                                                    |
| `exp`       | int       | Unix timestamp истечения                                                                                |
| `iat`       | int       | Unix timestamp выпуска                                                                                  |
| `nbf`       | int       | Not before timestamp                                                                                    |
| `aud`       | string    | Audience (client_id)                                                                                    |

**Токен в базе данных:**

JWT не хранится полностью — сохраняется только его HMAC-SHA256 хеш (64 символа) для проверки отзыва:

```sql
-- oauth_access_tokens.access_token = HMAC-SHA256(RAW_JWT, OAUTH2_TOKEN_HASH_SECRET)
```

Это позволяет PASSPORT проверять, не отозван ли токен, без хранения полного JWT.

**Refresh token:**
- Не является JWT
- Это случайная строка, хешированная через HMAC-SHA256
- Хранится только хеш
- Доступен только через HttpOnly cookie

---

## 4. Валидация токена внешним сервисом

Внешний сервис может проверять токены тремя способами.

### 4.1. Синхронная валидация через PASSPORT

PASSPORT предоставляет endpoint **`POST /token/introspect`** (RFC 7662 Token Introspection) для проверки токена.

**HTTP-запрос:**

```
POST /token/introspect
Content-Type: application/json

{
    "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Ответ (200 OK) — токен активен:**
```json
{
    "data": {
        "active": true,
        "sub": "42",
        "sub_type": "user",
        "client_id": "script-web",
        "token_type": "Bearer",
        "scope": ["user:read", "user:write"],
        "exp": 1712345678,
        "iat": 1712342078,
        "jti": "abc123def456"
    }
}
```

**Ответ (200 OK) — токен неактивен (истёк, отозван, недействителен):**
```json
{
    "data": {
        "active": false,
        "sub": null,
        "sub_type": null,
        "client_id": null,
        "token_type": "Bearer",
        "scope": [],
        "exp": null,
        "iat": null,
        "jti": null
    }
}
```

**Поля ответа:**

| Поле         | Тип       | Описание                                                                                                |
|--------------|-----------|---------------------------------------------------------------------------------------------------------|
| `active`     | bool      | `true` если токен действителен и не отозван                                                             |
| `sub`        | ?string   | ID пользователя (`null` для M2M-токенов)                                                                |
| `sub_type`   | ?string   | Extension-поле introspection: `"user"` — пользователь, `"client"` — M2M-сервис; в JWT claim отсутствует |
| `client_id`  | ?string   | ID OAuth-клиента                                                                                        |
| `token_type` | string    | Всегда `"Bearer"`                                                                                       |
| `scope`      | string[]  | Список выданных scopes                                                                                  |
| `exp`        | ?int      | Unix timestamp истечения                                                                                |
| `iat`        | ?int      | Unix timestamp выпуска                                                                                  |
| `jti`        | ?string   | Уникальный ID токена                                                                                    |

**Важно:** Endpoint `/token/introspect` — публичный, не требует аутентификации.

**Программная валидация (внутри PASSPORT):**

Можно использовать `TokenService::validateToken()` — этот метод принимает строку access token и возвращает массив с userId, clientId, tokenId, scopes:

```php
// PASSPORT внутренне
$claims = $tokenService->validateToken($accessToken);
// ['userId' => '42', 'clientId' => 'script-web', 'tokenId' => 'abc...', 'scopes' => ['user:read']]
```

**Рекомендация:** В будущем реализовать endpoint `POST /auth/introspect` по стандарту OAuth 2.0 Token Introspection (RFC 7662).

### 4.2. Локальная валидация (JWT через public key)

Внешний сервис может валидировать JWT **самостоятельно**, используя публичный ключ PASSPORT.

**Это предпочтительный способ** — он не создаёт лишних сетевых вызовов и нагрузки на PASSPORT.

**Что нужно:**

1. Получить публичный ключ PASSPORT (RSA, PEM-формат)
2. Использовать любую JWT-библиотеку для проверки подписи и expiry
3. Извлечь claims: `sub` (userId), `scopes`, `client_id`, `jti` (tokenId)

**Публичный ключ** настраивается через переменную окружения `OAUTH2_PUBLIC_KEY_PATH` (файл PEM).  
Продублировать этот ключ во внешнем сервисе — ответственность администратора инфраструктуры.

```bash
# Сгенерировать ключи (если ещё нет):
openssl genrsa -out private.key 2048
openssl rsa -in private.key -pubout -out public.key

# Переменные окружения PASSPORT:
OAUTH2_PUBLIC_KEY_PATH=/path/to/public.key
OAUTH2_PRIVATE_KEY_PATH=/path/to/private.key
```

### 4.3. Асинхронная валидация

Не реализована в текущей версии.  
В будущем: сервис мог бы подписаться на события PASSPORT (отзыв токена, смена ролей) через Redis Pub/Sub или Webhook, чтобы кэшировать статус токенов.

---

## 5. Scopes (Области доступа)

Scopes — это **грубые разрешения**, которые определяют, какие категории данных доступны токену.

**Полный список scopes** (определён в `modules/oauth/config/params.php`):

| Scope                   | Описание                                                                |
|-------------------------|-------------------------------------------------------------------------|
| `admin:web`             | Доступ к веб-панели администратора                                      |
| `system:manage`         | Управление системными справочниками (города, языки, должности, роли)    |
| `user:read`             | Чтение данных пользователей                                             |
| `user:write`            | Изменение данных пользователей                                          |
| `user:manage`           | Управление пользователями (архивация, восстановление, назначение ролей) |
| `user-educations:read`  | Чтение образования пользователей                                        |
| `user-educations:write` | Изменение образования пользователей                                     |
| `user-contacts:read`    | Чтение контактов пользователей                                          |
| `user-contacts:write`   | Изменение контактов пользователей                                       |

**Правила привязки scope к маршрутам** (scope_rules):

```php
// Примеры:
['method' => 'GET', 'pattern' => '~^/users(?:/\d+)?$~', 'scope' => 'user:read'],
['method' => 'POST', 'pattern' => '~^/users$~', 'scope' => 'user:write'],
['method' => 'DELETE', 'pattern' => '~^/users/\d+$~', 'scope' => 'user:manage'],
['method' => 'POST', 'pattern' => '~^/roles$~', 'scope' => 'system:manage'],
```

**При логине** пользователь может запросить конкретные scopes. Если не указаны — выдаётся полный набор:

```json
// Запрос с ограниченными scopes:
POST /auth/login
{"username": "...", "password": "...", "scope": "user:read user-contacts:read"}
```

**Проверить доступные scopes:**
```
GET /auth/available-scopes
Authorization: Bearer <token>
```

---

## 6. RBAC (Ролевая модель)

RBAC — это **тонкая настройка прав** внутри каждого scope.  
Каждый scope связан с набором permissions (разрешений), а permissions назначаются ролям.

**Связь слоёв:**

```
OAuth Scope (грубый)  →  Permission (конкретное)  →  Role (роль пользователя)
user:read                  users.read.self              user
                           users.read.any               manager / admin / owner
user:write                 users.write.self             user
                           users.write.any              admin / owner
system:manage              system.manage                owner
                           rbac.permissions.manage
                           roles.manage
```

**Роли и их permissions:**

| Роль           | Возможности                                                      |
|----------------|------------------------------------------------------------------|
| `user` (10)    | Чтение/редактирование себя, своих контактов, своего образования  |
| `manager` (20) | Чтение любых пользователей, просмотр ролей                       |
| `admin` (30)   | Всё, что может manager + запись любых данных + управление ролями |
| `owner` (40)   | Полный доступ, включая системные справочники, RBAC               |

**Иерархия ролей** — строгая. Низшая роль не может назначать высшую.

**Важно:** OAuth scope — это **верхняя граница**.  
RBAC никогда не предоставит больше прав, чем указано в scopes токена:

```php
// AuthorizationService::can()
if (!$identity->hasScope($definition->scope())) {
    return false; // Нет scope — нет разрешения
}
```

---

## 7. Права, необходимые внешнему сервису

### Для чтения данных пользователей

| Необходимо                                              | Как получить                              |
|---------------------------------------------------------|-------------------------------------------|
| OAuth scope: `user:read`                                | При логине запросить `scope: "user:read"` |
| RBAC permission: `users.read.self` или `users.read.any` | Назначить роль `manager` или выше         |
| Доступ к endpoint: `GET /users/{id}`                    | Через Bearer token                        |

### Для управления пользователями

| Необходимо                                                | Как получить          |
|-----------------------------------------------------------|-----------------------|
| OAuth scope: `user:write`                                 | Запросить при логине  |
| RBAC permission: `users.write.self` или `users.write.any` | Роль `admin` или выше |
| Endpoint: `PUT /users/{id}`, `POST /users/{id}/update`    |                       |

### Для архивных операций

| Необходимо                                                 | Как получить          |
|------------------------------------------------------------|-----------------------|
| OAuth scope: `user:manage`                                 | Запросить при логине  |
| RBAC permission: `users.manage.any`                        | Роль `admin` или выше |
| Endpoint: `DELETE /users/{id}`, `POST /users/{id}/restore` |                       |

### Для системных справочников (города, языки, должности, роли)

| Необходимо                                                            | Как получить         |
|-----------------------------------------------------------------------|----------------------|
| OAuth scope: `system:manage`                                          | Запросить при логине |
| RBAC permission: `system.manage`                                      | Роль `owner`         |
| Endpoint: `POST/PUT/DELETE /cities`, `/languages`, `/posts`, `/roles` |                      |

### Для чтения scopes токена

| Эндпоинт                     | Метод  | Описание                                 |
|------------------------------|--------|------------------------------------------|
| `GET /auth/available-scopes` | GET    | Возвращает список scopes текущего токена |

### Доступ к маршрутам без аутентификации (public routes)

Эти эндпоинты доступны **без токена**:

| Маршрут                                 | Описание                     |
|-----------------------------------------|------------------------------|
| `POST /auth/login`                      | Аутентификация               |
| `POST /auth/refresh`                    | Обновление токена            |
| `POST /auth/password-reset`             | Восстановление пароля        |
| `POST /auth/verify-email`               | Подтверждение email          |
| `GET /email-verify`                     | Страница подтверждения email |
| `GET /docs`, `GET /docs/*`              | Документация                 |
| `POST /users`                           | Регистрация                  |
| `GET /cities`, `GET /cities/{id}`       | Справочник городов           |
| `GET /languages`, `GET /languages/{id}` | Справочник языков            |

---

## 8. Примеры кода

### 8.1. Логин (curl / JS / PHP / Python)

**curl:**
```bash
curl -X POST https://passport.example.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "user@example.com",
    "password": "secret123",
    "scope": "user:read user-contacts:read"
  }' \
  -c cookies.txt

# Использование токена из cookie:
curl https://passport.example.com/users/42 \
  -b cookies.txt
```

**JavaScript (fetch):**
```javascript
const response = await fetch('https://passport.example.com/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'include',
  body: JSON.stringify({
    username: 'user@example.com',
    password: 'secret123',
    scope: 'user:read user-educations:read',
  }),
});

const { data, message } = await response.json();
// data.scopes     = ["user:read", "user-educations:read"]
// data.expires_in = 3600
// Токены установлены в HttpOnly cookies автоматически
```

**PHP (Guzzle):**
```php
<?php

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://passport.example.com',
    'cookies'  => true, // Автоматически сохраняет cookies
]);

// 1. Логин
$response = $client->post('/auth/login', [
    'json' => [
        'username' => 'service-bot@example.com',
        'password' => 'service-secret',
        'scope'    => 'user:read user:write',
    ],
]);

$body = json_decode((string) $response->getBody(), true);
echo $body['data']['scopes']; // ["user:read", "user:write"]

// 2. Запрос с токеном (cookie или Bearer)
$response = $client->get('/users/42', [
    // Cookie установлен автоматически через Jar
]);
```

**Python (httpx):**
```python
import httpx

with httpx.Client() as client:
    # 1. Логин
    response = client.post(
        'https://passport.example.com/auth/login',
        json={
            'username': 'user@example.com',
            'password': 'secret123',
            'scope': 'user:read',
        },
    )

    data = response.json()['data']
    print(data['scopes'])  # ['user:read']

    # 2. Использование cookies
    user = client.get('https://passport.example.com/users/42')
    print(user.json())
```

### 8.2. Регистрация

**curl:**
```bash
curl -X POST https://passport.example.com/users \
  -F "email=user@example.com" \
  -F "phone=+79991234567" \
  -F "password=secret123" \
  -F "surname=Иванов" \
  -F "name=Иван" \
  -F "consent=true" \
  -F "consentSource=web"
```

**PHP:**
```php
<?php

use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'https://passport.example.com']);

$response = $client->post('/users', [
    'multipart' => [
        ['name' => 'email',     'contents' => 'new-user@example.com'],
        ['name' => 'phone',     'contents' => '+79990000000'],
        ['name' => 'password',  'contents' => 'strong-pass-123'],
        ['name' => 'surname',   'contents' => 'Петров'],
        ['name' => 'name',      'contents' => 'Пётр'],
        ['name' => 'consent',   'contents' => 'true'],
    ],
]);

$user = json_decode((string) $response->getBody(), true);
echo $user['item']['id']; // 43
```

### 8.3. Обновление токена

Когда access token истекает, используйте refresh token из cookie.

**Ручное обновление:**
```bash
curl -X POST https://passport.example.com/auth/refresh \
  -b cookies.txt -c cookies.txt
```

**PHP:**
```php
<?php

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://passport.example.com',
    'cookies'  => true,
]);

// После логина refresh_token сохранён в Cookie Jar
// Когда access token истекает, Guzzle получит 401.
// Выполняем refresh:
$response = $client->post('/auth/refresh');
$body = json_decode((string) $response->getBody(), true);

// Новая пара токенов установлена в cookies
echo $body['data']['expires_in']; // 3600
```

### 8.4. Валидация токена (локальная через публичный ключ)

**PHP (firebase/php-jwt):**
```php
<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$publicKey = file_get_contents('/path/to/public.key');
$jwt = $tokenFromRequest; // Из заголовка Authorization: Bearer ...

try {
    $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));

    $userId    = $decoded->sub;        // "42"
    $scopes    = $decoded->scopes;    // ["user:read"]
    $clientId  = $decoded->client_id; // "script-web"
    $tokenId   = $decoded->jti;       // Уникальный ID токена
    $expiresAt = $decoded->exp;       // Unix timestamp

    // Дополнительно: проверить, что токен не отозван
    // (Требуется запрос к PASSPORT, если кэш отозванных токенов отсутствует)

} catch (\Exception $e) {
    // Токен недействителен
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
}
```

**Python (PyJWT):**
```python
import jwt

public_key = open('/path/to/public.key').read()
token = request.headers.get('Authorization', '').replace('Bearer ', '')

try:
    payload = jwt.decode(
        token,
        public_key,
        algorithms=['RS256'],
        audience='script-web',
    )

    user_id = payload['sub']
    scopes = payload['scopes']
    token_id = payload['jti']
except jwt.ExpiredSignatureError:
    print('Token expired')
except jwt.InvalidTokenError:
    print('Token invalid')
```

**Node.js (jsonwebtoken):**
```javascript
const jwt = require('jsonwebtoken');
const fs = require('fs');

const publicKey = fs.readFileSync('/path/to/public.key', 'utf8');
const token = req.headers.authorization?.replace('Bearer ', '');

try {
  const decoded = jwt.verify(token, publicKey, {
    algorithms: ['RS256'],
    audience: 'script-web',
  });

  const userId = decoded.sub;
  const scopes = decoded.scopes;
  const tokenId = decoded.jti;
} catch (err) {
  res.status(401).json({ error: 'Invalid token' });
}
```

### 8.5. Запрос к API с токеном

```bash
# Через заголовок Authorization:
curl https://passport.example.com/users/42 \
  -H "Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
```

```javascript
// JavaScript (fetch)
const response = await fetch('https://passport.example.com/users/42', {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});
```

```php
// PHP (Guzzle) с Bearer токеном
$response = $client->get('/users/42', [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
    ],
]);
```

### 8.6. M2M-запрос между бэкендами

**Client Credentials Grant (рекомендованный способ):**

```php
<?php

/**
 * Внешний сервис (например, CRM) взаимодействует с PASSPORT
 * через Client Credentials Grant — сервис действует от своего имени.
 *
 * Credentials хранятся в .env внешнего сервиса:
 * - PASSPORT_CLIENT_ID=my-service
 * - PASSPORT_CLIENT_SECRET=generated-secret
 */
class PassportClient
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;
    private ?string $refreshToken = null;
    private ?\GuzzleHttp\Client $http = null;

    public function __construct()
    {
        $this->baseUrl = $_ENV['PASSPORT_BASE_URL'];
        $this->clientId = $_ENV['PASSPORT_CLIENT_ID'];
        $this->clientSecret = $_ENV['PASSPORT_CLIENT_SECRET'];
    }

    public function authenticate(): void
    {
        $response = $this->getHttpClient()->post('/token', [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => 'user:read user:write',
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $data = $body['data'] ?? [];
        $this->accessToken = $data['access_token'] ?? null;
        $this->refreshToken = $data['refresh_token'] ?? null;
    }

    public function refresh(): void
    {
        $response = $this->getHttpClient()->post('/token', [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $data = $body['data'] ?? [];
        $this->accessToken = $data['access_token'];
        $this->refreshToken = $data['refresh_token'] ?? $this->refreshToken;
    }

    public function get(string $path, array $options = []): array
    {
        $response = $this->getHttpClient()->get($path, array_merge(
            ['headers' => ['Authorization' => 'Bearer ' . $this->accessToken]],
            $options,
        ));

        return json_decode((string) $response->getBody(), true);
    }

    public function post(string $path, array $data = []): array
    {
        $response = $this->getHttpClient()->post($path, [
            'headers' => ['Authorization' => 'Bearer ' . $this->accessToken],
            'json' => $data,
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    private function getHttpClient(): \GuzzleHttp\Client
    {
        if ($this->http === null) {
            $this->http = new \GuzzleHttp\Client([
                'base_uri'    => $this->baseUrl,
                'http_errors' => false,
            ]);
        }

        return $this->http;
    }
}

// Использование:
$passport = new PassportClient();
$passport->authenticate();

$user = $passport->get('/users/42');
$passport->post('/users', $userData);
```

**Вариант: системный пользователь (Password Grant):**

```php
<?php

/**
 * Альтернативный подход: создать системного пользователя
 * и использовать стандартный Password Grant для M2M.
 */
$response = $httpClient->post('https://passport.example.com/auth/login', [
    'json' => [
        'username' => 'system-bot@example.com',
        'password' => 'strong-secret',
        'scope'    => 'user:read user-contacts:read',
    ],
]);

// При Password Grant токены передаются через Set-Cookie.
// Извлекаем их вручную для backend-to-backend сценария:
$cookies = $response->getHeader('Set-Cookie');
$accessToken = extractCookie($cookies, 'access_token');
```

### 8.7. SSO через Authorization Code Grant

**Шаг 1. Редирект пользователя на PASSPORT (из внешнего сервиса):**

```php
// Внешний сервис (контроллер)
public function actionLogin(): RedirectResponse
{
    $clientId = $_ENV['PASSPORT_CLIENT_ID'];
    $redirectUri = 'https://my-service.example.com/oauth/callback';
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;

    $url = 'https://passport.example.com/auth/authorize?' . http_build_query([
        'response_type' => 'code',
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'scope'         => 'user:read user-contacts:read',
        'state'         => $state,
    ]);

    return new RedirectResponse($url);
}
```

**Шаг 2. Обработка callback (обмен code на токены):**

```php
// Внешний сервис (callback-контроллер)
public function actionCallback(Request $request): Response
{
    $code = $request->get('code');
    $state = $request->get('state');

    // Проверяем CSRF state
    if ($state !== ($_SESSION['oauth_state'] ?? '')) {
        throw new BadRequestException('Invalid state');
    }
    unset($_SESSION['oauth_state']);

    // Обмениваем code на токены
    $response = $httpClient->post('https://passport.example.com/token', [
        'form_params' => [
            'grant_type'    => 'authorization_code',
            'client_id'     => $_ENV['PASSPORT_CLIENT_ID'],
            'client_secret' => $_ENV['PASSPORT_CLIENT_SECRET'],
            'code'          => $code,
            'redirect_uri'  => 'https://my-service.example.com/oauth/callback',
        ],
    ]);

    $body = json_decode((string) $response->getBody(), true);
    $data = $body['data'] ?? [];

    $accessToken = $data['access_token'];
    $refreshToken = $data['refresh_token'];
    $expiresIn = $data['expires_in'];
    $scope = $data['scope'];

    // Сохраняем токены в сессии/БД внешнего сервиса
    $this->session->set('passport_access_token', $accessToken);
    $this->session->set('passport_refresh_token', $refreshToken);

    // Перенаправляем на целевую страницу
    return new RedirectResponse('/dashboard');
}
```

**Python (Flask):**

```python
import httpx
import secrets
from flask import Flask, redirect, request, session

app = Flask(__name__)

@app.route('/login')
def login():
    state = secrets.token_hex(16)
    session['oauth_state'] = state

    params = {
        'response_type': 'code',
        'client_id': 'my-service',
        'redirect_uri': 'https://my-service.example.com/oauth/callback',
        'scope': 'user:read',
        'state': state,
    }
    url = 'https://passport.example.com/auth/authorize?' + urllib.parse.urlencode(params)
    return redirect(url)

@app.route('/oauth/callback')
def callback():
    state = request.args.get('state')
    code = request.args.get('code')

    if state != session.pop('oauth_state', None):
        return 'Invalid state', 400

    # Обмен code на токены
    response = httpx.post('https://passport.example.com/token', data={
        'grant_type': 'authorization_code',
        'client_id': 'my-service',
        'client_secret': 'generated-secret',
        'code': code,
        'redirect_uri': 'https://my-service.example.com/oauth/callback',
    })

    tokens = response.json()['data']
    session['access_token'] = tokens['access_token']
    session['refresh_token'] = tokens.get('refresh_token')

    return redirect('/dashboard')
```

### 8.8. Интроспекция токена

**curl:**
```bash
curl -X POST https://passport.example.com/token/introspect \
  -H "Content-Type: application/json" \
  -d '{"token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."}'
```

**PHP (Guzzle):**
```php
$response = $client->post('https://passport.example.com/token/introspect', [
    'json' => ['token' => $accessToken],
]);

$intro = json_decode((string) $response->getBody(), true)['data'];

if ($intro['active']) {
    echo "Token valid. User: {$intro['sub']}, Scopes: " . implode(', ', $intro['scope']);
} else {
    echo "Token is invalid or expired";
}
```

---

## 9. Карта маршрутов

### Публичные (без токена)

| HTTP         | Маршрут                | Описание                                                                         |
|--------------|------------------------|----------------------------------------------------------------------------------|
| POST         | `/auth/login`          | Логин (выдаёт access + refresh в cookies)                                        |
| POST         | `/auth/refresh`        | Обновление токенов                                                               |
| POST         | `/auth/password-reset` | Сброс пароля                                                                     |
| POST         | `/auth/verify-email`   | Подтверждение email                                                              |
| GET          | `/email-verify`        | Страница подтверждения email                                                     |
| GET,POST     | `/auth/authorize`      | SSO-авторизация (Authorization Code Grant) — GET: форма, POST: обработка логина  |
| POST         | `/token/introspect`    | Интроспекция токена (RFC 7662)                                                   |
| POST         | `/token`               | Токен-эндпоинт (client_credentials, authorization_code, refresh_token)           |
| POST         | `/users`               | Создание пользователя (регистрация)                                              |
| GET          | `/cities`              | Список городов                                                                   |
| GET          | `/cities/{id}`         | Город по ID                                                                      |
| GET          | `/languages`           | Список языков                                                                    |
| GET          | `/languages/{id}`      | Язык по ID                                                                       |
| GET          | `/docs`                | Документация                                                                     |
| GET          | `/docs/swagger`        | Swagger UI                                                                       |
| GET          | `/`                    | Welcome страница                                                                 |
| OPTIONS      | `*`                    | CORS preflight                                                                   |

### Защищённые (требуют токен)

| HTTP                | Маршрут                       | Scope                   | RBAC Permission                       |
|---------------------|-------------------------------|-------------------------|---------------------------------------|
| GET                 | `/auth/available-scopes`      | любой                   | —                                     |
| POST                | `/auth/logout`                | любой                   | —                                     |
| POST                | `/auth/logout-all`            | любой                   | —                                     |
| GET                 | `/users`                      | `user:read`             | `users.list`                          |
| GET                 | `/users/{id}`                 | `user:read`             | `users.read.self` / `.any`            |
| PUT                 | `/users/{id}`                 | `user:write`            | `users.write.self` / `.any`           |
| POST                | `/users/{id}/update`          | `user:write`            | `users.write.self` / `.any`           |
| DELETE              | `/users/{id}`                 | `user:manage`           | `users.manage.any`                    |
| POST                | `/users/{id}/restore`         | `user:manage`           | `users.manage.any`                    |
| POST                | `/users/{id}/passport-change` | `user:write`            | `users.write.self` / `.any`           |
| GET                 | `/user-contacts`              | `user-contacts:read`    | `user_contacts.read.self` / `.any`    |
| POST                | `/user-contacts`              | `user-contacts:write`   | `user_contacts.write.self` / `.any`   |
| GET                 | `/user-educations`            | `user-educations:read`  | `user_educations.read.self` / `.any`  |
| POST                | `/user-educations`            | `user-educations:write` | `user_educations.write.self` / `.any` |
| GET                 | `/user-roles`                 | `user:read`             | `user_roles.read`                     |
| POST                | `/user-roles`                 | `user:manage`           | `user_roles.manage`                   |
| DELETE              | `/user-roles/{id}`            | `user:manage`           | `user_roles.manage`                   |
| GET/POST/PUT/DELETE | `/roles`                      | `system:manage`         | `roles.read` / `roles.manage`         |
| GET/POST/PUT/DELETE | `/posts`                      | `system:manage`         | `posts.read` / `system.manage`        |
| POST/PUT/DELETE     | `/cities`                     | `system:manage`         | `system.manage`                       |
| POST/PUT/DELETE     | `/languages`                  | `system:manage`         | `system.manage`                       |
| GET/POST/PUT/DELETE | `/rbac/permissions`           | `system:manage`         | `rbac.permissions.manage`             |
| GET/POST/PUT/DELETE | `/storage/...`                | зависит                 | `storage.*`                           |
| GET                 | `/admin`                      | `admin:web`             | `admin.web.access`                    |

---

## 10. Рабочий процесс аутентификации (диаграмма)

```
Пользователь / Внешний сервис      PASSPORT
                │                     │
  1. POST /users                      │
     (регистрация)                    │
                ├─────────────────────┤
                │   201 Created       │
                │   + email verify    │
                │◄────────────────────┤
                │                     │
  2. POST /auth/login                 │
     (username + password)            │
                ├─────────────────────┤
                │   200 OK            │
                │   Set-Cookie:       │
                │   access_token=JWT  │
                │   refresh_token=... │
                │   + scopes, expires │
                │◄────────────────────┤
                │                     │
  3. GET /users/42                    │
     Cookie: access_token=JWT         │
                ├─────────────────────┤
                │  OAuthMiddleware:   │
                │  ├─ валидация JWT   │
                │  ├─ проверка expiry │
                │  └─ проверка отзыва │
                │  RbacMiddleware:    │
                │  ├─ проверка scope  │
                │  └─ проверка perm   │
                │   200 OK + user     │
                │◄────────────────────┤
                │                     │
  4. [access token истёк]             │
     POST /auth/refresh               │
     Cookie: refresh_token=...        │
                ├─────────────────────┤
                │   200 OK            │
                │   Set-Cookie:       │
                │   новая пара токенов│
                │◄────────────────────┤
                │                     │
  5. POST /auth/logout                │
     Cookie: access_token=JWT         │
                ├─────────────────────┤
                │   отзыв токена      │
                │   200 OK            │
                │◄────────────────────┤
```

---

## Приложение A: Переменные окружения (OAuth)

| Переменная                 | Назначение                          | Пример                      |
|----------------------------|-------------------------------------|-----------------------------|
| `OAUTH2_PUBLIC_KEY_PATH`   | Путь к публичному ключу RSA (PEM)   | `/etc/passport/public.key`  |
| `OAUTH2_PRIVATE_KEY_PATH`  | Путь к приватному ключу RSA (PEM)   | `/etc/passport/private.key` |
| `OAUTH2_ENCRYPTION_KEY`    | Ключ шифрования (32+ байт)          | `def00000...`               |
| `OAUTH2_TOKEN_HASH_SECRET` | Секрет HMAC для хеширования токенов | `random-64-chars...`        |
| `OAUTH2_CLIENT_ID`         | ID first-party клиента              | `script-web`                |
| `OAUTH2_CLIENT_SECRET`     | Secret first-party клиента          | `random-string`             |
| `OAUTH2_ACCESS_TOKEN_TTL`  | Время жизни access token (сек)      | `3600`                      |
| `OAUTH2_REFRESH_TOKEN_TTL` | Время жизни refresh token (сек)     | `2592000`                   |
| `COOKIE_DOMAIN`            | Домен cookie (для SSO)              | `.example.com`              |
| `COOKIE_SECURE`            | Флаг Secure для cookie              | `true`                      |

---

## Приложение B: Схема БД (OAuth)

```sql
-- oauth_clients: зарегистрированные OAuth-клиенты
CREATE TABLE oauth_clients (
    id            BIGINT PRIMARY KEY AUTO_INCREMENT,
    name          VARCHAR(100),
    client_id     VARCHAR(80) NOT NULL UNIQUE,
    client_secret VARCHAR(255) NOT NULL,         -- bcrypt hash
    redirect_uri  TEXT,
    grant_types   VARCHAR(500),                   -- "password refresh_token"
    scope         VARCHAR(2000) NOT NULL,
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- oauth_access_tokens: выданные access tokens
CREATE TABLE oauth_access_tokens (
    id            BIGINT PRIMARY KEY AUTO_INCREMENT,
    token_id      VARCHAR(80) NOT NULL UNIQUE,    -- JWT jti
    user_id       BIGINT NOT NULL,
    client_id     BIGINT NOT NULL,
    access_token  VARCHAR(64) NOT NULL UNIQUE,    -- HMAC JWT
    expires_at    TIMESTAMP NOT NULL,
    scope         VARCHAR(2000),
    revoked_at    TIMESTAMP NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES oauth_clients(id) ON DELETE CASCADE
);

-- oauth_refresh_tokens: refresh tokens
CREATE TABLE oauth_refresh_tokens (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    access_token_id BIGINT NOT NULL,
    refresh_token   VARCHAR(64) NOT NULL UNIQUE,  -- HMAC refresh token
    expires_at      TIMESTAMP NOT NULL,
    revoked_at      TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens(id) ON DELETE CASCADE
);
```

---

## История изменений

| Версия  | Дата       | Изменения                                                                                                                                                                                                                                                                                             |
|---------|------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1.1     | 2026-09-16 | Реализован Client Credentials Grant (M2M через `/token`). Реализован Authorization Code Grant (SSO через `/auth/authorize` + `/token`). Добавлен `/token/introspect` для проверки истечения и статуса токенов (RFC 7662). Обновлены примеры кода для M2M и SSO. Добавлена таблица `oauth_auth_codes`. |
| 1.0     | 2026-09-15 | Первая версия документа                                                                                                                                                                                                                                                                               |