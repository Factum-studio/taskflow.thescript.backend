<?php

namespace modules\tasks\application\command;

use DateTimeImmutable;

class StartTimerCommand
{
    public int $taskId;
    public int $userId;
    public ?DateTimeImmutable $startTime;
    public ?string $comment;

    public function __construct(
        int $taskId,
        int $userId,
        ?DateTimeImmutable $startTime = null,
        ?string $comment = null
    ) {
        $this->taskId       = $taskId;
        $this->userId       = $userId;
        $this->startTime    = $startTime;
        $this->comment      = $comment;
    }
}