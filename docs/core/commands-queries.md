# Команды, запросы и хендлеры ядра (CQRS)

> Документ описывает команды (запись) и запросы (чтение) ядра, их хендлеры, параметры и логику.

## 1. Роли (Roles)

### 1.1. Создание роли — `CreateRoleCommand` / `CreateRoleHandler`

**Command:** `name: RoleName`, `description: ?string`.

Логика хендлера:
1. Проверяет уникальность имени (репозиторий `IRoleRepository->findByName`).
2. Создаёт сущность `Role` и сохраняет (`save`).
3. Возвращает `RoleDto`.

Исключения: `EntityAlreadyExistsException` (роль с таким именем уже есть).

### 1.2. Обновление роли — `UpdateRoleCommand` / `UpdateRoleHandler`

**Command:** `id: int`, `name: RoleName`, `description: ?string`.

Логика:
1. Находит роль (`findById`); если нет — `EntityNotFoundException`.
2. Переименовывает (`rename`), меняет описание, сохраняет.
3. Возвращает `RoleDto`.

### 1.3. Удаление роли — `DeleteRoleCommand` / `DeleteRoleHandler`

**Command:** `id: int`.

Логика:
1. Находит роль; если нет — `EntityNotFoundException`.
2. Удаляет. Поведение при наличии назначений зависит от внешних ключей (CASCADE в миграции).
3. Возвращает `void` (контроллер возвращает `SuccessDto`).

### 1.4. Список ролей — `ListRoleQuery` / `ListRoleHandler`

**Query:** `RoleFiltersDto` (`ids`, `limit`, `offset`, `orderBy`, `name`, `description`, `createdFrom`, `createdTo`).

Использует `IUserRoleSearch`/`IRoleRepository` для выборки с фильтрами. Возвращает `RoleDto[]`.

### 1.5. Получение роли — `GetRoleQuery` / `GetRoleHandler`

**Query:** `id: int`.

Возвращает `RoleDto`; если роли нет — `EntityNotFoundException`.

---

## 2. Назначение ролей (User Role)

### 2.1. Назначить роль — `AssignUserRoleCommand` / `AssignUserRoleHandler`

**Command:** `userId: int`, `roleId: int`, `assignedBy: ?int`.

Логика:
1. Проверяет существование пользователя и роли.
2. Создаёт `UserRole` и сохраняет (через `IUserRoleRepository`).
3. При дублирующем назначении — `EntityAlreadyExistsException`.

### 2.2. Снять роль — `RemoveUserRoleCommand` / `RemoveUserRoleHandler`

**Command:** `userRoleId: int`.

Логика:
1. Находит назначение (`findById`); если нет — `EntityNotFoundException`.
2. Удаляет.

### 2.3. Список назначений — `ListUserRoleQuery` / `ListUserRoleHandler`

**Query:** `UserRoleFiltersDto` (`userId`, `roleId`, `assignedBy`, `assignedFrom`, `assignedTo`, …).

Использует `IUserRoleSearch`. Возвращает `UserRoleDto[]`.

---

## 3. Пользователи (Users)

### 3.1. Синхронизация с Passport — `SyncUserCommand` / `SyncUserHandler`

**Command:** `passportId: int`, `email: string`, `surname`, `name`, `patronymic`, `post`, `isOwner: bool`.

Это ключевой хендлер, вызывается из `HandleSsoCallbackHandler` (модуль passport) при успешном входе.

Логика (в транзакции `ITransactionManager->run`):
1. Создаёт/находит локального пользователя по email (`existsByEmail`).
2. Сохраняет пользователя (`IUserRepository->save`).
3. Назначает базовую роль **`user`** (`IRoleRepository->findByName('user')` → `IUserRoleRepository->sync`).
4. Если `isOwner === true` (владелец в Passport) — дополнительно назначает роль **`admin`**.
5. **Если пользователь новый** (`!$userExists`) — диспатчит `UserFirstLoginEvent` (на него подписан модуль projects: создание личного проекта).

Возвращает `UserDto`.

> ⚠️ Порядок: сначала назначается роль `user`, затем (если owner) `admin`. Обе роли «синкаются», дубликаты не создаются.

### 3.2. Список пользователей — `ListUserQuery` / `ListUserHandler`

**Query:** `UserFiltersDto` (`email`, `surname`, `name`, `isOwner`, `passportId`, `post`, диапазоны дат, …).

Использует `IUserRepository`/поиск. Возвращает `UserDto[]`.

### 3.3. Получение пользователя — `GetUserQuery` / `GetUserHandler`

**Query:** `id: int`.

Возвращает `UserDto`; если нет — `EntityNotFoundException`.

---

## 4. Таблица: команда/запрос → хендлер → порты

| Команда/Запрос | Хендлер | Порты (dependency) |
|---|---|---|
| `CreateRoleCommand` | `CreateRoleHandler` | `IRoleRepository` |
| `UpdateRoleCommand` | `UpdateRoleHandler` | `IRoleRepository` |
| `DeleteRoleCommand` | `DeleteRoleHandler` | `IRoleRepository` |
| `ListRoleQuery` | `ListRoleHandler` | `IRoleRepository` (+ поиск) |
| `GetRoleQuery` | `GetRoleHandler` | `IRoleRepository` |
| `AssignUserRoleCommand` | `AssignUserRoleHandler` | `IUserRoleRepository`, `IUserRepository`, `IRoleRepository` |
| `RemoveUserRoleCommand` | `RemoveUserRoleHandler` | `IUserRoleRepository` |
| `ListUserRoleQuery` | `ListUserRoleHandler` | `IUserRoleSearch` |
| `SyncUserCommand` | `SyncUserHandler` | `IUserRepository`, `IRoleRepository`, `IUserRoleRepository`, `ITransactionManager`, `IEventDispatcher` |
| `ListUserQuery` | `ListUserHandler` | `IUserRepository`/поиск |
| `GetUserQuery` | `GetUserHandler` | `IUserRepository` |

## 5. Контроллеры ядра

| Контроллер | Действия | Маршруты |
|---|---|---|
| `RoleController` | index/view/create/update/delete | `GET/POST /roles`, `GET/PUT/DELETE /roles/<id>` |
| `UserController` | index/view/sync | `GET /users`, `GET /users/<id>`, `GET /users/sync` |
| `UserRoleController` | index/create/delete | `GET/POST /user-roles`, `DELETE /user-roles/<id>` |
| `WelcomeController` | index | `GET /` |
| `SwaggerController` | json/ui | `GET /docs/swagger/json`, `GET /docs/swagger` |
| `DocsController` | ui | `GET /docs`, `GET /docs/<page>` |

Маршруты объявлены в `config/web.php` (секция `urlManager.rules`), модульные — в `modules/*/config/routing.php`.