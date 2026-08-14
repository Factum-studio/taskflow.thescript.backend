# События ядра (core)

Глобальный диспетчер событий (`GlobalEventDispatcher`) позволяет подписываться на события из любого модуля.

## Список событий

### `UserFirstLoginEvent`
**Когда возникает**: при первом входе пользователя (создаётся локальная запись в таблице `user`).

**Поля**:
- `userId` (int) – ID пользователя из Passport
- `occurredAt` (DateTimeImmutable)

**Обработчики**:
- `CreatePersonalProjectOnUserFirstLogin` (модуль Projects) – создаёт личный проект.

## Форвардимые события из модулей

### Модуль Projects
- `ProjectCreatedEvent`
- `ProjectMemberAddedEvent`
- `ProjectMemberRemovedEvent`
- `BoardCreatedEvent`
- `InvitationCreatedEvent`
- `InvitationAcceptedEvent`
- `InvitationCancelledEvent`

### Модуль Tasks
- `TaskCreatedEvent`
- `TaskAssignedEvent`
- `TaskMovedToColumnEvent`
- `TaskUpdatedEvent`
- `TaskSoftDeletedEvent`
- `TaskRestoredEvent`
- `CommentAddedEvent`
- `CommentUpdatedEvent`
- `StickerAttachedToTaskEvent`
- `TimerStartedEvent`
- `TimerStoppedEvent`
- `IntervalLoggedEvent`

## Как подписаться на событие

В конфигурации DI (например, `config/container.php`):

```php
$globalDispatcher = Yii::$container->get(GlobalEventDispatcher::class);
$globalDispatcher->addListener(YourEvent::class, YourListener::class);
```

Где `YourListener` должен иметь метод `handle($event)`.

## Планируемые события
- `FileUploadedEvent`
- `AnalyticsEvent`