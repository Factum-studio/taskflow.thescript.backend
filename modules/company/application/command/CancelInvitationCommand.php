<?php

declare(strict_types=1);

namespace modules\company\application\command;

class CancelInvitationCommand
{
    public int $companyId;
    public int $userId;
    public int $invitationId;

    public function __construct(int $companyId, int $userId, int $invitationId)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->invitationId = $invitationId;
    }
}
