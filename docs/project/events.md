# События модуля Projects

Модуль генерирует доменные события, часть из которых пробрасывается в глобальную шину.

## Локальные события

### `ProjectCreatedEvent`
**Когда возникает**: после успешного создания проекта (любого типа).

**Поля**:
- `projectId` (int)
- `ownerId` (int)
- `occurredAt` (DateTimeImmutable)

**Обработчики**:
- `AddOwnerAsMemberListener` – добавляет владельца как участника с ролью `admin`.

## Форвардинг в глобальную шину

В `modules/projects/config/di.php` указано:

```
$forwardEvents = [
    ProjectCreatedEvent::class,
];
```

Это означает, что `ProjectCreatedEvent` также отправляется в `GlobalEventDispatcher` (core). Другие модули могут на него подписаться.

## Планируемые события
- `ProjectMemberAddedEvent`
- `ProjectMemberRemovedEvent`
- `BoardCreatedEvent`