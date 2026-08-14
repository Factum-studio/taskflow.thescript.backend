<?php

namespace modules\tasks\infrastructure\listener;

use modules\tasks\domain\event\CommentAddedEvent;
use modules\tasks\domain\event\CommentUpdatedEvent;
use modules\tasks\domain\event\IntervalLoggedEvent;
use modules\tasks\domain\event\StickerAttachedToTaskEvent;
use modules\tasks\domain\event\TaskCreatedEvent;
use modules\tasks\domain\event\TaskMovedToColumnEvent;
use modules\tasks\domain\event\TaskRestoredEvent;
use modules\tasks\domain\event\TaskSoftDeletedEvent;
use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\domain\event\TaskUpdatedEvent;
use modules\tasks\domain\event\TimerStartedEvent;
use modules\tasks\domain\event\TimerStoppedEvent;
use Yii;

class TaskLoggerListener
{
    public function handleTaskCreated(TaskCreatedEvent $event): void
    {
        Yii::info("Task created: ID {$event->getAggregateId()} by user {$event->getCreatedBy()}", 'tasks');
    }

    public function handleTaskMovedToColumn(TaskMovedToColumnEvent $event): void
    {
        Yii::info("Task {$event->getAggregateId()} moved from column {$event->getOldColumnId()} to {$event->getNewColumnId()}", 'tasks');
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
        Yii::info("Task {$event->getAggregateId()} soft deleted by {$event->getDeletedBy()}", 'tasks');
    }

    public function handleTaskUpdated(TaskUpdatedEvent $event): void
    {
        $changedKeys = implode(', ', array_keys($event->getChangedFields()));
        Yii::info("Task {$event->getAggregateId()} updated by {$event->getUpdatedBy()}. Changed fields: {$changedKeys}", 'tasks');
    }

    public function handleCommentAdded(CommentAddedEvent $event): void
    {
        Yii::info("Comment {$event->getCommentId()} added to task {$event->getAggregateId()} by user {$event->getUserId()}", 'tasks');
    }

    public function handleCommentUpdated(CommentUpdatedEvent $event): void
    {
        Yii::info("Comment {$event->getCommentId()} in task {$event->getAggregateId()} updated by user {$event->getUserId()}", 'tasks');
    }

    public function handleStickerAttached(StickerAttachedToTaskEvent $event): void
    {
        Yii::info("Sticker {$event->getStickerId()} attached to task {$event->getTaskId()} by user {$event->getAttachedBy()}", 'tasks');
    }

    public function handleTimerStarted(TimerStartedEvent $event): void
    {
        Yii::info("Timer started for task {$event->getTaskId()} by user {$event->getUserId()}", 'tasks');
    }

    public function handleTimerStopped(TimerStoppedEvent $event): void
    {
        Yii::info("Timer stopped for task {$event->getTaskId()} by user {$event->getUserId()}, duration {$event->getDuration()}s", 'tasks');
    }

    public function handleIntervalLogged(IntervalLoggedEvent $event): void
    {
        Yii::info("Interval logged for task {$event->getTaskId()} by user {$event->getUserId()}, duration {$event->getDuration()}s", 'tasks');
    }
}