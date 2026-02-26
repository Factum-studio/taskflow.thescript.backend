<?php

namespace modules\tasks\application\dto;

class TaskStatusDto
{
    public int $id;
    public string $name;
    public string $label;
    public int $sortOrder;
    public ?int $workflowId;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->name         = $data['name'];
        $this->label        = $data['label'];
        $this->sortOrder    = $data['sortOrder'];
        $this->workflowId   = $data['workflowId'] ?? null;
    }
}