# Управление доступом в модуле projects (IProjectAccess / ProjectAccess)

> Документ описывает модель прав на проекты и доски: роли участников, ограничения, лимиты.

## 1. Порт `IProjectAccess`

**Файл:** `modules/projects/application/port/IProjectAccess.php`

```php
interface IProjectAccess
{
    public function getUserProjectIds(int $userId): array;
    public function getProjectBoardIds(int $projectId): array;
    public function getProjectIdByBoardId(int $boardId): ?int;

    public function canViewProject(int $userId, int $projectId): bool;
    public function canViewBoard(int $userId, int $boardId): bool;
    public function canManageProject(int $userId, int $projectId): bool;
    public function canManageBoard(int $userId, int $boardId): bool;
    public function canManageUser(int $userId, int $targetUserId, int $projectId): bool;
    public function canAddMember(int $userId, int $targetUserId, int $projectId): bool;
    public function canInviteUser(int $userId, int $targetUserId, int $projectId): bool;
    public function canRemoveUser(int $userId, int $targetUserId, int $projectId): bool;
    public function canDeleteProject(int $userId, int $projectId): bool;
    public function canDeleteBoard(int $userId, int $boardId): bool;
    public function canCreateBoard(int $userId, int $projectId): bool;
    public function canCreateProject(int $userId): bool;
}
```

## 2. Реализация `ProjectAccess`

**Файл:** `modules/projects/infrastructure/access/ProjectAccess.php`

### Константы-лимиты (захардкожены в коде!)

| Константа | Значение | Описание |
|---|---|---|
| `MAX_USER_PROJECTS` | 10 | Максимум проектов, владельцем которых может быть пользователь |
| `MAX_PROJECT_BOARDS` | 4 | Максимум досок в одном проекте |

> ⚠️ Эти лимиты заданы жёстко в коде (не в конфиге). Изменение — правка класса и, возможно, тестов.

## 3. Таблица прав

| Метод | Владелец (owner) | Админ проекта | Участник (member) | Не участник |
|---|---|---|---|---|
| `canViewProject` | ✅ | ✅ | ✅ | ❌ |
| `canViewBoard` | ✅ | ✅ | ✅ | ❌ |
| `canManageProject` | ✅ | ✅ | ❌ | ❌ |
| `canManageBoard` | ✅ | ✅*; создатель доски тоже ✅ | ❌ | ❌ |
| `canManageUser` | ✅ | ✅ (см. оговорки) | ❌ | ❌ |
| `canAddMember` | ❌ | ❌ (см. типы проектов) | ❌ | ❌ |
| `canInviteUser` | ✅ | ✅ | ❌ | ❌ |
| `canRemoveUser` | ✅ | ✅ (оговорки) | ❌ | ❌ |
| `canDeleteProject` | ✅ (не personal) | ❌ | ❌ | ❌ |
| `canDeleteBoard` | ✅ | ❌ | ❌ | ❌ |
| `canCreateBoard` | ✅ | ✅ (лимит 4) | ❌ | ❌ |
| `canCreateProject` | по лимиту 10 | по лимиту | по лимиту | по лимиту |

\* `canManageBoard`: админ проекта ✅; иначе — создатель доски (`board.created_by == userId`).

## 4. Особые правила

### 4.1. Управление участниками (`canManageUser`, `canRemoveUser`)
- Только админ/владелец.
- **Нельзя** управлять владельцем проекта.
- **Нельзя** удалить/понизить **последнего админа** (кроме владельца): если цель — админ и админов в проекте ≤ 1 — `false`.

### 4.2. Типы проектов и добавление участников (`canAddMember`, `canInviteUser`)
- `personal` → `canAddMember` всегда `false` (можно только invite? нет — invite тоже false для personal, см. код).
- `collaborative` → `canAddMember` false (добавление только по приглашению).
- `corporate` → `canAddMember` true только если **обе компании совпадают** (одинаковый `company_id`); `canInviteUser` true только если компании **различаются**.
- Для `canInviteUser` также нужно быть админом/владельцем.

> Компания пользователя читается из `UserAR::company_id` (`getUserCompanyId`). **TODO в коде:** вынести в отдельный сервис / привязать проекты к компании (см. [known-issues-and-roadmap](../../known-issues-and-roadmap.md)).

### 4.3. Удаление
- Проект: только владелец; **personal-проект удалить нельзя**.
- Доска: только владелец проекта.

## 5. Использование модулем tasks

`TaskAccess` (модуль tasks) делегирует проверки прав на доску в `IProjectAccess`:

- `canViewTask` → `canViewBoard(projectAccess)`.
- `canCreateTask` → `canViewBoard`.
- `canHardDeleteTask` → `canManageBoard`.
- `canCreateColumn` / `canUpdateColumn` → `canManageBoard`.

См. [tasks/access.md](../tasks/access.md).

## 6. Как пользоваться в хендлерах

```php
if (!$this->projectAccess->canManageProject($userId, $projectId)) {
    throw new PermissionDeniedException('Недостаточно прав для управления проектом');
}
```

Хендлеры получают `IProjectAccess` через DI (реализация — `ProjectAccess`).