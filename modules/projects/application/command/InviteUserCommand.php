<?php

namespace modules\projects\application\command;

class InviteUserCommand
{
    public int $projectId;
    public string $email;
    public int $invitedBy;
    public string $jwtToken;

    public function __construct(int $projectId, string $email, int $invitedBy, string $jwtToken)
    {
        $this->projectId    = $projectId;
        $this->email        = $email;
        $this->invitedBy    = $invitedBy;
        $this->jwtToken     = $jwtToken;
    }
}