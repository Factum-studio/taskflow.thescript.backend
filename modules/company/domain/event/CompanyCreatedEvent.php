<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;
use modules\company\domain\entity\Company;

class CompanyCreatedEvent implements ICompanyDomainEvent
{
    private int $companyId;
    private int $ownerId;
    private string $companyName;
    private DateTimeImmutable $occurredAt;

    public function __construct(Company $company)
    {
        $this->companyId = $company->getId()->value();
        $this->ownerId = $company->getOwnerId()->value();
        $this->companyName = $company->getName();
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->companyId;
    }
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }
    public function getCompanyName(): string
    {
        return $this->companyName;
    }
    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
