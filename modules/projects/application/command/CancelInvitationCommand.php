<?php

namespace modules\projects\application\command;

use core\domain\valueObject\JwtToken;

class CancelInvitationCommand
{
    public int $projectId;
    public string $email;
    public int $cancelledBy;
    public JwtToken $jwtToken;

    public function __construct(
        int $projectId,
        string $email,
        int $cancelledBy,
        JwtToken $jwtToken
    ) {
        $this->projectId    = $projectId;
        $this->email        = $email;
        $this->cancelledBy  = $cancelledBy;
        $this->jwtToken     = $jwtToken;
    }
}