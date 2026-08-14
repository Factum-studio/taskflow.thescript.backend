<?php

namespace modules\projects\application\command;

class RemoveProjectMemberCommand
{
    public int $projectId;
    public int $userId;
    public int $removedBy;

    public function __construct(int $projectId, int $userId, int $removedBy)
    {
        $this->projectId    = $projectId;
        $this->userId       = $userId;
        $this->removedBy    = $removedBy;
    }
}