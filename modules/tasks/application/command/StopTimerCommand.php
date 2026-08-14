<?php

namespace modules\tasks\application\command;

use DateTimeImmutable;

class StopTimerCommand
{
    public int $intervalId;
    public int $userId;
    public ?DateTimeImmutable $stopTime;
    public ?string $comment;

    public function __construct(
        int $intervalId,
        int $userId,
        ?DateTimeImmutable $stopTime = null,
        ?string $comment = null
    ) {
        $this->intervalId = $intervalId;
        $this->userId = $userId;
        $this->stopTime = $stopTime;
        $this->comment = $comment;
    }
}