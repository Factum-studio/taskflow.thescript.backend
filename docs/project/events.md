# События модуля Projects

Модуль генерирует доменные события, часть из которых пробрасывается в глобальную шину.

## Локальные события

### `ProjectCreatedEvent`
**Когда возникает**: после успешного создания проекта (любого типа).

**Поля**:
- `projectId` (int)
- `ownerId` (int)
- `occurredAt` (DateTimeImmutable)

### `ProjectMemberAddedEvent`
**Когда возникает**: после добавления участника в проект (вручную или через приглашение).

**Поля**:
- `projectId` (int)
- `userId` (int)
- `role` (string) – admin/member
- `addedBy` (int)
- `occurredAt` (DateTimeImmutable)

### `ProjectMemberRemovedEvent`
**Когда возникает**: после удаления участника из проекта.

**Поля**:
- `projectId` (int)
- `userId` (int)
- `removedBy` (int)
- `occurredAt` (DateTimeImmutable)

### `BoardCreatedEvent`
**Когда возникает**: после создания новой доски в проекте.

**Поля**:
- `boardId` (int)
- `projectId` (int)
- `createdBy` (int)
- `occurredAt` (DateTimeImmutable)

### `InvitationCreatedEvent`
**Когда возникает**: после того, как администратор отправил приглашение по email.

**Поля**:
- `invitationId` (int)
- `projectId` (int)
- `email` (string)
- `invitedBy` (int)
- `token` (string)
- `occurredAt` (DateTimeImmutable)

### `InvitationAcceptedEvent`
**Когда возникает**: после того, как пользователь принял приглашение по токену.

**Поля**:
- `invitationId` (int)
- `projectId` (int)
- `email` (string)
- `userId` (int)
- `occurredAt` (DateTimeImmutable)

### `InvitationCancelledEvent`
**Когда возникает**: после отмены приглашения администратором проекта.

**Поля**:
- `invitationId` (int)
- `projectId` (int)
- `email` (string)
- `cancelledBy` (int)
- `occurredAt` (DateTimeImmutable)

## Форвардинг в глобальную шину

В `modules/projects/config/di.php` определен массив `$forwardEvents`, содержащий все перечисленные выше события.
Это означает, что все они также отправляются в `GlobalEventDispatcher` (core) и доступны для подписки из других модулей.

## Обработчики

- `AddOwnerAsMemberListener` – добавляет владельца как участника при создании проекта.
- `SendInvitationEmailListener` – отправляет email-уведомление о приглашении.
- `ProjectLoggerListener` – логирует все события в файл `runtime/logs/projects-info.log`.