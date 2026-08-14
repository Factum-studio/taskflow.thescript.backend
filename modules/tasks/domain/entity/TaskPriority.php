<?php

namespace modules\tasks\domain\entity;

use modules\tasks\domain\valueObject\PriorityId;

class TaskPriority
{
    private PriorityId $id;
    private int $value;
    private string $label;
    private ?string $color;

    public function __construct(
        PriorityId $id,
        int $value,
        string $label,
        ?string $color = null
    ) {
        $this->id       = $id;
        $this->value    = $value;
        $this->label    = $label;
        $this->color    = $color;
    }

    public function getId(): PriorityId
    {
        return $this->id;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
}