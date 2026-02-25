<?php

namespace modules\tasks\domain\event;

use DateTimeImmutable;

interface ITaskDomainEvent
{
    public function getAggregateId(): int;
    public function getOccurredAt(): DateTimeImmutable;
}