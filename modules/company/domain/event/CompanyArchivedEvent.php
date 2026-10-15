<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;
use modules\company\domain\entity\Company;

class CompanyArchivedEvent implements ICompanyDomainEvent
{
    private int $companyId;
    private DateTimeImmutable $occurredAt;

    public function __construct(Company $company)
    {
        $this->companyId = $company->getId()->value();
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int { return $this->companyId; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
}