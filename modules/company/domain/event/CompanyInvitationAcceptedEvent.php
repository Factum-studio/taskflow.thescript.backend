<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;

class CompanyInvitationAcceptedEvent implements ICompanyDomainEvent
{
    private int $companyId;
    private string $email;
    private int $userId;
    private DateTimeImmutable $occurredAt;

    public function __construct(int $companyId, string $email, int $userId)
    {
        $this->companyId = $companyId;
        $this->email = $email;
        $this->userId = $userId;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->companyId; }
    public function getEmail(): string { return $this->email; }
    public function getUserId(): int { return $this->userId; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}