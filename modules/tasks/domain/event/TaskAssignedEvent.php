<?php

namespace modules\tasks\domain\event;

use core\domain\valueObject\UserId;
use modules\tasks\domain\entity\Task;
use DateTimeImmutable;

class TaskAssignedEvent implements ITaskDomainEvent
{
    private int $taskId;
    private ?int $oldAssigneeId;
    private ?int $newAssigneeId;
    private int $assignedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Task $task, ?UserId $oldAssignee, int $assignedBy)
    {
        $this->taskId           = $task->getId()->value();
        $this->oldAssigneeId    = $oldAssignee?->value();
        $this->newAssigneeId    = $task->getAssignedTo()?->value();
        $this->assignedBy       = $assignedBy;
        $this->occurredAt       = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getOldAssigneeId(): ?int
    {
        return $this->oldAssigneeId;
    }

    public function getNewAssigneeId(): ?int
    {
        return $this->newAssigneeId;
    }

    public function getAssignedBy(): int
    {
        return $this->assignedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}