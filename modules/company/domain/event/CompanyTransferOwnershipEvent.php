<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;

class CompanyTransferOwnershipEvent implements ICompanyDomainEvent
{
    private int $companyId;
    private int $previousOwnerId;
    private int $newOwnerId;
    private DateTimeImmutable $occurredAt;

    public function __construct(int $companyId, int $previousOwnerId, int $newOwnerId)
    {
        $this->companyId = $companyId;
        $this->previousOwnerId = $previousOwnerId;
        $this->newOwnerId = $newOwnerId;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->companyId;
    }
    public function getPreviousOwnerId(): int
    {
        return $this->previousOwnerId;
    }
    public function getNewOwnerId(): int
    {
        return $this->newOwnerId;
    }
    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
