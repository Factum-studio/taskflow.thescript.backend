<?php

namespace modules\tasks\application\command;

use DateTimeImmutable;

class UpdateTaskCommand
{
    public int $id;
    public ?string $title;
    public ?string $description;
    public ?int $columnId;
    public ?int $priorityId;
    public ?DateTimeImmutable $dueDate;
    public ?DateTimeImmutable $plannedStart;
    public ?DateTimeImmutable $plannedEnd;
    public ?int $assignedTo;
    public ?int $boardId;
    public ?int $parentId;
    public int $updatedBy;

    public function __construct(
        int $id,
        int $updatedBy,
        ?string $title = null,
        ?string $description = null,
        ?int $columnId = null,
        ?int $priorityId = null,
        ?DateTimeImmutable $dueDate = null,
        ?DateTimeImmutable $plannedStart = null,
        ?DateTimeImmutable $plannedEnd = null,
        ?int $assignedTo = null,
        ?int $boardId = null,
        ?int $parentId = null
    ) {
        $this->id           = $id;
        $this->updatedBy    = $updatedBy;
        $this->title        = $title;
        $this->description  = $description;
        $this->columnId     = $columnId;
        $this->priorityId   = $priorityId;
        $this->dueDate      = $dueDate;
        $this->plannedStart = $plannedStart;
        $this->plannedEnd   = $plannedEnd;
        $this->assignedTo   = $assignedTo;
        $this->boardId      = $boardId;
        $this->parentId     = $parentId;
    }
}