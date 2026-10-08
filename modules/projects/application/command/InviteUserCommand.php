<?php

namespace modules\projects\application\command;

class InviteUserCommand
{
    public int $projectId;
    public ?string $email;
    public ?int $userId;
    public int $invitedBy;

    public function __construct(
        int $projectId,
        int $invitedBy,
        ?string $email = null,
        ?int $userId = null
    ) {
        $this->projectId    = $projectId;
        $this->email        = $email;
        $this->userId       = $userId;
        $this->invitedBy    = $invitedBy;
    }
}