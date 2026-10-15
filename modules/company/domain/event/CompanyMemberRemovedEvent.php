<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;

class CompanyMemberRemovedEvent implements ICompanyDomainEvent
{
    private int $companyId;
    private int $userId;
    private DateTimeImmutable $occurredAt;

    public function __construct(int $companyId, int $userId)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->companyId; }
    public function getUserId(): int { return $this->userId; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}