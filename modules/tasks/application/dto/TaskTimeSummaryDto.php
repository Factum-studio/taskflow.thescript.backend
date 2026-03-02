<?php

namespace modules\tasks\application\dto;

class TaskTimeSummaryDto
{
    public int $taskId;
    public string $taskTitle;
    /** @var TimeBlockDto[] */
    public array $blocks;
    public int $totalDuration;

    public function __construct(array $data)
    {
        $this->taskId           = $data['taskId'];
        $this->taskTitle        = $data['taskTitle'];
        $this->blocks           = $data['blocks'];
        $this->totalDuration    = $data['totalDuration'];
    }
}