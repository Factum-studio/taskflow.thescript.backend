# Конфигурация модуля tasks

> Документ описывает DI-регистрацию, маршруты и карту событий модуля tasks.

## 1. `config/di.php`

### Репозитории

| Порт | Реализация |
|---|---|
| `ITaskRepository` | `DbTaskRepository(Yii::$app->db)` |
| `IBoardColumnRepository` | `DbBoardColumnRepository` |
| `ITaskPriorityRepository` | `DbTaskPriorityRepository` |
| `ICommentRepository` | `DbCommentRepository` |
| `IStickerRepository` | `DbStickerRepository` |
| `ITaskStickerRepository` | `DbTaskStickerRepository` |
| `ITimeIntervalRepository` | `DbTimeIntervalRepository` |
| `IDailySummaryRepository` | `DbDailySummaryRepository` |

### Доступ и сервисы

| Сервис | Реализация |
|---|---|
| `ITaskAccess` | `TaskAccess` |
| `ITaskStatisticsService` (порт ядра) | `TaskStatisticsService` |

### Слушатели

`TaskLoggerListener`, `TaskNotificationListener`, `TimeTrackingListener`, `AutoTimerListener`, `PlannedIntervalListener` — все регистрируются как классы.

### `ModuleEventDispatcher` (карта событий)

```php
$listeners = [
    TaskCreatedEvent::class => [
        [$logger, 'handleTaskCreated'],
        [$plannedInterval, 'handleTaskCreated'],
    ],
    TaskMovedToColumnEvent::class => [
        [$logger, 'handleTaskMovedToColumn'],
        [$notifier, 'handleTaskMovedToColumn'],
        [$autoTimer, 'handleTaskMovedToColumn'],
    ],
    TaskAssignedEvent::class => [
        [$logger, 'handleTaskAssigned'],
        [$notifier, 'handleTaskAssigned'],
        [$autoTimer, 'handleTaskAssigned'],
    ],
    TaskRestoredEvent::class => [[$logger, 'handleTaskRestored']],
    TaskSoftDeletedEvent::class => [[$logger, 'handleTaskSoftDeleted'], [$autoTimer, 'handleTaskSoftDeleted']],
    TaskUpdatedEvent::class => [[$logger, 'handleTaskUpdated'], [$plannedInterval, 'handleTaskUpdated']],
    CommentAddedEvent::class => [[$logger, 'handleCommentAdded']],
    CommentUpdatedEvent::class => [[$logger, 'handleCommentUpdated']],
    StickerAttachedToTaskEvent::class => [[$logger, 'handleStickerAttached']],
    TimerStoppedEvent::class => [[$timeTracker, 'handleTimerStopped'], [$logger, 'handleTimerStopped']],
    TimerStartedEvent::class => [[$logger, 'handleTimerStarted']],
    IntervalLoggedEvent::class => [[$logger, 'handleIntervalLogged']],
];
```

### Forward-события (в глобальную шину)

```php
$forwardEvents = [
    TaskCreatedEvent::class, TaskAssignedEvent::class, TaskMovedToColumnEvent::class,
    TaskUpdatedEvent::class, TaskSoftDeletedEvent::class, TaskRestoredEvent::class,
    CommentAddedEvent::class, CommentUpdatedEvent::class, StickerAttachedToTaskEvent::class,
    TimerStartedEvent::class, TimerStoppedEvent::class, IntervalLoggedEvent::class,
];
```

`DispatchingEventDecorator(ModuleEventDispatcher, GlobalEventDispatcher, $forwardEvents)`.

## 2. `config/routing.php`

```php
return [
    // Задачи
    'GET task'                               => 'tasks/task/index',
    'GET task/<id:\d+>'                      => 'tasks/task/view',
    'POST task'                              => 'tasks/task/create',
    'PUT task/<id:\d+>'                      => 'tasks/task/update',
    'DELETE task/<id:\d+>'                   => 'tasks/task/delete',
    'POST task/<id:\d+>/move-to-column'      => 'tasks/task/move-to-column',
    'POST task/<id:\d+>/assign'              => 'tasks/task/assign',
    'POST task/<id:\d+>/restore'             => 'tasks/task/restore',
    'DELETE task/<id:\d+>/hard'              => 'tasks/task/hard-delete',

    // Приоритеты
    'GET task-priority'                      => 'tasks/priority/index',

    // Комментарии
    'GET task/<taskId:\d+>/comment'          => 'tasks/comment/index',
    'POST task/<taskId:\d+>/comment'         => 'tasks/comment/create',
    'GET comment/<id:\d+>'                   => 'tasks/comment/view',
    'PUT comment/<id:\d+>'                   => 'tasks/comment/update',
    'DELETE comment/<id:\d+>'                => 'tasks/comment/delete',

    // Стикеры
    'GET sticker'                            => 'tasks/sticker/index',
    'POST sticker'                           => 'tasks/sticker/create',
    'GET sticker/<id:\d+>'                   => 'tasks/sticker/view',
    'PUT sticker/<id:\d+>'                   => 'tasks/sticker/update',
    'DELETE sticker/<id:\d+>'                => 'tasks/sticker/delete',

    // Стикеры задачи
    'GET task/<taskId:\d+>/sticker'          => 'tasks/task-sticker/index',
    'POST task/<taskId:\d+>/sticker'         => 'tasks/task-sticker/attach',
    'DELETE task/<taskId:\d+>/sticker/<stickerId:\d+>' => 'tasks/task-sticker/detach',

    // Тайм-трекинг
    'GET time-interval'                      => 'tasks/time-interval/index',
    'POST time-interval/start'               => 'tasks/time-interval/start',
    'POST time-interval/stop'                => 'tasks/time-interval/stop',
    'POST time-interval'                     => 'tasks/time-interval/create',
    'GET time-interval/daily-summary'        => 'tasks/time-interval/daily-summary',
    'GET task/<taskId:\d+>/time-summary'     => 'tasks/time-interval/task-summary',

    // Колонки досок
    'GET board/<boardId:\d+>/column'         => 'tasks/board-column/index',
    'POST board/<boardId:\d+>/column'        => 'tasks/board-column/create',
    'GET board/column/<id:\d+>'              => 'tasks/board-column/view',
    'PUT board/column/<id:\d+>'              => 'tasks/board-column/update',
    'DELETE board/column/<id:\d+>'           => 'tasks/board-column/delete',
    'POST board/<boardId:\d+>/column/reorder'=> 'tasks/board-column/reorder',
];
```

## 3. Контроллеры

| Контроллер | Сущность |
|---|---|
| `TaskController` | задачи |
| `PriorityController` | приоритеты |
| `CommentController` | комментарии |
| `StickerController` | стикеры (глобальные) |
| `TaskStickerController` | привязка стикеров к задачам |
| `TimeIntervalController` | тайм-трекинг |
| `BoardColumnController` | колонки досок |

Все наследуют `BaseController`.

## 4. Что задаётся в коде (не конфиг)

| Что | Где |
|---|---|
| Активные статусы для автотаймера (комментарий `['in_progress','review','testing','blocked']`) | AutoTimerListener — фактически решение по `isActive` колонки |
| `TimeInterval::TYPE_TIMER = 'timer'`, `TYPE_PLAN = 'plan'` | сущность TimeInterval |
| Поведение BusyChartService (granularity/mode) | BusyChartService |
| Лимиты колонок `<50` символов, label `<255` | BoardColumn |