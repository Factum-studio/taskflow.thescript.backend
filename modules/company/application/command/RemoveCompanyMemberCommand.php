<?php

declare(strict_types=1);

namespace modules\company\application\command;

class RemoveCompanyMemberCommand
{
    public int $companyId;
    public int $userId;
    public int $targetUserId;

    public function __construct(int $companyId, int $userId, int $targetUserId)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->targetUserId = $targetUserId;
    }
}