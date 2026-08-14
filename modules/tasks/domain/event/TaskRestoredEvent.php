<?php

namespace modules\tasks\domain\event;

use modules\tasks\domain\entity\Task;
use DateTimeImmutable;

class TaskRestoredEvent implements ITaskDomainEvent
{
    private int $taskId;
    private int $restoredBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Task $task, int $restoredBy)
    {
        $this->taskId       = $task->getId()->getValue();
        $this->restoredBy   = $restoredBy;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getRestoredBy(): int
    {
        return $this->restoredBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}