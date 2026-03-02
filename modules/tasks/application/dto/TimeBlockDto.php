<?php

namespace modules\tasks\application\dto;

class TimeBlockDto
{
    public string $start;
    public string $end;
    public ?string $type; // 'single', 'merged', 'overlap'
    public ?array $taskIds; // для пересечений
    public ?int $duration;

    public function __construct(array $data)
    {
        $this->start    = $data['start'];
        $this->end      = $data['end'];
        $this->type     = $data['type'] ?? null;
        $this->taskIds  = $data['taskIds'] ?? null;
        $this->duration = $data['duration'] ?? null;
    }
}