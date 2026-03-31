<?php

namespace modules\projects\domain\event;

use DateTimeImmutable;
use modules\projects\domain\entity\Invitation;

class InvitationCreatedEvent implements IProjectDomainEvent
{
    private int $invitationId;
    private int $projectId;
    private string $email;
    private int $invitedBy;
    private string $token;
    private DateTimeImmutable $occurredAt;

    public function __construct(Invitation $invitation)
    {
        $this->invitationId = $invitation->getId();
        $this->projectId    = $invitation->getProjectId()->getValue();
        $this->email        = $invitation->getEmail();
        $this->invitedBy    = $invitation->getInvitedBy()->getValue();
        $this->token        = $invitation->getToken();
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->invitationId; }
    public function getProjectId(): int { return $this->projectId; }
    public function getEmail(): string { return $this->email; }
    public function getInvitedBy(): int { return $this->invitedBy; }
    public function getToken(): string { return $this->token; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}