<?php

namespace modules\projects\application\command;

class AddProjectMemberCommand
{
    public int $projectId;
    public int $userId;
    public string $role;
    public int $addedBy;

    public function __construct(
        int $projectId,
        int $userId,
        string $role,
        int $addedBy
    ) {
        $this->projectId    = $projectId;
        $this->userId       = $userId;
        $this->role         = $role;
        $this->addedBy      = $addedBy;
    }
}