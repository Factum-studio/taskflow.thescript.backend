<?php

namespace modules\tasks\domain\event;

use DateTimeImmutable;
use modules\tasks\domain\entity\Task;

class TaskSoftDeletedEvent implements ITaskDomainEvent
{
    private int $taskId;
    private int $deletedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Task $task, int $deletedBy)
    {
        $this->taskId       = $task->getId()->getValue();
        $this->deletedBy    = $deletedBy;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getDeletedBy(): int
    {
        return $this->deletedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}