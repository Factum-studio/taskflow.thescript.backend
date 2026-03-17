<?php

namespace modules\tasks\application\dto;

class TimeIntervalDto
{
    public int $id;
    public int $taskId;
    public int $userId;
    public string $startTime; // формат 'Y-m-d H:i:s'
    public ?string $endTime;
    public ?int $duration; // секунды
    public ?string $comment;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->taskId       = $data['taskId'];
        $this->userId       = $data['userId'];
        $this->startTime    = $data['startTime'];
        $this->endTime      = $data['endTime'] ?? null;
        $this->duration     = $data['duration'] ?? null;
        $this->comment      = $data['comment'] ?? null;
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
    }
}