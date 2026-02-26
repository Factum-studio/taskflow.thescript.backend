<?php

namespace modules\tasks\domain\event;

use modules\tasks\domain\entity\Task;
use modules\tasks\domain\valueObject\UserId;
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
        $this->taskId           = $task->getId()->getValue();
        $this->oldAssigneeId    = $oldAssignee?->getValue();
        $this->newAssigneeId    = $task->getAssignedTo()?->getValue();
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