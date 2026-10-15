<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;

class CompanyInvitationCreatedEvent implements ICompanyDomainEvent
{
    private int $companyId;
    private string $email;
    private int $invitedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(int $companyId, string $email, int $invitedBy)
    {
        $this->companyId = $companyId;
        $this->email = $email;
        $this->invitedBy = $invitedBy;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->companyId; }
    public function getEmail(): string { return $this->email; }
    public function getInvitedBy(): int { return $this->invitedBy; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}