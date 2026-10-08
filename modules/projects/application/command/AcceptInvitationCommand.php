<?php

namespace modules\projects\application\command;


class AcceptInvitationCommand
{
    public string $token;
    public int $userId;

    public function __construct(string $token, int $userId)
    {
        $this->token    = $token;
        $this->userId   = $userId;
    }
}