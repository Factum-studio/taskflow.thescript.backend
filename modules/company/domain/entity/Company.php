<?php

declare(strict_types=1);

namespace modules\company\domain\entity;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyStatus;
use InvalidArgumentException;

class Company
{
    private CompanyId $id;
    private string $name;
    private UserId $ownerId;
    private CompanyStatus $status;
    private bool $verified;
    private ?string $email;
    private ?string $website;
    private ?string $phone;
    private ?string $country;
    private ?string $city;
    private ?string $address;
    private ?string $taxNum;
    private ?string $registrationNum;
    private ?string $description;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        CompanyId $id,
        string $name,
        UserId $ownerId,
        ?CompanyStatus $status = null,
        bool $verified = false,
        ?string $email = null,
        ?string $website = null,
        ?string $phone = null,
        ?string $country = null,
        ?string $city = null,
        ?string $address = null,
        ?string $taxNum = null,
        ?string $registrationNum = null,
        ?string $description = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->setName($name);
        $this->id = $id;
        $this->ownerId = $ownerId;
        $this->status = $status ?? new CompanyStatus(CompanyStatus::ACTIVE);
        $this->verified = $verified;
        $this->email = $email;
        $this->website = $website;
        $this->phone = $phone;
        $this->country = $country;
        $this->city = $city;
        $this->address = $address;
        $this->taxNum = $taxNum;
        $this->registrationNum = $registrationNum;
        $this->description = $description;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    // -------- Getters --------

    public function getId(): CompanyId { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getOwnerId(): UserId { return $this->ownerId; }
    public function getStatus(): CompanyStatus { return $this->status; }
    public function isVerified(): bool { return $this->verified; }
    public function getEmail(): ?string { return $this->email; }
    public function getWebsite(): ?string { return $this->website; }
    public function getPhone(): ?string { return $this->phone; }
    public function getCountry(): ?string { return $this->country; }
    public function getCity(): ?string { return $this->city; }
    public function getAddress(): ?string { return $this->address; }
    public function getTaxNum(): ?string { return $this->taxNum; }
    public function getRegistrationNum(): ?string { return $this->registrationNum; }
    public function getDescription(): ?string { return $this->description; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    // -------- Mutators --------

    public function rename(string $newName): void
    {
        $this->setName($newName);
        $this->touch();
    }

    public function updateProfile(
        ?string $email,
        ?string $website,
        ?string $phone,
        ?string $country,
        ?string $city,
        ?string $address,
        ?string $taxNum,
        ?string $registrationNum,
        ?string $description,
    ): void {
        $this->email = $email;
        $this->website = $website;
        $this->phone = $phone;
        $this->country = $country;
        $this->city = $city;
        $this->address = $address;
        $this->taxNum = $taxNum;
        $this->registrationNum = $registrationNum;
        $this->description = $description;
        $this->touch();
    }

    public function archive(): void
    {
        if ($this->status->isArchived()) {
            throw new InvalidArgumentException('Company is already archived');
        }
        $this->status = new CompanyStatus(CompanyStatus::ARCHIVED);
        $this->touch();
    }

    public function unarchive(): void
    {
        if ($this->status->isActive()) {
            throw new InvalidArgumentException('Company is already active');
        }
        $this->status = new CompanyStatus(CompanyStatus::ACTIVE);
        $this->touch();
    }

    public function verify(): void
    {
        $this->verified = true;
        $this->touch();
    }

    public function transferOwnership(UserId $newOwnerId): void
    {
        $this->ownerId = $newOwnerId;
        $this->touch();
    }

    public function isOwner(UserId $userId): bool
    {
        return $this->ownerId->value() === $userId->value();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    // -------- Internal --------

    /**
     * @internal For repository use only
     */
    public function setId(CompanyId $id): void
    {
        $this->id = $id;
    }

    private function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Company name cannot be empty');
        }
        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Company name must not exceed 255 characters');
        }
        $this->name = $name;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}