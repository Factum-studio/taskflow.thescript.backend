<?php

namespace modules\tasks\domain\event;

use modules\tasks\domain\entity\Task;
use DateTimeImmutable;

class TaskUpdatedEvent implements ITaskDomainEvent
{
    private int $taskId;
    private array $changedFields;
    private int $updatedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Task $task, array $changedFields, int $updatedBy)
    {
        $this->taskId           = $task->getId()->getValue();
        $this->changedFields    = $changedFields;
        $this->updatedBy        = $updatedBy;
        $this->occurredAt       = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getChangedFields(): array
    {
        return $this->changedFields;
    }

    public function getUpdatedBy(): int
    {
        return $this->updatedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}