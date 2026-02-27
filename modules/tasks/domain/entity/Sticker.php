<?php

namespace modules\tasks\domain\entity;

use DateTimeImmutable;
use InvalidArgumentException;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\StickerName;
use modules\tasks\domain\valueObject\StickerType;
use modules\tasks\domain\valueObject\UserId;

class Sticker
{
    private StickerId $id;
    private StickerName $name;
    private StickerType $type;
    private ?int $projectId;
    private ?array $data; // JSON-совместимые данные
    private ?string $color;
    private UserId $createdBy;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        StickerId $id,
        StickerName $name,
        StickerType $type,
        UserId $createdBy,
        ?int $projectId = null,
        ?array $data = null,
        ?string $color = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        if ($type->isUser() && $projectId === null) {
            throw new InvalidArgumentException('User sticker must be assigned to a project');
        }
        $this->id           = $id;
        $this->name         = $name;
        $this->type         = $type;
        $this->projectId    = $projectId;
        $this->data         = $data;
        $this->color        = $color;
        $this->createdBy    = $createdBy;
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): StickerId { return $this->id; }
    public function getName(): StickerName { return $this->name; }
    public function getType(): StickerType { return $this->type; }
    public function getProjectId(): ?int { return $this->projectId; }
    public function getData(): ?array { return $this->data; }
    public function getColor(): ?string { return $this->color; }
    public function getCreatedBy(): UserId { return $this->createdBy; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function rename(StickerName $newName): void
    {
        $this->name = $newName;
        $this->touch();
    }

    public function changeColor(?string $color): void
    {
        $this->color = $color;
        $this->touch();
    }

    public function updateData(?array $data): void
    {
        $this->data = $data;
        $this->touch();
    }

    /**
     * @internal Используется только в репозитории, для того, чтобы установить ID после создания
     */
    public function setId(StickerId $id): void
    {
        $this->id = $id;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}