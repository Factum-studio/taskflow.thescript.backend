<?php

namespace modules\tasks\domain\event;

use modules\tasks\domain\entity\Task;
use DateTimeImmutable;

class TaskCreatedEvent implements ITaskDomainEvent
{
    private int $taskId;
    private int $createdBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Task $task)
    {
        $this->taskId       = $task->getId()->getValue();
        $this->createdBy    = $task->getCreatedBy()->getValue();
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}