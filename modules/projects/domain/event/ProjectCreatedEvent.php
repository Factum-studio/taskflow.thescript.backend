<?php

namespace modules\projects\domain\event;

use DateTimeImmutable;
use modules\projects\domain\entity\Project;

class ProjectCreatedEvent implements IProjectDomainEvent
{
    private int $projectId;
    private int $ownerId;
    private DateTimeImmutable $occurredAt;

    public function __construct(Project $project)
    {
        $this->projectId = $project->getId()->getValue();
        $this->ownerId = $project->getOwnerId()->getValue();
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->projectId;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}