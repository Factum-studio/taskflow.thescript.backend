<?php

namespace modules\projects\domain\event;

use DateTimeImmutable;
use modules\projects\domain\entity\Invitation;

class InvitationCancelledEvent implements IProjectDomainEvent
{
    private int $invitationId;
    private int $projectId;
    private string $email;
    private int $cancelledBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Invitation $invitation, int $cancelledBy)
    {
        $this->invitationId = $invitation->getId();
        $this->projectId    = $invitation->getProjectId()->getValue();
        $this->email        = $invitation->getEmail();
        $this->cancelledBy  = $cancelledBy;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->invitationId; }
    public function getProjectId(): int { return $this->projectId; }
    public function getEmail(): string { return $this->email; }
    public function getCancelledBy(): int { return $this->cancelledBy; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}