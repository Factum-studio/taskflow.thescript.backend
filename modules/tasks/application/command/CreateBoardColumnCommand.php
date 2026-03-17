<?php

namespace modules\tasks\application\command;

class CreateBoardColumnCommand
{
    public int $boardId;
    public string $name;
    public string $label;
    public int $sortOrder;
    public bool $isActive;
    public bool $isFinal;
    public ?string $color;
    public ?int $workflowId;
    public int $createdBy;

    public function __construct(
        int $boardId,
        string $name,
        string $label,
        int $sortOrder,
        int $createdBy,
        bool $isActive = true,
        bool $isFinal = false,
        ?string $color = null,
        ?int $workflowId = null
    ) {
        $this->boardId      = $boardId;
        $this->name         = $name;
        $this->label        = $label;
        $this->sortOrder    = $sortOrder;
        $this->createdBy    = $createdBy;
        $this->isActive     = $isActive;
        $this->isFinal      = $isFinal;
        $this->color        = $color;
        $this->workflowId   = $workflowId;
    }
}