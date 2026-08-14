<?php
namespace core\domain\entity;

use core\domain\valueObject\AuthorId;
use core\domain\valueObject\AuthorPhrase;
use core\domain\valueObject\Badge;

class Author
{
    private AuthorId $id;
    private int $userId;
    private AuthorPhrase $phrase;
    /** @var Badge[] */
    private array $badges;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        AuthorId $id,
        int $userId,
        AuthorPhrase $phrase,
        array $badges,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id           = $id;
        $this->userId       = $userId;
        $this->phrase       = $phrase;
        $this->setBadges($badges);
        $this->createdAt    = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new \DateTimeImmutable();
    }

    private function setBadges(array $badges): void
    {
        foreach ($badges as $badge) {
            if (!$badge instanceof Badge) {
                throw new \InvalidArgumentException('Badges must be array of Badge objects');
            }
        }
        $this->badges = $badges;
    }

    public function updatePhrase(AuthorPhrase $phrase): void
    {
        $this->phrase = $phrase;
        $this->touch();
    }

    public function updateBadges(array $badges): void
    {
        $this->setBadges($badges);
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): AuthorId { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getPhrase(): AuthorPhrase { return $this->phrase; }
    /** @return Badge[] */
    public function getBadges(): array { return $this->badges; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}