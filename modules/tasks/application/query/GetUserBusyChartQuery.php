<?php

namespace modules\tasks\application\query;

use DateTimeImmutable;

class GetUserBusyChartQuery
{
    public int $userId;
    public DateTimeImmutable $from;
    public DateTimeImmutable $to;
    public string $granularity; // 'minute', 'ten_minutes', 'hour' и т.д.
    public string $mode; // 'merged', 'separate', 'overlap'

    public function __construct(
        int $userId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        string $granularity = 'minute',
        string $mode = 'merged'
    ) {
        $this->userId       = $userId;
        $this->from         = $from;
        $this->to           = $to;
        $this->granularity  = $granularity;
        $this->mode         = $mode;
    }
}