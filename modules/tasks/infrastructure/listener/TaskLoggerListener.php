<?php

namespace modules\tasks\infrastructure\listener;

use modules\tasks\domain\event\TaskCreatedEvent;
use modules\tasks\domain\event\TaskRestoredEvent;
use modules\tasks\domain\event\TaskSoftDeletedEvent;
use modules\tasks\domain\event\TaskStatusChangedEvent;
use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\domain\event\TaskUpdatedEvent;
use Yii;

class TaskLoggerListener
{
    public function handleTaskCreated(TaskCreatedEvent $event): void
    {
        Yii::info("Task created: ID {$event->getAggregateId()} by user {$event->getCreatedBy()}", 'tasks');
    }

    public function handleTaskStatusChanged(TaskStatusChangedEvent $event): void
    {
        Yii::info("Task {$event->getAggregateId()} status changed from {$event->getOldStatusId()} to {$event->getNewStatusId()}", 'tasks');
    }

    public function handleTaskAssigned(TaskAssignedEvent $event): void
    {
        Yii::info("Task {$event->getAggregateId()} assigned from {$event->getOldAssigneeId()} to {$event->getNewAssigneeId()} by {$event->getAssignedBy()}", 'tasks');
    }

    public function handleTaskRestored(TaskRestoredEvent $event): void
    {
        Yii::info("Task {$event->getAggregateId()} restored by {$event->getRestoredBy()}", 'tasks');
    }

    public function handleTaskSoftDeleted(TaskSoftDeletedEvent $event): void
    {
        Yii::info("Task {$event->getAggregateId()} deleted by {$event->getDeletedBy()}", 'tasks');
    }

    public function handleTaskUpdated(TaskUpdatedEvent $event): void
    {
        $changedKeys = implode(', ', array_keys($event->getChangedFields()));
        Yii::info("Task {$event->getAggregateId()} updated by {$event->getUpdatedBy()}. Changed fields: {$changedKeys}", 'tasks');
    }
}