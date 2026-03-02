<?php

namespace modules\tasks\application\dto;

class DailySummaryDto
{
    public int $id;
    public int $taskId;
    public int $userId;
    public string $date; // 'Y-m-d'
    public int $totalDuration; // секунды
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id               = $data['id'];
        $this->taskId           = $data['taskId'];
        $this->userId           = $data['userId'];
        $this->date             = $data['date'];
        $this->totalDuration    = $data['totalDuration'];
        $this->updatedAt        = $data['updatedAt'];
    }
}