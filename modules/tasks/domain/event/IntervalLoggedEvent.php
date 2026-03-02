<?php

namespace modules\tasks\domain\event;

use DateTimeImmutable;
use modules\tasks\domain\entity\TimeInterval;

class IntervalLoggedEvent implements ITaskDomainEvent
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

    /**
     * @return int
     */
    public function getIntervalId(): int
    {
        return $this->intervalId;
    }

    /**
     * @return int
     */
    public function getTaskId(): int
    {
        return $this->taskId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStartedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStoppedAt(): DateTimeImmutable
    {
        return $this->stoppedAt;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}