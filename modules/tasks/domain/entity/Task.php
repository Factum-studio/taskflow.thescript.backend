<?php

namespace modules\tasks\domain\entity;

use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\ColumnId;
use modules\tasks\domain\valueObject\PriorityId;
use modules\tasks\domain\valueObject\UserId;
use modules\tasks\domain\valueObject\Title;
use DateTimeImmutable;

class Task
{
    private TaskId $id;
    private Title $title;
    private ?string $description;
    private ColumnId $columnId;
    private PriorityId $priorityId;
    private ?DateTimeImmutable $dueDate;
    private ?DateTimeImmutable $plannedStart;
    private ?DateTimeImmutable $plannedEnd;
    private UserId $createdBy;
    private ?UserId $assignedTo;
    private int $boardId;
    private ?TaskId $parentId;
    private bool $overdue;
    private bool $complete;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private ?DateTimeImmutable $deletedAt;

    public function __construct(
        TaskId $id,
        Title $title,
        ColumnId $columnId,
        PriorityId $priorityId,
        UserId $createdBy,
        int $boardId,
        ?string $description = null,
        ?DateTimeImmutable $dueDate = null,
        ?DateTimeImmutable $plannedStart = null,
        ?DateTimeImmutable $plannedEnd = null,
        ?UserId $assignedTo = null,
        ?TaskId $parentId = null,
        bool $overdue = false,
        bool $complete = false,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
        ?DateTimeImmutable $deletedAt = null
    ) {
        $this->id           = $id;
        $this->title        = $title;
        $this->columnId     = $columnId;
        $this->priorityId   = $priorityId;
        $this->createdBy    = $createdBy;
        $this->boardId      = $boardId;
        $this->description  = $description;
        $this->dueDate      = $dueDate;
        $this->plannedStart = $plannedStart;
        $this->plannedEnd   = $plannedEnd;
        $this->assignedTo   = $assignedTo;
        $this->parentId     = $parentId;
        $this->overdue      = $overdue;
        $this->complete     = $complete;
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
        $this->deletedAt    = $deletedAt;
    }

    public function getId(): TaskId { return $this->id; }
    public function getTitle(): Title { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getColumnId(): ColumnId { return $this->columnId; }
    public function getPriorityId(): PriorityId { return $this->priorityId; }
    public function getDueDate(): ?DateTimeImmutable { return $this->dueDate; }
    public function getPlannedStart(): ?DateTimeImmutable { return $this->plannedStart; }
    public function getPlannedEnd(): ?DateTimeImmutable { return $this->plannedEnd; }
    public function getCreatedBy(): UserId { return $this->createdBy; }
    public function getAssignedTo(): ?UserId { return $this->assignedTo; }
    public function getBoardId(): int { return $this->boardId; }
    public function getParentId(): ?TaskId { return $this->parentId; }
    public function isOverdue(): bool { return $this->overdue; }
    public function isComplete(): bool { return $this->complete; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
    public function getDeletedAt(): ?DateTimeImmutable { return $this->deletedAt; }

    public function changeTitle(Title $title): void
    {
        $this->title = $title;
        $this->touch();
    }

    public function changeDescription(?string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function moveToColumn(ColumnId $columnId): void
    {
        $this->columnId = $columnId;
        $this->touch();
    }

    public function changePriority(PriorityId $priorityId): void
    {
        $this->priorityId = $priorityId;
        $this->touch();
    }

    public function changeDueDate(?DateTimeImmutable $dueDate): void
    {
        $this->dueDate = $dueDate;
        $this->touch();
    }

    public function changePlannedTime(?DateTimeImmutable $plannedStart, ?DateTimeImmutable $plannedEnd): void
    {
        $this->plannedStart = $plannedStart;
        $this->plannedEnd = $plannedEnd;
        $this->touch();
    }

    public function assignTo(?UserId $assignedTo): void
    {
        $this->assignedTo = $assignedTo;
        $this->touch();
    }

    public function moveToBoard(int $boardId): void
    {
        $this->boardId = $boardId;
        $this->touch();
    }

    public function setParentId(?TaskId $parentId): void
    {
        $this->parentId = $parentId;
        $this->touch();
    }

    public function setBoardId(?int $boardId): void
    {
        $this->boardId = $boardId;
        $this->touch();
    }

    public function markOverdue(bool $overdue): void
    {
        $this->overdue = $overdue;
        $this->touch();
    }

    public function markComplete(bool $complete): void
    {
        $this->complete = $complete;
        $this->touch();
    }

    public function markAsDeleted(): void
    {
        $this->deletedAt = new DateTimeImmutable();
        $this->touch();
    }

    public function restore(): void
    {
        $this->deletedAt = null;
        $this->touch();
    }

    /**
     * @internal Используется только в репозитории, для того, чтобы установить ID после создания
     */
    public function setId(TaskId $id): void
    {
        $this->id = $id;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}