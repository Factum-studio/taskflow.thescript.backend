<?php

namespace modules\tasks\domain\entity;

use DateTimeImmutable;
use InvalidArgumentException;
use modules\tasks\domain\valueObject\CommentId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;

class Comment
{
    private CommentId $id;
    private TaskId $taskId;
    private UserId $userId;
    private string $content;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        CommentId $id,
        TaskId $taskId,
        UserId $userId,
        string $content,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->setContent($content);
        $this->id           = $id;
        $this->taskId       = $taskId;
        $this->userId       = $userId;
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): CommentId
    {
        return $this->id;
    }

    public function getTaskId(): TaskId
    {
        return $this->taskId;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changeContent(string $newContent): void
    {
        $this->setContent($newContent);
        $this->touch();
    }

    private function setContent(string $content): void
    {
        $content = trim($content);
        if ($content === '') {
            throw new InvalidArgumentException('Comment content cannot be empty');
        }
        $this->content = $content;
    }

    /**
     * @internal Используется только в репозитории, для того, чтобы установить ID после создания
     */
    public function setId(CommentId $id): void
    {
        $this->id = $id;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}