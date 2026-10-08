<?php

namespace modules\tasks\domain\event;

use modules\tasks\domain\entity\Task;
use modules\tasks\domain\valueObject\ColumnId;
use DateTimeImmutable;

class TaskMovedToColumnEvent implements ITaskDomainEvent
{
    private int $taskId;
    private int $oldColumnId;
    private int $newColumnId;
    private ?int $movedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Task $task, ColumnId $oldColumn, ?int $movedBy = null)
    {
        $this->taskId       = $task->getId()->value();
        $this->oldColumnId  = $oldColumn->value();
        $this->newColumnId  = $task->getColumnId()->value();
        $this->movedBy      = $movedBy;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getOldColumnId(): int
    {
        return $this->oldColumnId;
    }

    public function getNewColumnId(): int
    {
        return $this->newColumnId;
    }

    public function getMovedBy(): ?int
    {
        return $this->movedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}