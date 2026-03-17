<?php

namespace modules\tasks\application\command;

use DateTimeImmutable;

class CreateTaskCommand
{
    public string $title;
    public ?string $description;
    public int $statusId;
    public int $priorityId;
    public ?DateTimeImmutable $dueDate;
    public ?DateTimeImmutable $plannedStart;
    public ?DateTimeImmutable $plannedEnd;
    public int $createdBy;
    public ?int $assignedTo;
    public int $boardId;
    public ?int $parentId;

    public function __construct(
        string $title,
        int $statusId,
        int $priorityId,
        int $createdBy,
        int $boardId,
        ?string $description = null,
        ?DateTimeImmutable $dueDate = null,
        ?DateTimeImmutable $plannedStart = null,
        ?DateTimeImmutable $plannedEnd = null,
        ?int $assignedTo = null,
        ?int $parentId = null
    ) {
        $this->title        = $title;
        $this->statusId     = $statusId;
        $this->priorityId   = $priorityId;
        $this->createdBy    = $createdBy;
        $this->boardId      = $boardId;
        $this->description  = $description;
        $this->dueDate      = $dueDate;
        $this->plannedStart = $plannedStart;
        $this->plannedEnd   = $plannedEnd;
        $this->assignedTo   = $assignedTo;
        $this->parentId     = $parentId;
    }
}