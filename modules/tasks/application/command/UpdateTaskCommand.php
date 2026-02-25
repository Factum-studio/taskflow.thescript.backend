<?php

namespace modules\tasks\application\command;

use DateTimeImmutable;

class UpdateTaskCommand
{
    public int $id;
    public ?string $title;
    public ?string $description;
    public ?int $statusId;
    public ?int $priorityId;
    public ?DateTimeImmutable $dueDate;
    public ?int $assignedTo;
    public ?int $boardId;
    public ?int $parentId;
    public int $updatedBy;

    public function __construct(
        int $id,
        int $updatedBy,
        ?string $title = null,
        ?string $description = null,
        ?int $statusId = null,
        ?int $priorityId = null,
        ?DateTimeImmutable $dueDate = null,
        ?int $assignedTo = null,
        ?int $boardId = null,
        ?int $parentId = null
    ) {
        $this->id           = $id;
        $this->updatedBy    = $updatedBy;
        $this->title        = $title;
        $this->description  = $description;
        $this->statusId     = $statusId;
        $this->priorityId   = $priorityId;
        $this->dueDate      = $dueDate;
        $this->assignedTo   = $assignedTo;
        $this->boardId      = $boardId;
        $this->parentId     = $parentId;
    }
}