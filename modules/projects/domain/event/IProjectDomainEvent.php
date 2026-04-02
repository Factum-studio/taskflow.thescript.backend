<?php

namespace modules\projects\domain\event;

use DateTimeImmutable;

interface IProjectDomainEvent
{
    public function getAggregateId(): int;
    public function getOccurredAt(): DateTimeImmutable;
}