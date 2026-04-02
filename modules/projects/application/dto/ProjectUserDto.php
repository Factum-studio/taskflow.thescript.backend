<?php

namespace modules\projects\application\dto;

class ProjectUserDto
{
    public int $projectId;
    public int $userId;
    public string $role;
    public ?int $invitedBy;
    public ?string $invitedAt;
    public ?string $acceptedAt;
    public string $joinedAt;

    public function __construct(array $data)
    {
        $this->projectId    = $data['projectId'];
        $this->userId       = $data['userId'];
        $this->role         = $data['role'];
        $this->invitedBy    = $data['invitedBy'] ?? null;
        $this->invitedAt    = $data['invitedAt'] ?? null;
        $this->acceptedAt   = $data['acceptedAt'] ?? null;
        $this->joinedAt     = $data['joinedAt'];
    }
}