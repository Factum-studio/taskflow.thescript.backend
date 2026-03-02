<?php

namespace modules\tasks\domain\entity;

use DateTimeImmutable;
use InvalidArgumentException;
use modules\tasks\domain\valueObject\Duration;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\TimeIntervalId;
use modules\tasks\domain\valueObject\UserId;

class TimeInterval
{
    private TimeIntervalId $id;
    private TaskId $taskId;
    private UserId $userId;
    private DateTimeImmutable $startTime;
    private ?DateTimeImmutable $endTime;
    private ?Duration $duration;
    private ?string $comment;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        TimeIntervalId $id,
        TaskId $taskId,
        UserId $userId,
        DateTimeImmutable $startTime,
        ?DateTimeImmutable $endTime = null,
        ?Duration $duration = null,
        ?string $comment = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        if ($endTime && $startTime > $endTime) {
            throw new InvalidArgumentException('Start time must be before end time');
        }
        $this->id           = $id;
        $this->taskId       = $taskId;
        $this->userId       = $userId;
        $this->startTime    = $startTime;
        $this->endTime      = $endTime;
        $this->duration     = $duration;
        $this->comment      = $comment;
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): TimeIntervalId { return $this->id; }
    public function getTaskId(): TaskId { return $this->taskId; }
    public function getUserId(): UserId { return $this->userId; }
    public function getStartTime(): DateTimeImmutable { return $this->startTime; }
    public function getEndTime(): ?DateTimeImmutable { return $this->endTime; }
    public function getDuration(): ?Duration { return $this->duration; }
    public function getComment(): ?string { return $this->comment; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function stop(DateTimeImmutable $endTime, ?string $comment = null): void
    {
        if ($this->endTime !== null) {
            throw new InvalidArgumentException('Interval already stopped');
        }
        if ($endTime < $this->startTime) {
            throw new InvalidArgumentException('End time cannot be before start time');
        }
        $this->endTime = $endTime;
        $seconds = $endTime->getTimestamp() - $this->startTime->getTimestamp();
        $this->duration = new Duration($seconds);
        if ($comment !== null) {
            $this->comment = $comment;
        }
        $this->touch();
    }

    public function changeComment(?string $comment): void
    {
        $this->comment = $comment;
        $this->touch();
    }

    public function isStopped(): bool
    {
        return $this->endTime !== null;
    }

    public function getDurationSeconds(): ?int
    {
        return $this->duration?->getSeconds();
    }

    /**
     * @internal Используется только в репозитории, для того, чтобы установить ID после создания
     */
    public function setId(TimeIntervalId $id): void
    {
        $this->id = $id;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}