# Безопасность и идентификация (YiiIdentity)

> Документ описывает `YiiIdentity` — имплементацию `IdentityInterface` для аутентифицированного субъекта.

## 1. YiiIdentity

**Файл:** `core/security/YiiIdentity.php`

Устанавливается в `Yii::$app->user->setIdentity(...)` в `PassportAuthMiddleware` на основе JWT из куки/заголовка.

### Конструктор

```php
new YiiIdentity(
    string $id,             // sub из JWT (внешний ID в Passport)
    string $clientId,       // client_id из JWT
    string $tokenId,        // jti (ID токена)
    array  $scopes,         // разрешённые OAuth-скоупы
    string $subjectType     // 'user' | ? (по умолчанию 'user')
)
```

### Основные методы

| Метод | Возвращает | Описание |
|---|---|---|
| `getId()` | string | Идентификатор субъекта (Passport sub) |
| `getClientId()` | string | OAuth-клиент (например, `'script-taskflow'`) |
| `getTokenId()` | string | ID токена (jti) |
| `getScopes()` | string[] | Массив разрешённых OAuth-скоупов |
| `hasScope(string $scope)` | bool | Есть ли конкретный скоуп |
| `getSubjectType()` | string | `'user'` — основной тип |
| `isUser()` | bool | `true`, если subjectType === 'user' |
| `getAuthKey()` | null | Не используется (типовой Yii) |
| `validateAuthKey($authKey)` | false | Не используется |
| `findIdentity($id)` | null | Не используется |
| `findIdentityByAccessToken($token, $type)` | null | Не используется |

### Как используется

- В контроллерах: `$this->getUserId()` / `$this->getUserIdentity()`.
- В RBAC: `AuthorizationService::canForIdentity()` и `authorizeForIdentity()`.
- В `BaseController::actionCreate/Update` — проверка, что пользователь аутентифицирован.

## 2. OAuth-скоупы

Список скоупов, запрашиваемых у Passport (задан жёстко в `modules/passport/auth/config/params.php`):

```
user:read, user:write, user-educations:read, user-educations:write,
user-contacts:read, user-contacts:write
```

Скоупы передаются в JWT и доступны через `YiiIdentity::getScopes()`.

## 3. Аутентификация (PassportAuthMiddleware)

См. [passport/flow.md](../modules/passport/flow.md).

Кратко:
1. Middleware извлекает access-токен (Authorization: Bearer или кука `taskflow_access_token`).
2. Валидирует JWT (RS256, публичный ключ, exp, nbf, aud, client_id).
3. При истечении — пытается обновить через refresh-токен.
4. Устанавливает `YiiIdentity` в `Yii::$app->user->identity`.

## 4. Авторизация (RBAC)

Проверка маршрутов — `RbacMiddleware` (вызывается сразу после PassportAuthMiddleware):

1. Проверка публичного маршрута (без прав).
2. Поиск правила `permission_rules` для `(method, pattern)`.
3. Разрешение конкретного permissions с учётом контекста (self-or-any, owner-only).
4. Для групповых назначений — проверка ранговой политики.

См. [rbac/middleware.md](../modules/rbac/middleware.md).

## 5. Роли и разрешения

Глобальные системные роли — **user** и **admin**. Разрешения seed'ятся в миграции `m260922_080010_create_permission_tables.php`. Связь ролей и разрешений:

| Роль | Разрешения |
|---|---|
| `user` | `users.read.self` (свой профиль), `user_roles.read` (свои роли) |
| `admin` | всё: `users.list`, `users.read.any`, `user_roles.manage`, `roles.*`, `system.manage`, `rbac.permissions.manage` |

Ранги (заданы в `modules/rbac/config/params.php`):

| Роль | Ранг |
|---|---|
| `user` | 10 |
| `admin` | 50 |

Администратор может назначать/снимать роли только ниже своего ранга.