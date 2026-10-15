<?php

declare(strict_types=1);

namespace modules\company\application\command;

class ChangeMemberRoleCommand
{
    public int $companyId;
    public int $userId;
    public int $targetUserId;
    public string $newRole;

    public function __construct(int $companyId, int $userId, int $targetUserId, string $newRole)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->targetUserId = $targetUserId;
        $this->newRole = $newRole;
    }
}
