<?php
namespace core\domain\entity;

use core\domain\valueObject\IdeaType;
use DateTimeImmutable;

class FeedbackIdea
{
    private ?int $id;
    private int $userId;
    private IdeaType $type;
    private string $comment;
    private bool $isImplemented;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $userId,
        IdeaType $type,
        string $comment,
        bool $isImplemented = false,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id               = $id;
        $this->userId           = $userId;
        $this->type             = $type;
        $this->comment          = $comment;
        $this->isImplemented    = $isImplemented;
        $this->createdAt        = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt        = $updatedAt ?? new DateTimeImmutable();
    }

    public function markImplemented(): void
    {
        $this->isImplemented    = true;
        $this->updatedAt        = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getType(): IdeaType { return $this->type; }
    public function getComment(): string { return $this->comment; }
    public function isImplemented(): bool { return $this->isImplemented; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}