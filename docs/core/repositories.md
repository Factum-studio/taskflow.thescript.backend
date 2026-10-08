# Репозитории ядра: порты и реализации

> Документ описывает порты (интерфейсы) репозиториев ядра, их реализации и транзакции.

## 1. Порты (интерфейсы)

Все порты находятся в `core/application/port/`.

| Интерфейс | Методы | Назначение |
|---|---|---|
| `IUserRepository` | `save(User): void`, `findById(UserId): ?User`, `findByEmail(Email): ?User`, `existsByEmail(Email): bool`, `delete(UserId): void`, … | Пользователи |
| `IRoleRepository` | `save(Role): void`, `findById(RoleId): ?Role`, `findByName(RoleName): ?Role`, `delete(RoleId): void`, … | Роли |
| `IUserRoleRepository` | `assign(UserRole): void`, `remove(UserRoleId): void`, `sync(UserRole): void`, `findById(UserRoleId): ?UserRole`, … | Назначения ролей |
| `IUserRoleSearch` | `search(UserRoleFiltersDto): UserRole[]`, … | Поиск назначений с фильтрами |
| `ITransactionManager` | `run(callable): mixed` | Обёртка транзакций |
| `IEventDispatcher` | `addListener`, `dispatch` | Шина событий |
| `ITaskStatisticsService` | (см. tasks) | Агрегация статистики задач (реализуется модулем tasks) |

## 2. Реализации (infrastructure)

| Порт | Реализация | Зависимости |
|---|---|---|
| `IUserRepository` | `DbUserRepository` | `Yii::$app->db`, `UserAR` |
| `IRoleRepository` | `DbRoleRepository` | `Yii::$app->db`, `RoleAR` |
| `IUserRoleRepository` | `DbUserRoleRepository` | `Yii::$app->db`, `UserRoleAR` |
| `IUserRoleSearch` | `DbUserRoleSearch` | `Yii::$app->db` |
| `ITransactionManager` | `DbTransactionManager` | `Yii::$app->db` |
| `IEventDispatcher` | `GlobalEventDispatcher` | контейнер |

## 3. ActiveRecord-модели (persistence)

`core/infrastructure/persistence/`:

| Модель | Таблица |
|---|---|
| `UserAR` | `users` |
| `RoleAR` | `roles` |
| `UserRoleAR` | `user_roles` |

ActiveRecord-модели используются ТОЛЬКО внутри репозиториев. Хендлеры работают с доменными сущностями, а не с AR.

## 4. Схема таблиц

### `users`

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | ID |
| `passport_id` | string(64) UNIQUE | ID в Passport |
| `surname` | string(128) | Фамилия |
| `name` | string(128) | Имя |
| `patronymic` | string(128) NULL | Отчество |
| `email` | string(255) UNIQUE | Email |
| `post` | string(255) NULL | Должность |
| `is_owner` | boolean def false | Владелец (Passport) |
| `synced_at` | timestamp NULL | Последняя синхронизация |
| `created_at` / `updated_at` | timestamp | Служебные |

### `roles`

| Колонка | Тип |
|---|---|
| `id` | bigint PK |
| `name` | string(50) UNIQUE |
| `description` | string(255) NULL |
| `created_at` | timestamp |

### `user_roles`

| Колонка | Тип | FK |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint | → users.id (CASCADE) |
| `role_id` | bigint | → roles.id (CASCADE) |
| `assigned_at` | timestamp | |
| `assigned_by` | bigint NULL | |

### Таблицы RBAC (модуль rbac)

`permissions`, `role_permissions` — см. [modules/rbac/architecture.md](../modules/rbac/architecture.md).

## 5. Транзакции

`ITransactionManager::run(callable)` (реализация `DbTransactionManager`):

```php
$this->transactionManager->run(function () use ($user): void {
    // несколько операций внутри одной транзакции
});
```

Использование: `SyncUserHandler` (создание пользователя + роли), хендлеры проектов/задач при необходимости.

## 6. Как добавить репозиторий

1. Интерфейс в `core/application/port/` (или `modules/<m>/domain/repository/` для модулей).
2. Реализация `DbXxxRepository` в `core/infrastructure/repository/` (или `modules/<m>/infrastructure/repository/`).
3. АР-модель в `persistence/` (если нужно).
4. Регистрация в DI (config/container.php для ядра, modules/<m>/config/di.php для модулей).
5. Миграция таблицы.