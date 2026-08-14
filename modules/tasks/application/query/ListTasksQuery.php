<?php

namespace modules\tasks\application\query;

class ListTasksQuery
{
    public ?int $columnId = null;
    public ?int $assignedTo = null;
    public ?int $createdBy = null;
    public ?int $boardId = null;
    public ?int $parentId = null;
    public ?bool $onlyOverdue = null;
    public ?bool $onlyComplete = null;
    public ?bool $includeDeleted = null;
    public int $userId;

    public function __construct(int $userId, array $filters = [])
    {
        $this->userId = $userId;
        foreach ($filters as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Преобразует query в критерии для репозитория
     * @return array
     */
    public function toCriteria(): array
    {
        $criteria = [];
        if ($this->columnId !== null) {
            $criteria['columnId'] = $this->columnId;
        }
        if ($this->assignedTo !== null) {
            $criteria['assigned_to'] = $this->assignedTo;
        }
        if ($this->createdBy !== null) {
            $criteria['created_by'] = $this->createdBy;
        }
        if ($this->boardId !== null) {
            $criteria['board_id'] = $this->boardId;
        }
        if ($this->parentId !== null) {
            $criteria['parent_id'] = $this->parentId;
        }
        if ($this->onlyComplete !== null) {
            $criteria['complete'] = $this->onlyComplete ? 1 : 0;
        }
        if ($this->onlyOverdue !== null) {
            $criteria['overdue'] = $this->onlyOverdue ? 1 : 0;
        }
        if ($this->includeDeleted !== null) {
            $criteria['include_deleted'] = $this->includeDeleted;
        }
        return $criteria;
    }
}