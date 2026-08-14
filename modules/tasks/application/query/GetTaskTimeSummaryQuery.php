<?php

namespace modules\tasks\application\query;

use DateTimeImmutable;

class GetTaskTimeSummaryQuery
{
    public int $taskId;
    public ?int $userId;
    public ?DateTimeImmutable $from;
    public ?DateTimeImmutable $to;
    public string $granularity;
    public string $mode;

    public function __construct(
        int $taskId,
        ?int $userId = null,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
        string $granularity = 'minute',
        string $mode = 'merged'
    ) {
        $this->taskId       = $taskId;
        $this->userId       = $userId;
        $this->from         = $from;
        $this->to           = $to;
        $this->granularity  = $granularity;
        $this->mode         = $mode;
    }
}