<?php

declare(strict_types=1);

namespace modules\company\domain\event;

use DateTimeImmutable;

interface ICompanyDomainEvent
{
    public function getAggregateId(): int;
    public function getOccurredAt(): DateTimeImmutable;
}
