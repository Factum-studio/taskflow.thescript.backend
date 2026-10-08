<?php

namespace modules\projects\domain\event;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\projects\domain\entity\Invitation;

class InvitationAcceptedEvent implements IProjectDomainEvent
{
    private int $invitationId;
    private int $projectId;
    private string $email;
    private int $userId;
    private DateTimeImmutable $occurredAt;

    public function __construct(Invitation $invitation, UserId $userId)
    {
        $this->invitationId = $invitation->getId();
        $this->projectId    = $invitation->getProjectId()->value();
        $this->email        = $invitation->getEmail();
        $this->userId       = $userId->value();
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->invitationId; }
    public function getProjectId(): int { return $this->projectId; }
    public function getEmail(): string { return $this->email; }
    public function getUserId(): int { return $this->userId; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}