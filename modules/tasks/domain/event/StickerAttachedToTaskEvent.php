<?php

namespace modules\tasks\domain\event;

use DateTimeImmutable;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\TaskId;

class StickerAttachedToTaskEvent implements ITaskDomainEvent
{
    private int $taskId;
    private int $stickerId;
    private int $attachedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(TaskId $taskId, StickerId $stickerId, int $attachedBy)
    {
        $this->taskId       = $taskId->getValue();
        $this->stickerId    = $stickerId->getValue();
        $this->attachedBy   = $attachedBy;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getStickerId(): int
    {
        return $this->stickerId;
    }

    public function getAttachedBy(): int
    {
        return $this->attachedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}