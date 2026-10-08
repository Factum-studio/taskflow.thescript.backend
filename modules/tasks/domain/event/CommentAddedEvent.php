<?php

namespace modules\tasks\domain\event;

use DateTimeImmutable;
use modules\tasks\domain\entity\Comment;

class CommentAddedEvent implements ITaskDomainEvent
{
    private int $commentId;
    private int $taskId;
    private int $userId;
    private DateTimeImmutable $occurredAt;

    public function __construct(Comment $comment)
    {
        $this->commentId    = $comment->getId()->value();
        $this->taskId       = $comment->getTaskId()->value();
        $this->userId       = $comment->getUserId()->value();
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getAggregateId(): int
    {
        return $this->taskId;
    }

    public function getCommentId(): int
    {
        return $this->commentId;
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