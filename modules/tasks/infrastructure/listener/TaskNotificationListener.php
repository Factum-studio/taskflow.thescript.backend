<?php

namespace modules\tasks\infrastructure\listener;

use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\domain\event\TaskStatusChangedEvent;

class TaskNotificationListener
{
    public function handleTaskAssigned(TaskAssignedEvent $event): void
    {

    }

    public function handleTaskStatusChanged(TaskStatusChangedEvent $event): void
    {

    }
}