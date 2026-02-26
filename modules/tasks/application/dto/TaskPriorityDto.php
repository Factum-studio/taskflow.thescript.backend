<?php

namespace modules\tasks\application\dto;

class TaskPriorityDto
{
    public int $id;
    public int $value;
    public string $label;
    public ?string $color;

    public function __construct(array $data)
    {
        $this->id       = $data['id'];
        $this->value    = $data['value'];
        $this->label    = $data['label'];
        $this->color    = $data['color'] ?? null;
    }
}