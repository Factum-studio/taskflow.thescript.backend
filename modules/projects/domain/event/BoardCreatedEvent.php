<?php

namespace modules\projects\domain\event;

use DateTimeImmutable;
use modules\projects\domain\entity\Board;

class BoardCreatedEvent implements IProjectDomainEvent
{
    private int $boardId;
    private int $projectId;
    private int $createdBy;
    private DateTimeImmutable $occurredAt;

    public function __construct(Board $board)
    {
        $this->boardId = $board->getId()->getValue();
        $this->projectId = $board->getProjectId()->getValue();
        $this->createdBy = $board->getCreatedBy()->getValue();
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->boardId;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}