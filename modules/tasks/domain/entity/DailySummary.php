<?php

namespace modules\tasks\domain\entity;

use DateTimeImmutable;
use modules\tasks\domain\valueObject\DailySummaryId;
use modules\tasks\domain\valueObject\Date;
use modules\tasks\domain\valueObject\Duration;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;

class DailySummary
{
    private DailySummaryId $id;
    private TaskId $taskId;
    private UserId $userId;
    private Date $date;
    private Duration $totalDuration;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        DailySummaryId $id,
        TaskId $taskId,
        UserId $userId,
        Date $date,
        Duration $totalDuration,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id               = $id;
        $this->taskId           = $taskId;
        $this->userId           = $userId;
        $this->date             = $date;
        $this->totalDuration    = $totalDuration;
        $this->updatedAt        = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): DailySummaryId { return $this->id; }
    public function getTaskId(): TaskId { return $this->taskId; }
    public function getUserId(): UserId { return $this->userId; }
    public function getDate(): Date { return $this->date; }
    public function getTotalDuration(): Duration { return $this->totalDuration; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function addTime(Duration $additional): void
    {
        $newSeconds = $this->totalDuration->getSeconds() + $additional->getSeconds();
        $this->totalDuration = new Duration($newSeconds);
        $this->touch();
    }

    public function setTotalDuration(Duration $duration): void
    {
        $this->totalDuration = $duration;
        $this->touch();
    }

    /**
     * @internal Используется только в репозитории, для того, чтобы установить ID после создания
     */
    public function setId(DailySummaryId $id): void
    {
        $this->id = $id;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}