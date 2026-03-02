<?php

namespace modules\tasks\application\query;

use DateTimeImmutable;

class GetDailySummaryQuery
{
    public int $userId;
    public DateTimeImmutable $date;

    public function __construct(int $userId, DateTimeImmutable $date)
    {
        $this->userId   = $userId;
        $this->date     = $date;
    }
}