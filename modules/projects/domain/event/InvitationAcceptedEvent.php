<?php

namespace modules\projects\domain\event;

use DateTimeImmutable;
use modules\projects\domain\entity\Invitation;
use modules\projects\domain\valueObject\UserId;

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
        $this->projectId    = $invitation->getProjectId()->getValue();
        $this->email        = $invitation->getEmail();
        $this->userId       = $userId->getValue();
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->invitationId; }
    public function getProjectId(): int { return $this->projectId; }
    public function getEmail(): string { return $this->email; }
    public function getUserId(): int { return $this->userId; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}