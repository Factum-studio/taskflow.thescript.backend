<?php

namespace modules\tasks\domain\entity;

use modules\tasks\domain\valueObject\StatusId;

class TaskStatus
{
    private StatusId $id;
    private string $name;
    private string $label;
    private int $sortOrder;
    private ?int $workflowId;

    public function __construct(
        StatusId $id,
        string $name,
        string $label,
        int $sortOrder,
        ?int $workflowId = null
    ) {
        $this->id           = $id;
        $this->name         = $name;
        $this->label        = $label;
        $this->sortOrder    = $sortOrder;
        $this->workflowId   = $workflowId;
    }

    public function getId(): StatusId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getWorkflowId(): ?int
    {
        return $this->workflowId;
    }
}