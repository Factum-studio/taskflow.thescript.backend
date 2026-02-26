<?php

use modules\tasks\domain\event\TaskRestoredEvent;
use modules\tasks\domain\event\TaskSoftDeletedEvent;
use modules\tasks\domain\event\TaskUpdatedEvent;
use modules\tasks\domain\repository\ITaskPriorityRepository;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\repository\ITaskStatusRepository;
use modules\tasks\infrastructure\repository\DbTaskPriorityRepository;
use modules\tasks\infrastructure\repository\DbTaskRepository;
use modules\tasks\infrastructure\event\ModuleEventDispatcher;
use modules\tasks\infrastructure\listener\TaskLoggerListener;
use modules\tasks\infrastructure\listener\TaskNotificationListener;
use modules\tasks\domain\event\TaskCreatedEvent;
use modules\tasks\domain\event\TaskStatusChangedEvent;
use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\infrastructure\repository\DbTaskStatusRepository;
use yii\di\Container;

Yii::$container->set(ITaskRepository::class, DbTaskRepository::class);
Yii::$container->set(ITaskStatusRepository::class, function () {
    return new DbTaskStatusRepository(Yii::$app->db);
});
Yii::$container->set(ITaskPriorityRepository::class, function () {
    return new DbTaskPriorityRepository(Yii::$app->db);
});

Yii::$container->set(TaskLoggerListener::class);
Yii::$container->set(TaskNotificationListener::class);

Yii::$container->set(IEventDispatcher::class, function (Container $container) {
    /** @var TaskLoggerListener $logger */
    $logger = $container->get(TaskLoggerListener::class);
    /** @var TaskNotificationListener $notifier */
    $notifier = $container->get(TaskNotificationListener::class);

    $listeners = [
        TaskCreatedEvent::class => [
            [$logger, 'handleTaskCreated'],
        ],
        TaskStatusChangedEvent::class => [
            [$logger, 'handleTaskStatusChanged'],
            [$notifier, 'handleTaskStatusChanged'],
        ],
        TaskAssignedEvent::class => [
            [$logger, 'handleTaskAssigned'],
            [$notifier, 'handleTaskAssigned'],
        ],
        TaskRestoredEvent::class => [
            [$logger, 'handleTaskRestored'],
        ],
        TaskSoftDeletedEvent::class => [
            [$logger, 'handleTaskSoftDeleted'],
        ],
        TaskUpdatedEvent::class => [
            [$logger, 'handleTaskUpdated'],
        ]
    ];

    return new ModuleEventDispatcher($listeners);
});