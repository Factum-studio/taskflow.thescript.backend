<?php

declare(strict_types=1);

namespace modules\company\application\dto;

class CompanyMemberDto
{
    public int $companyId;
    public int $userId;
    public string $role;
    public ?int $invitedBy;
    public ?string $invitedAt;
    public ?string $acceptedAt;
    public string $joinedAt;

    public function __construct(array $data)
    {
        $this->companyId = $data['companyId'];
        $this->userId = $data['userId'];
        $this->role = $data['role'];
        $this->invitedBy = $data['invitedBy'] ?? null;
        $this->invitedAt = $data['invitedAt'] ?? null;
        $this->acceptedAt = $data['acceptedAt'] ?? null;
        $this->joinedAt = $data['joinedAt'];
    }
}