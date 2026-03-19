<?php

namespace modules\projects\application\command;

class ChangeMemberRoleCommand
{
    public int $projectId;
    public int $userId;
    public string $newRole;
    public int $changedBy;

    public function __construct(
        int $projectId,
        int $userId,
        string $newRole,
        int $changedBy
    ) {
        $this->projectId    = $projectId;
        $this->userId       = $userId;
        $this->newRole      = $newRole;
        $this->changedBy    = $changedBy;
    }
}