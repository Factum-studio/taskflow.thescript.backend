<?php

namespace modules\tasks\domain\event;

use DateTimeImmutable;
use modules\tasks\domain\entity\TimeInterval;

class TimerStoppedEvent implements ITaskDomainEvent
{
    private int $intervalId;
    private int $taskId;
    private int $userId;
    private DateTimeImmutable $startedAt;
    private DateTimeImmutable $stoppedAt;
    private int $duration;
    private ?string $comment;
    private DateTimeImmutable $occurredAt;

    public function __construct(TimeInterval $interval)
    {
        if (!$interval->isStopped()) {
            throw new \InvalidArgumentException('Cannot create event for non-stopped interval');
        }
        $this->intervalId   = $interval->getId()->getValue();
        $this->taskId       = $interval->getTaskId()->getValue();
        $this->userId       = $interval->getUserId()->getValue();
        $this->startedAt    = $interval->getStartTime();
        $this->stoppedAt    = $interval->getEndTime();
        $this->duration     = $interval->getDurationSeconds();
        $this->comment      = $interval->getComment();
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

    public function getStoppedAt(): DateTimeImmutable
    {
        return $this->stoppedAt;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}