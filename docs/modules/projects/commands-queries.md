# Команды и запросы модуля projects

> Полный справочник CQRS-команд и запросов модуля: параметры, логика, исключения.

## 1. Проекты

### 1.1. `CreateProjectCommand` → `CreateProjectHandler`

Параметры: `name: string`, `type: string`, `ownerId: int`, `settings: array = []`.

Логика:
1. Проверка права `IProjectAccess::canCreateProject($ownerId)` (лимит проектов на владельца).
2. Создание сущности `Project`, сохранение.
3. Диспатч `ProjectCreatedEvent`.
4. (Событие обрабатывает `AddOwnerAsMemberListener` — владелец становится участником.)

### 1.2. `UpdateProjectCommand` → `UpdateProjectHandler`

Параметры: `id: int`, `updatedBy: int`, `name: ?string = null`, `settings: ?array = null`.

Логика:
1. Проверка права `canManageProject($updatedBy, $id)` (owner или admin).
2. Найти проект (`findById`), иначе — ошибка not found.
3. `rename()` / `changeSettings()`.
4. Сохранение, возврат `ProjectDto`.

### 1.3. `DeleteProjectCommand` → `DeleteProjectHandler`

Параметры: `id: int`, `userId: int`.

Логика:
1. Проверка `canDeleteProject($userId, $id)` (только owner, personal-проект удалить нельзя).
2. Удаление проекта (вместе с досками — см. FK).

### 1.4. `GetProjectQuery` → `GetProjectHandler`

Параметры: `id: int`, `userId: int`.

Логика: проверка `canViewProject` → возврат `ProjectDto`.

### 1.5. `ListUserProjectsQuery` → `ListUserProjectsHandler`

Параметры: `userId: int`.

Логика: список проектов, где пользователь — участник (включая владельца).

## 2. Доски

### 2.1. `CreateBoardCommand` → `CreateBoardHandler`

Параметры: `projectId: int`, `name: string`, `createdBy: int`, `description: ?string`, `settings: array = []`.

Проверка `canCreateBoard($createdBy, $projectId)` (лимит досок на проект — `MAX_PROJECT_BOARDS = 4`).

### 2.2. `UpdateBoardCommand` → `UpdateBoardHandler`

Параметры: `id: int`, `updatedBy: int`, `name`, `description`, `settings`.

Проверка `canManageBoard`.

### 2.3. `DeleteBoardCommand` → `DeleteBoardHandler`

Параметры: `id: int`, `userId: int`.

Проверка `canDeleteBoard` (только владелец проекта).

### 2.4. `GetBoardQuery` → `GetBoardHandler`

Параметры: `id: int`, `userId: int`.

### 2.5. `ListProjectBoardsQuery` → `ListProjectBoardsHandler`

Параметры: `projectId: int`, `userId: int`.

Проверка доступа к проекту; возврат списка `BoardDto`.

## 3. Участники

### 3.1. `AddProjectMemberCommand` → `AddProjectMemberHandler`

Параметры: `projectId`, `userId`, `role: string`, `addedBy`.

Проверка `canAddMember($addedBy, $userId, $projectId)`. Personal/collaborative — `false` (нельзя добавлять напрямую); corporate — только в пределах компании.

### 3.2. `ChangeMemberRoleCommand` → `ChangeMemberRoleHandler`

Параметры: `projectId`, `userId`, `newRole`, `changedBy`.

Проверка `canManageUser`. Нельзя менять роль владельца; нельзя снять последнего админа.

### 3.3. `RemoveProjectMemberCommand` → `RemoveProjectMemberHandler`

Параметры: `projectId`, `userId`, `removedBy`.

Проверка `canRemoveUser`. Нельзя удалить владельца; нельзя удалить последнего админа.

### 3.4. `ListProjectMembersQuery` → `ListProjectMembersHandler`

Параметры: `projectId`, `userId` (запрашивающий).

Проверка доступа к проекту; возврат списка `ProjectUserDto`.

## 4. Приглашения

### 4.1. `InviteUserCommand` → `InviteUserHandler`

Параметры: `projectId`, `invitedBy`, `email: ?string`, `userId: ?int`.

Логика:
1. Если `email` пустой и `userId` пустой — ошибка валидации.
2. Проверка прав (`canInviteUser`).
3. Создание приглашения с токеном, статус `pending`.
4. Диспатч `InvitationCreatedEvent` → `SendInvitationEmailListener` отправляет письмо.

### 4.2. `AcceptInvitationCommand` → `AcceptInvitationHandler`

Параметры: `token: string`, `userId: int`.

Логика:
1. Найти приглашение по токену (иначе 404).
2. Проверить, что пользователь — получатель (email совпадает или userId).
3. Создать `ProjectUser` (роль из приглашения), статус приглашения → `accepted`.
4. Диспатч `InvitationAcceptedEvent`.

### 4.3. `CancelInvitationCommand` → `CancelInvitationHandler`

Параметры: `projectId`, `email`, `userId` (кто отменяет).

Проверка прав; статус приглашения → `cancelled`. Диспатч `InvitationCancelledEvent`.

## 5. Сводная таблица

| Объект | Команда/Запрос | Хендлер | Право |
|---|---|---|---|
| Проект | Create | CreateProjectHandler | `canCreateProject` |
| Проект | Update | UpdateProjectHandler | `canManageProject` |
| Проект | Delete | DeleteProjectHandler | `canDeleteProject` (owner) |
| Проект | Get | GetProjectHandler | `canViewProject` |
| Проект | List (мои) | ListUserProjectsHandler | участник |
| Доска | Create | CreateBoardHandler | `canCreateBoard` |
| Доска | Update | UpdateBoardHandler | `canManageBoard` |
| Доска | Delete | DeleteBoardHandler | `canDeleteBoard` (owner) |
| Доска | Get | GetBoardHandler | `canViewBoard` |
| Доска | List | ListProjectBoardsHandler | `canViewProject` |
| Участник | Add | AddProjectMemberHandler | `canAddMember` |
| Участник | ChangeRole | ChangeMemberRoleHandler | `canManageUser` |
| Участник | Remove | RemoveProjectMemberHandler | `canRemoveUser` |
| Участник | List | ListProjectMembersHandler | `canViewProject` |
| Приглашение | Invite | InviteUserHandler | `canInviteUser` |
| Приглашение | Accept | AcceptInvitationHandler | — (владелец токена) |
| Приглашение | Cancel | CancelInvitationHandler | `canManageProject` / владелец |