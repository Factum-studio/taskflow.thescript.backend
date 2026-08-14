<?php

namespace modules\tasks\application\command;

class CreateStickerCommand
{
    public string $name;
    public string $type;
    public ?int $projectId;
    public ?array $data;
    public ?string $color;
    public int $createdBy;

    public function __construct(
        string $name,
        string $type,
        int $createdBy,
        ?int $projectId = null,
        ?array $data = null,
        ?string $color = null
    ) {
        $this->name         = $name;
        $this->type         = $type;
        $this->createdBy    = $createdBy;
        $this->projectId    = $projectId;
        $this->data         = $data;
        $this->color        = $color;
    }
}