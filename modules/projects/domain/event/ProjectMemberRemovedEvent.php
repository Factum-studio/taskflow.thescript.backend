<?php

namespace modules\projects\domain\event;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\projects\domain\valueObject\ProjectId;

class ProjectMemberRemovedEvent implements IProjectDomainEvent
{
    private int $projectId;
    private int $userId;
    private int $removedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(ProjectId $projectId, UserId $userId, int $removedBy)
    {
        $this->projectId = $projectId->value();
        $this->userId = $userId->value();
        $this->removedBy = $removedBy;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->projectId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRemovedBy(): int
    {
        return $this->removedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}