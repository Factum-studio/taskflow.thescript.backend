<?php

namespace modules\projects\application\command;

class CancelInvitationCommand
{
    public int $projectId;
    public string $email;
    public int $cancelledBy;

    public function __construct(int $projectId, string $email, int $cancelledBy)
    {
        $this->projectId    = $projectId;
        $this->email        = $email;
        $this->cancelledBy  = $cancelledBy;
    }
}