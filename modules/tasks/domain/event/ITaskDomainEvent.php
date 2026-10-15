<?php

declare(strict_types=1);

namespace modules\tasks\domain\event;

use DateTimeImmutable;

interface ITaskDomainEvent
{
    public function getAggregateId(): int;
    public function getOccurredAt(): DateTimeImmutable;
}
