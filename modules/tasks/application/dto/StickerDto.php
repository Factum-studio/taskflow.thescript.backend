<?php

namespace modules\tasks\application\dto;

class StickerDto
{
    public int $id;
    public string $name;
    public string $type;
    public ?int $projectId;
    public ?array $data;
    public ?string $color;
    public int $createdBy;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->name         = $data['name'];
        $this->type         = $data['type'];
        $this->projectId    = $data['projectId'] ?? null;
        $this->data         = $data['data'] ?? null;
        $this->color        = $data['color'] ?? null;
        $this->createdBy    = $data['createdBy'];
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
    }
}