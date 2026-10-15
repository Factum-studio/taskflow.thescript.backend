<?php

declare(strict_types=1);

namespace modules\projects\domain\event;

use DateTimeImmutable;

interface IProjectDomainEvent
{
    public function getAggregateId(): int;
    public function getOccurredAt(): DateTimeImmutable;
}
