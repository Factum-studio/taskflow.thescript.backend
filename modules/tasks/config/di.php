<?php

use core\application\port\IEventDispatcher as GlobalEventDispatcher;
use modules\tasks\domain\event\IEventDispatcher as TaskEventDispatcher;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\event\CommentAddedEvent;
use modules\tasks\domain\event\CommentUpdatedEvent;
use modules\tasks\domain\event\IntervalLoggedEvent;
use modules\tasks\domain\event\StickerAttachedToTaskEvent;
use modules\tasks\domain\event\TaskMovedToColumnEvent;
use modules\tasks\domain\event\TaskRestoredEvent;
use modules\tasks\domain\event\TaskSoftDeletedEvent;
use modules\tasks\domain\event\TaskUpdatedEvent;
use modules\tasks\domain\event\TimerStartedEvent;
use modules\tasks\domain\event\TimerStoppedEvent;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\repository\IDailySummaryRepository;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\repository\ITaskPriorityRepository;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStickerRepository;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\infrastructure\access\TaskAccess;
use modules\tasks\infrastructure\event\DispatchingEventDecorator;
use modules\tasks\infrastructure\listener\AutoTimerListener;
use modules\tasks\infrastructure\listener\PlannedIntervalListener;
use modules\tasks\infrastructure\listener\TimeTrackingListener;
use modules\tasks\infrastructure\repository\DbBoardColumnRepository;
use modules\tasks\infrastructure\repository\DbCommentRepository;
use modules\tasks\infrastructure\repository\DbDailySummaryRepository;
use modules\tasks\infrastructure\repository\DbStickerRepository;
use modules\tasks\infrastructure\repository\DbTaskPriorityRepository;
use modules\tasks\infrastructure\repository\DbTaskRepository;
use modules\tasks\infrastructure\event\ModuleEventDispatcher;
use modules\tasks\infrastructure\listener\TaskLoggerListener;
use modules\tasks\infrastructure\listener\TaskNotificationListener;
use modules\tasks\domain\event\TaskCreatedEvent;
use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\infrastructure\repository\DbTaskStickerRepository;
use modules\tasks\infrastructure\repository\DbTimeIntervalRepository;
use yii\di\Container;

Yii::$container->set(ITaskRepository::class, function () {
    return new DbTaskRepository(Yii::$app->db);
});
Yii::$container->set(IBoardColumnRepository::class, function () {
    return new DbBoardColumnRepository(Yii::$app->db);
});
Yii::$container->set(ITaskPriorityRepository::class, function () {
    return new DbTaskPriorityRepository(Yii::$app->db);
});
Yii::$container->set(ICommentRepository::class, function() {
    return new DbCommentRepository(Yii::$app->db);
});
Yii::$container->set(IStickerRepository::class, function() {
    return new DbStickerRepository(Yii::$app->db);
});
Yii::$container->set(ITaskStickerRepository::class, function() {
    return new DbTaskStickerRepository(Yii::$app->db);
});
Yii::$container->set(ITimeIntervalRepository::class, function() {
    return new DbTimeIntervalRepository(Yii::$app->db);
});
Yii::$container->set(IDailySummaryRepository::class, function() {
    return new DbDailySummaryRepository(Yii::$app->db);
});

Yii::$container->set(
    ITaskAccess::class,
    TaskAccess::class
);

Yii::$container->set(TaskLoggerListener::class);
Yii::$container->set(TaskNotificationListener::class);
Yii::$container->set(TimeTrackingListener::class);
Yii::$container->set(AutoTimerListener::class);
Yii::$container->set(PlannedIntervalListener::class);

Yii::$container->set(ModuleEventDispatcher::class, function (Container $container) {
    /** @var TaskLoggerListener $logger */
    $logger = $container->get(TaskLoggerListener::class);
    /** @var TaskNotificationListener $notifier */
    $notifier = $container->get(TaskNotificationListener::class);
    /** @var TimeTrackingListener $timeTracker */
    $timeTracker = $container->get(TimeTrackingListener::class);
    /** @var AutoTimerListener $autoTimer */
    $autoTimer = $container->get(AutoTimerListener::class);
    /** @var PlannedIntervalListener $plannedInterval */
    $plannedInterval = $container->get(PlannedIntervalListener::class);

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
        TaskRestoredEvent::class => [
            [$logger, 'handleTaskRestored'],
        ],
        TaskSoftDeletedEvent::class => [
            [$logger, 'handleTaskSoftDeleted'],
            [$autoTimer, 'handleTaskSoftDeleted'],
        ],
        TaskUpdatedEvent::class => [
            [$logger, 'handleTaskUpdated'],
            [$plannedInterval, 'handleTaskUpdated'],
        ],
        CommentAddedEvent::class => [
            [$logger, 'handleCommentAdded'],
        ],
        CommentUpdatedEvent::class => [
            [$logger, 'handleCommentUpdated'],
        ],
        StickerAttachedToTaskEvent::class => [
            [$logger, 'handleStickerAttached'],
        ],
        TimerStoppedEvent::class => [
            [$timeTracker, 'handleTimerStopped'],
            [$logger, 'handleTimerStopped'],
        ],
        TimerStartedEvent::class => [
            [$logger, 'handleTimerStarted'],
        ],
        IntervalLoggedEvent::class => [
            [$logger, 'handleIntervalLogged'],
        ],
    ];

    return new ModuleEventDispatcher($listeners);
});

$forwardEvents = [
    TaskCreatedEvent::class,
    TaskAssignedEvent::class,
    TaskMovedToColumnEvent::class,
    TaskUpdatedEvent::class,
    TaskSoftDeletedEvent::class,
    TaskRestoredEvent::class,
    CommentAddedEvent::class,
    CommentUpdatedEvent::class,
    StickerAttachedToTaskEvent::class,
    TimerStartedEvent::class,
    TimerStoppedEvent::class,
    IntervalLoggedEvent::class,
];

Yii::$container->set(TaskEventDispatcher::class, function (Container $container) use ($forwardEvents) {
    return new DispatchingEventDecorator(
        $container->get(ModuleEventDispatcher::class),
        $container->get(GlobalEventDispatcher::class),
        $forwardEvents
    );
});