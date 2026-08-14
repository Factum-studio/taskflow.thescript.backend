<?php

namespace modules\tasks\application\query;

use DateTimeImmutable;

class ListTimeIntervalsQuery
{
    public ?int $taskId;
    public ?int $userId;
    public ?DateTimeImmutable $from;
    public ?DateTimeImmutable $to;
    public ?bool $activeOnly; // только незавершённые

    public function __construct(
        ?int $taskId = null,
        ?int $userId = null,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
        ?bool $activeOnly = false
    ) {
        $this->taskId       = $taskId;
        $this->userId       = $userId;
        $this->from         = $from;
        $this->to           = $to;
        $this->activeOnly   = $activeOnly;
    }
}