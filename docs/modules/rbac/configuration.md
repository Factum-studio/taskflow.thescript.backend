# Конфигурация модуля rbac

> Документ описывает параметры модуля (params.php), DI-регистрацию и маршруты.

## 1. `config/params.php`

```php
return [
    // Публичные маршруты (не блокируются RBAC)
    'public_routes' => [
        'GET /',
        'GET /*',
        'POST /*',
        'PUT /*',
        'DELETE /*',
        'GET /auth/*',
        'GET /gii',
        'GET /debug',
        'GET /debug/*',
        'GET /docs',
        'GET /docs/*',
        'OPTIONS *',
    ],

    // Правила RBAC: метод → паттерн → разрешение
    'permission_rules' => [
        // RBAC administration
        ['method' => 'GET',    'pattern' => '~^/rbac/permissions$~', 'permission' => 'rbac.permissions.manage'],
        ['method' => 'GET',    'pattern' => '~^/rbac/roles/\d+/permissions$~', 'permission' => 'rbac.permissions.manage'],
        ['method' => 'POST',   'pattern' => '~^/rbac/roles/\d+/permissions/\d+$~', 'permission' => 'rbac.permissions.manage'],
        ['method' => 'DELETE', 'pattern' => '~^/rbac/roles/\d+/permissions/\d+$~', 'permission' => 'rbac.permissions.manage'],

        // Users
        ['method' => 'GET', 'pattern' => '~^/users$~', 'permission' => 'users.list'],
        ['method' => 'GET', 'pattern' => '~^/users/(?<userId>\d+)$~', 'permission' => 'users.read',
         'subject' => ['mode' => 'self-or-any', 'source' => 'path', 'key' => 'userId']],

        // User roles
        ['method' => 'GET',    'pattern' => '~^/user-roles$~', 'permission' => 'user_roles.read'],
        ['method' => 'POST',   'pattern' => '~^/user-roles$~', 'permission' => 'user_roles.manage', 'policy' => 'role-assignment'],
        ['method' => 'DELETE', 'pattern' => '~^/user-roles/(?<id>\d+)$~', 'permission' => 'user_roles.manage', 'policy' => 'role-assignment'],

        // Roles
        ['method' => 'GET',  'pattern' => '~^/roles$~', 'permission' => 'roles.read'],
        ['method' => 'GET',  'pattern' => '~^/roles/\d+$~', 'permission' => 'roles.read'],
        ['method' => 'POST', 'pattern' => '~^/roles$~', 'permission' => 'roles.manage'],
        ['method' => 'PUT',  'pattern' => '~^/roles/\d+$~', 'permission' => 'roles.manage'],
        ['method' => 'DELETE', 'pattern' => '~^/roles/\d+$~', 'permission' => 'roles.manage'],
    ],

    // Ранговая иерархия ролей
    'role_ranks' => [
        'user'  => 10,
        'admin' => 50,
    ],
];
```

> ⚠️ Порядок записей в `permission_rules` важен — используется первый подходящий `pattern` для заданного `method` (метод `findRule` проходит по массиву по порядку).

## 2. `config/di.php`

Регистрирует:

- `IAuthorizationService` → `AuthorizationService` (с `IPermissionRepository`).
- `IPermissionRepository` → `DbPermissionRepository`.
- `ISubjectResolver` → `DbSubjectResolver`.
- `IRoleAssignmentPolicy` → `DbRoleAssignmentPolicy`.
- `RbacMiddleware` (с `public_routes`, `permission_rules`, `role_ranks` из params).

## 3. `config/routing.php`

```php
return [
    'GET rbac/permissions'                                          => 'rbac/permission/index',
    'GET rbac/roles/<roleId:\d+>/permissions'                       => 'rbac/role-permission/index',
    'POST rbac/roles/<roleId:\d+>/permissions/<permissionId:\d+>'   => 'rbac/role-permission/grant',
    'DELETE rbac/roles/<roleId:\d+>/permissions/<permissionId:\d+>' => 'rbac/role-permission/revoke',
];
```

Подключается в `config/web.php` (array_merge в urlManager.rules). DI-файл подключается в `config/web.php` через `$diConfigs`.

## 4. Что задаётся в коде (не конфиг)

| Что | Где |
|---|---|
| Ранги ролей (значения 10/50) | params.php — **конфиг**, но сами роли известны ядру |
| Стартовые роли: `user`, `admin` | миграции ядра (seed) |
| `subject`-механика (path/query/body/owner) | код middleware + AuthorizationService |
| Поведение `*`/`.self`/`.any`/`.*` | код `AuthorizationService::matches()` |

## 5. Контроллеры

| Контроллер | Действия | Маршруты |
|---|---|---|
| `PermissionController` | index | `GET /rbac/permissions` |
| `RolePermissionController` | index, grant, revoke | см. routing.php |

Методы реализуют администрирование RBAC (требуют `rbac.permissions.manage`).