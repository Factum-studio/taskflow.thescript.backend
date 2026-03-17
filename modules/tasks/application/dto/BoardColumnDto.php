<?php

namespace modules\tasks\application\dto;

class BoardColumnDto
{
    public int $id;
    public int $boardId;
    public string $name;
    public string $label;
    public int $sortOrder;
    public bool $isActive;
    public bool $isFinal;
    public ?string $color;
    public ?int $workflowId;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->boardId      = $data['boardId'];
        $this->name         = $data['name'];
        $this->label        = $data['label'];
        $this->sortOrder    = $data['sortOrder'];
        $this->isActive     = $data['isActive'];
        $this->isFinal      = $data['isFinal'];
        $this->color        = $data['color'] ?? null;
        $this->workflowId   = $data['workflowId'] ?? null;
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
    }
}