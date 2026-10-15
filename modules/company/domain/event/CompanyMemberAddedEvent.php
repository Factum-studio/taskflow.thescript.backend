<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;

class CompanyMemberAddedEvent implements ICompanyDomainEvent
{
    private int $companyId;
    private int $userId;
    private string $role;
    private DateTimeImmutable $occurredAt;

    public function __construct(int $companyId, int $userId, string $role)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->role = $role;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->companyId;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getRole(): string
    {
        return $this->role;
    }
    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
