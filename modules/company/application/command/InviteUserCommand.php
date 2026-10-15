<?php

declare(strict_types=1);

namespace modules\company\application\command;

class InviteUserCommand
{
    public int $companyId;
    public int $invitedBy;
    public string $email;

    public function __construct(int $companyId, int $invitedBy, string $email)
    {
        $this->companyId = $companyId;
        $this->invitedBy = $invitedBy;
        $this->email = $email;
    }
}
