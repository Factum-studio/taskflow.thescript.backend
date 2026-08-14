<?php

namespace modules\tasks\domain\event;

use DateTimeImmutable;
use modules\tasks\domain\entity\TimeInterval;

class TimerStartedEvent implements ITaskDomainEvent
{
    private int $intervalId;
    private int $taskId;
    private int $userId;
    private DateTimeImmutable $startedAt;
    private DateTimeImmutable $occurredAt;

    public function __construct(TimeInterval $interval)
    {
        $this->intervalId   = $interval->getId()->getValue();
        $this->taskId       = $interval->getTaskId()->getValue();
        $this->userId       = $interval->getUserId()->getValue();
        $this->startedAt    = $interval->getStartTime();
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getIntervalId(): int
    {
        return $this->intervalId;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getStartedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}