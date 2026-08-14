<?php

namespace modules\tasks\application\command;

class UpdateBoardColumnCommand
{
    public int $id;
    public ?string $name;
    public ?string $label;
    public ?int $sortOrder;
    public ?bool $isActive;
    public ?bool $isFinal;
    public ?string $color;
    public ?int $workflowId;
    public int $updatedBy;

    public function __construct(
        int $id,
        int $updatedBy,
        ?string $name = null,
        ?string $label = null,
        ?int $sortOrder = null,
        ?bool $isActive = null,
        ?bool $isFinal = null,
        ?string $color = null,
        ?int $workflowId = null
    ) {
        $this->id           = $id;
        $this->updatedBy    = $updatedBy;
        $this->name         = $name;
        $this->label        = $label;
        $this->sortOrder    = $sortOrder;
        $this->isActive     = $isActive;
        $this->isFinal      = $isFinal;
        $this->color        = $color;
        $this->workflowId   = $workflowId;
    }
}