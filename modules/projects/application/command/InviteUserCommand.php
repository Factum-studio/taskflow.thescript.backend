<?php

namespace modules\projects\application\command;

use core\domain\valueObject\JwtToken;

class InviteUserCommand
{
    public int $projectId;
    public ?string $email;
    public ?int $userId;
    public int $invitedBy;
    public JwtToken $jwtToken;

    public function __construct(
        int $projectId,
        int $invitedBy,
        JwtToken $jwtToken,
        ?string $email = null,
        ?int $userId = null
    ) {
        $this->projectId    = $projectId;
        $this->email        = $email;
        $this->userId       = $userId;
        $this->invitedBy    = $invitedBy;
        $this->jwtToken     = $jwtToken;
    }
}