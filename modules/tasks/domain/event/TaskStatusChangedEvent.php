<?php

namespace modules\tasks\domain\event;

use modules\tasks\domain\entity\Task;
use modules\tasks\domain\valueObject\StatusId;
use DateTimeImmutable;

class TaskStatusChangedEvent implements ITaskDomainEvent
{
    private int $taskId;
    private int $oldStatusId;
    private int $newStatusId;
    private ?int $changedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Task $task, StatusId $oldStatus, ?int $changedBy = null)
    {
        $this->taskId       = $task->getId()->getValue();
        $this->oldStatusId  = $oldStatus->getValue();
        $this->newStatusId  = $task->getStatusId()->getValue();
        $this->changedBy    = $changedBy;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getOldStatusId(): int
    {
        return $this->oldStatusId;
    }

    public function getNewStatusId(): int
    {
        return $this->newStatusId;
    }

    public function getChangedBy(): ?int
    {
        return $this->changedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}