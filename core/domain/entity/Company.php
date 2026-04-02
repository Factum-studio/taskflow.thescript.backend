<?php
namespace core\domain\entity;

use core\domain\valueObject\CompanyId;
use core\domain\valueObject\CompanyName;
use core\domain\valueObject\CompanyDescription;
use DateTimeImmutable;

class Company
{
    private CompanyId $id;
    private CompanyName $name;
    private ?CompanyDescription $description;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        CompanyId $id,
        CompanyName $name,
        ?CompanyDescription $description = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id           = $id;
        $this->name         = $name;
        $this->description  = $description;
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): CompanyId { return $this->id; }
    public function getName(): CompanyName { return $this->name; }
    public function getDescription(): ?CompanyDescription { return $this->description; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function updateName(CompanyName $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function updateDescription(?CompanyDescription $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}