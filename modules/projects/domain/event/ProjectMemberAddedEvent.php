<?php

namespace modules\projects\domain\event;

use DateTimeImmutable;
use modules\projects\domain\entity\ProjectUser;

class ProjectMemberAddedEvent implements IProjectDomainEvent
{
    private int $projectId;
    private int $userId;
    private string $role;
    private int $addedBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(ProjectUser $projectUser, int $addedBy)
    {
        $this->projectId = $projectUser->getProjectId()->value();
        $this->userId = $projectUser->getUserId()->value();
        $this->role = $projectUser->getRole()->value();
        $this->addedBy = $addedBy;
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

    public function getRole(): string
    {
        return $this->role;
    }

    public function getAddedBy(): int
    {
        return $this->addedBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}