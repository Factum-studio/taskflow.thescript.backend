<?php

namespace modules\tasks\infrastructure\listener;

use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\domain\event\TaskMovedToColumnEvent;

class TaskNotificationListener
{
    public function handleTaskAssigned(TaskAssignedEvent $event): void
    {
        // TODO: implement
    }

    public function handleTaskMovedToColumn(TaskMovedToColumnEvent $event): void
    {
        // TODO: implement
    }
}