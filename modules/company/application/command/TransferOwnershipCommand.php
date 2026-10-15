<?php

declare(strict_types=1);

namespace modules\company\application\command;

class TransferOwnershipCommand
{
    public int $companyId;
    public int $userId;
    public int $newOwnerId;

    public function __construct(int $companyId, int $userId, int $newOwnerId)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->newOwnerId = $newOwnerId;
    }
}
