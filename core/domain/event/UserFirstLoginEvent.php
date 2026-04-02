<?php

namespace core\domain\event;

use DateTimeImmutable;

class UserFirstLoginEvent
{
    private int $userId;
    private DateTimeImmutable $occurredAt;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}