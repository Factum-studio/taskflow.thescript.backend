<?php

declare(strict_types=1);

namespace core\domain\entity;

use core\domain\valueObject\RoleId;
use core\domain\valueObject\RoleName;
use DateTimeImmutable;
use Exception;

class Role
{
    private RoleId $id;
    private RoleName $name;
    private ?string $description;
    private DateTimeImmutable $createdAt;

    /**
     * @throws Exception
     */
    public function __construct(
        RoleId $id,
        RoleName $name,
        ?string $description = null,
        string $createdAt = null,
    ) {
        $this->id           = $id;
        $this->name         = $name;
        $this->description  = $description;
        $this->createdAt    = $createdAt !== null ? new DateTimeImmutable($createdAt) : new DateTimeImmutable();
    }

    public function getId(): RoleId
    {
        return $this->id;
    }

    public function getName(): RoleName
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function rename(RoleName $name): void
    {
        $this->name = $name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @internal Используется только репозиторием для установки ID после сохранения
     */
    public function setId(RoleId $id): void
    {
        $this->id = $id;
    }
}
