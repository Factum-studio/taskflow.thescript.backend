<?php
namespace core\domain\entity;

use core\domain\valueObject\Rating;
use DateTimeImmutable;

class FeedbackRating
{
    private ?int $id;
    private int $userId;
    private Rating $speed;
    private Rating $functionality;
    private Rating $design;
    private Rating $usability;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $userId,
        Rating $speed,
        Rating $functionality,
        Rating $design,
        Rating $usability,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id               = $id;
        $this->userId           = $userId;
        $this->speed            = $speed;
        $this->functionality    = $functionality;
        $this->design           = $design;
        $this->usability        = $usability;
        $this->createdAt        = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt        = $updatedAt ?? new DateTimeImmutable();
    }

    public function updateRatings(Rating $speed, Rating $functionality, Rating $design, Rating $usability): void
    {
        $this->speed            = $speed;
        $this->functionality    = $functionality;
        $this->design           = $design;
        $this->usability        = $usability;
        $this->updatedAt        = new DateTimeImmutable();
    }

    public function isMaxRating(): bool
    {
        return $this->speed->isMax()
            && $this->functionality->isMax()
            && $this->design->isMax()
            && $this->usability->isMax();
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getSpeed(): Rating { return $this->speed; }
    public function getFunctionality(): Rating { return $this->functionality; }
    public function getDesign(): Rating { return $this->design; }
    public function getUsability(): Rating { return $this->usability; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}