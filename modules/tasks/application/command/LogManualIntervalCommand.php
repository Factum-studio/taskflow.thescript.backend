<?php

namespace modules\tasks\application\command;

use DateTimeImmutable;

class LogManualIntervalCommand
{
    public int $taskId;
    public int $userId;
    public DateTimeImmutable $startTime;
    public DateTimeImmutable $endTime;
    public ?string $comment;

    public function __construct(
        int $taskId,
        int $userId,
        DateTimeImmutable $startTime,
        DateTimeImmutable $endTime,
        ?string $comment = null
    ) {
        $this->taskId       = $taskId;
        $this->userId       = $userId;
        $this->startTime    = $startTime;
        $this->endTime      = $endTime;
        $this->comment      = $comment;
    }
}