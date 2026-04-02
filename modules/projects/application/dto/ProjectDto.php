<?php

namespace modules\projects\application\dto;

class ProjectDto
{
    public int $id;
    public string $name;
    public string $type;
    public int $ownerId;
    public array $settings;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->name         = $data['name'];
        $this->type         = $data['type'];
        $this->ownerId      = $data['ownerId'];
        $this->settings     = $data['settings'] ?? [];
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
    }
}