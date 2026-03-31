# События модуля Tasks

Модуль генерирует множество событий, часть из которых пробрасывается в глобальную шину.

## Локальные события (все)

| Событие                      | Когда возникает            | Данные                                                   |
|------------------------------|----------------------------|----------------------------------------------------------|
| `TaskCreatedEvent`           | Создана задача             | `taskId`, `createdBy`                                    |
| `TaskUpdatedEvent`           | Обновлена задача           | `taskId`, `changedFields`, `updatedBy`                   |
| `TaskMovedToColumnEvent`     | Перемещена между колонками | `taskId`, `oldColumnId`, `newColumnId`, `movedBy`        |
| `TaskAssignedEvent`          | Назначен исполнитель       | `taskId`, `oldAssigneeId`, `newAssigneeId`, `assignedBy` |
| `TaskSoftDeletedEvent`       | Мягкое удаление            | `taskId`, `deletedBy`                                    |
| `TaskRestoredEvent`          | Восстановление             | `taskId`, `restoredBy`                                   |
| `CommentAddedEvent`          | Добавлен комментарий       | `commentId`, `taskId`, `userId`                          |
| `CommentUpdatedEvent`        | Обновлён комментарий       | `commentId`, `taskId`, `userId`                          |
| `StickerAttachedToTaskEvent` | Прикреплён стикер          | `taskId`, `stickerId`, `attachedBy`                      |
| `TimerStartedEvent`          | Запущен таймер             | `intervalId`, `taskId`, `userId`                         |
| `TimerStoppedEvent`          | Остановлен таймер          | `intervalId`, `taskId`, `userId`, `duration`             |
| `IntervalLoggedEvent`        | Добавлен ручной интервал   | `intervalId`, `taskId`, `userId`, `duration`             |

## Форвардинг в глобальную шину

В `modules/tasks/config/di.php`:

```
$forwardEvents = [
    TaskCreatedEvent::class,
    TaskAssignedEvent::class,
];
```

Эти события доступны для подписки из других модулей (например, для аналитики или уведомлений).

## Обработчики (внутри модуля)

- `TaskLoggerListener` – логирует все события в Yii::info/error.
- `TaskNotificationListener` – заглушка для уведомлений (TODO).
- `TimeTrackingListener` – обновляет ежедневные сводки (`DailySummary`) при остановке таймера.
- `AutoTimerListener` – автоматически запускает/останавливает таймер при перемещении задачи или смене исполнителя.
- `PlannedIntervalListener` – синхронизирует плановые интервалы с таблицей `task_time_intervals`.