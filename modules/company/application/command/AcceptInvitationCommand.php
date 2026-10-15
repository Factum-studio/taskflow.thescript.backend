<?php

declare(strict_types=1);

namespace modules\company\application\command;

class AcceptInvitationCommand
{
    public string $token;
    public int $userId;

    public function __construct(string $token, int $userId)
    {
        $this->token = $token;
        $this->userId = $userId;
    }
}