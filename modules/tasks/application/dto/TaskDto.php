<?php

namespace modules\tasks\application\dto;

class TaskDto
{
    public int $id;
    public string $title;
    public ?string $description;
    public int $statusId;
    public int $priorityId;
    public ?string $dueDate; // формат timestamp, передаём строкой
    public int $createdBy;
    public ?int $assignedTo;
    public int $boardId;
    public ?int $parentId;
    public bool $overdue;
    public string $createdAt;
    public string $updatedAt;
    public ?string $deletedAt;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->title        = $data['title'];
        $this->description  = $data['description'] ?? null;
        $this->statusId     = $data['statusId'];
        $this->priorityId   = $data['priorityId'];
        $this->dueDate      = $data['dueDate'] ?? null;
        $this->createdBy    = $data['createdBy'];
        $this->assignedTo   = $data['assignedTo'] ?? null;
        $this->boardId      = $data['boardId'];
        $this->parentId     = $data['parentId'] ?? null;
        $this->overdue      = $data['overdue'] ?? false;
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
        $this->deletedAt    = $data['deletedAt'] ?? null;
    }
}