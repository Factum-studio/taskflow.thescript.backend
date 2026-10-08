# Сущности, события и листенеры модуля tasks

> Документ описывает доменные события и реакцию на них (листенеры), а также карту подписок из DI.

## 1. События модуля

Все события — в `modules/tasks/domain/event/`:

| Событие | Когда | Ключевые данные |
|---|---|---|
| `TaskCreatedEvent` | Создана задача | aggregateId (taskId) |
| `TaskUpdatedEvent` | Обновлена задача | aggregateId, changedFields |
| `TaskAssignedEvent` | Назначен исполнитель | aggregateId, oldAssigneeId, newAssigneeId |
| `TaskMovedToColumnEvent` | Задача перенесена в колонку | aggregateId, oldColumnId, newColumnId |
| `TaskSoftDeletedEvent` | Мягкое удаление | aggregateId |
| `TaskRestoredEvent` | Восстановление | aggregateId |
| `CommentAddedEvent` | Добавлен комментарий | taskId, commentId |
| `CommentUpdatedEvent` | Обновлён комментарий | taskId, commentId |
| `StickerAttachedToTaskEvent` | Стикер привязан к задаче | taskId, stickerId |
| `TimerStartedEvent` | Таймер запущен | taskId, userId |
| `TimerStoppedEvent` | Таймер остановлен | taskId, userId, duration, stoppedAt |
| `IntervalLoggedEvent` | Ручной интервал добавлен | taskId, userId, intervalId |

## 2. Листенеры

### 2.1. `AutoTimerListener` — автотаймер
- `handleTaskMovedToColumn(TaskMovedToColumnEvent)`:
  - **финальная колонка** (`isFinal`) → остановить активный таймер задачи (`stop(..., 'Task completed (final column)')`);
  - переход **неактивная → активная** (по `isActive` колонок) → создать таймер `auto-started`;
  - переход **активная → неактивная** → удалить (не остановить!) активный таймер.
- `handleTaskAssigned(TaskAssignedEvent)`:
  - при смене исполнителя — **перенести** активный таймер на нового исполнителя.
- `handleTaskSoftDeleted(TaskSoftDeletedEvent)`:
  - остановить активный таймер (`'Task deleted'`).

> Активные системные статусы (по имени `isActive` колонки): в коде захардкожен массив `['in_progress', 'review', 'testing', 'blocked']` как комментарий; фактически решение принимается по флагу `isActive` колонки.

### 2.2. `PlannedIntervalListener` — плановые интервалы
- `handleTaskCreated(TaskCreatedEvent)` → создать planned-интервал из `plannedStart`/`plannedEnd`.
- `handleTaskUpdated(TaskUpdatedEvent)` → если изменились `plannedStart`/`plannedEnd`/`assignedTo` — синхронизировать planned-интервал (создать/обновить/удалить).
- Planned-интервалы — тип `plan` в `time_intervals`.

### 2.3. `TimeTrackingListener` — дневная сводка
- `handleTimerStopped(TimerStoppedEvent)`:
  - `findOrCreate(DailySummary)` по (taskId, userId, date);
  - `addTime(duration)`; сохранить.

### 2.4. `TaskLoggerListener` — логирование
- Слушает большинство событий; пишет логи в категорию `tasks` (`Yii::info`).

### 2.5. `TaskNotificationListener` — уведомления
- Заготовка: `handleTaskAssigned`, `handleTaskMovedToColumn` — **TODO: not implemented** (см. [known-issues-and-roadmap](../../known-issues-and-roadmap.md)).

## 3. Карта подписок (из `modules/tasks/config/di.php`)

| Событие | Слушатели |
|---|---|
| `TaskCreatedEvent` | TaskLoggerListener::handleTaskCreated, PlannedIntervalListener::handleTaskCreated |
| `TaskMovedToColumnEvent` | TaskLoggerListener, TaskNotificationListener, AutoTimerListener |
| `TaskAssignedEvent` | TaskLoggerListener, TaskNotificationListener, AutoTimerListener |
| `TaskRestoredEvent` | TaskLoggerListener |
| `TaskSoftDeletedEvent` | TaskLoggerListener, AutoTimerListener |
| `TaskUpdatedEvent` | TaskLoggerListener, PlannedIntervalListener |
| `CommentAddedEvent` | TaskLoggerListener |
| `CommentUpdatedEvent` | TaskLoggerListener |
| `StickerAttachedToTaskEvent` | TaskLoggerListener |
| `TimerStoppedEvent` | TimeTrackingListener, TaskLoggerListener |
| `TimerStartedEvent` | TaskLoggerListener |
| `IntervalLoggedEvent` | TaskLoggerListener |

## 4. Forward-события (в глобальную шину)

Через `DispatchingEventDecorator` в глобальный `IEventDispatcher` ядра пробрасываются:

```
TaskCreatedEvent, TaskAssignedEvent, TaskMovedToColumnEvent, TaskUpdatedEvent,
TaskSoftDeletedEvent, TaskRestoredEvent, CommentAddedEvent, CommentUpdatedEvent,
StickerAttachedToTaskEvent, TimerStartedEvent, TimerStoppedEvent, IntervalLoggedEvent
```

## 5. Миграции модуля

| Миграция | Таблица |
|---|---|
| `m260224_132704_create_task_statuses_table.php` | `task_statuses` |
| `m260224_132706_create_task_priorities_table.php` | `task_priorities` |
| `m260224_132707_create_tasks_table.php` | `tasks` |
| `m260224_132708_create_task_comments_table.php` | `task_comments` |
| `m260224_132712_create_task_time_intervals_table.php` | `task_time_intervals` |
| `m260224_132716_create_task_daily_time_summary_table.php` | `task_daily_time_summary` |
| `m260226_164536_create_stickers_table.php` | `stickers` |
| `m260226_164850_create_task_sticker_map_table.php` | `task_sticker_map` |
| `m260316_152523_add_planned_fields_to_tasks.php` | `tasks` (+planned_start, planned_end) |
| `m260316_152704_add_type_to_task_time_intervals.php` | `task_time_intervals` (+type) |
| `m260317_095225_create_board_columns_table.php` | `board_columns` |
| `m260317_095250_alter_tasks_add_column_id.php` | `tasks` (+column_id) |
| `m260409_133019_add_complete_fields_to_tasks.php` | `tasks` (+complete) |