<?php

namespace modules\tasks\domain\entity;

use DateTimeImmutable;
use modules\tasks\domain\valueObject\BoardId;
use modules\tasks\domain\valueObject\ColumnId;

class BoardColumn
{
    private ColumnId $id;
    private BoardId $boardId;
    private string $name;   // системное имя (slug)
    private string $label;  // отображаемое название
    private int $sortOrder;
    private bool $isActive;
    private bool $isFinal;
    private ?string $color;
    private ?int $workflowId;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        ColumnId $id,
        BoardId $boardId,
        string $name,
        string $label,
        int $sortOrder,
        bool $isActive = true,
        bool $isFinal = false,
        ?string $color = null,
        ?int $workflowId = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id           = $id;
        $this->boardId      = $boardId;
        $this->setName($name);
        $this->setLabel($label);
        $this->sortOrder    = $sortOrder;
        $this->isActive     = $isActive;
        $this->isFinal      = $isFinal;
        $this->color        = $color;
        $this->workflowId   = $workflowId;
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    // Геттеры
    public function getId(): ColumnId { return $this->id; }
    public function getBoardId(): BoardId { return $this->boardId; }
    public function getName(): string { return $this->name; }
    public function getLabel(): string { return $this->label; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function isActive(): bool { return $this->isActive; }
    public function isFinal(): bool { return $this->isFinal; }
    public function getColor(): ?string { return $this->color; }
    public function getWorkflowId(): ?int { return $this->workflowId; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function rename(string $name): void
    {
        $this->setName($name);
        $this->touch();
    }

    public function changeLabel(string $label): void
    {
        $this->setLabel($label);
        $this->touch();
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
        $this->touch();
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    public function setFinal(bool $isFinal): void
    {
        $this->isFinal = $isFinal;
        $this->touch();
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
        $this->touch();
    }

    public function setWorkflowId(?int $workflowId): void
    {
        $this->workflowId = $workflowId;
        $this->touch();
    }

    // Внутренние сеттеры
    private function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Column name cannot be empty');
        }
        if (mb_strlen($name) > 50) {
            throw new \InvalidArgumentException('Column name must not exceed 50 characters');
        }
        $this->name = $name;
    }

    private function setLabel(string $label): void
    {
        $label = trim($label);
        if ($label === '') {
            throw new \InvalidArgumentException('Column label cannot be empty');
        }
        if (mb_strlen($label) > 255) {
            throw new \InvalidArgumentException('Column label must not exceed 255 characters');
        }
        $this->label = $label;
    }

    /**
     * @internal Используется только в репозитории для установки ID после создания
     */
    public function setId(ColumnId $id): void
    {
        $this->id = $id;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}