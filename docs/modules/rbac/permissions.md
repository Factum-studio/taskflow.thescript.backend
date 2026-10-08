# Разрешения (permissions), роли и ранги

> Документ описывает каталог разрешений, базовые роли, связи роль↔разрешение и ранговую иерархию.

## 1. Таблица `permissions`

Создаётся и заполняется миграцией `m260922_080010_create_permission_tables.php`.

| Код | Описание |
|---|---|
| `users.list` | Просмотр списка локальных пользователей |
| `users.read.self` | Просмотр собственного локального пользователя |
| `users.read.any` | Просмотр любого локального пользователя |
| `user_roles.read` | Просмотр назначенных ролей |
| `user_roles.manage` | Назначение и снятие ролей |
| `roles.read` | Просмотр ролей |
| `roles.manage` | Создание, изменение и удаление ролей |
| `system.manage` | Управление системными справочниками |
| `rbac.permissions.manage` | Управление permissions ролей |

> В `params.php` (permission_rules) упоминаются также логические `roles.manage` (POST/PUT/DELETE /roles) и `roles.read` (GET /roles), `users.list`/`users.read` (GET /users), `user_roles.read|manage` — они маппятся на конкретные коды через `resolveConcretePermission` (self/any). Отдельный пермишен `users.read` в БД НЕ заводится — есть `.self` и `.any`.

## 2. Таблица `role_permissions`

Связь многие-ко-многим: `(role_id, permission_id)` — композитный PK, FK на `roles` и `permissions` (CASCADE).

## 3. Сид ролей и связей

### Роли (ядро, миграция `m260922_080002_seed_basic_data.php`)

| Роль | Описание |
|---|---|
| `user` | Зарегистрированный пользователь |
| `admin` | Админ, имеет все полномочия в системе |

### Связи роль → разрешения (сеются в миграции permissions)

| Роль | Разрешения |
|---|---|
| `user` | `users.read.self`, `user_roles.read` |
| `admin` | все: `users.list`, `users.read.any`, `user_roles.manage`, `roles.read`, `roles.manage`, `system.manage`, `rbac.permissions.manage` |

## 4. Ранги (role_ranks)

Заданы в `modules/rbac/config/params.php`:

```php
'role_ranks' => [
    'user'  => 10,
    'admin' => 50,
],
```

**Правило:** субъект может назначить/снять роль только с рангом **ниже** своего собственного.

- `user` (ранг 10) не может назначать никакие роли (нет ролей ниже 10) → назначать может только admin.
- `admin` (ранг 50) может назначать `user` (10) и снимать её.

Это защищает от эскалации привилегий: даже имея разрешение `user_roles.manage`, нельзя выдать роль `admin` без ранга 50+.

## 5. Автоматическое назначение ролей при синхронизации

`SyncUserHandler` (ядро):
1. Всегда назначается роль `user`.
2. Если `isOwner == true` (владелец в Passport) — дополнительно роль `admin`.

Роли «синкаются» через `IUserRoleRepository::sync()` — повторные вызовы не создают дубликаты.

## 6. PermissionDto

**Файл:** `modules/rbac/application/dto/PermissionDto.php`

Представление разрешения для контроллера `PermissionController` и `RolePermissionController` (поля: `id`, `code`, `description`; фабрика `fromEntity`/`fromRow`).

## 7. Permission (value object)

**Файл:** `modules/rbac/domain/valueObject/Permission.php`

Обёртка над кодом разрешения (строка). Используется как результат `IPermissionRepository::findByCode()`.