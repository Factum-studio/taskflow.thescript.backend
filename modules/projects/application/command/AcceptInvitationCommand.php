<?php

namespace modules\projects\application\command;

use core\domain\valueObject\JwtToken;

class AcceptInvitationCommand
{
    public string $token;
    public int $userId;
    public JwtToken $jwtToken;

    public function __construct(string $token, int $userId, JwtToken $jwtToken)
    {
        $this->token    = $token;
        $this->userId   = $userId;
        $this->jwtToken = $jwtToken;
    }
}