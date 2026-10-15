<?php

declare(strict_types=1);

namespace modules\company\application\dto;

class CompanyDto
{
    public int $id;
    public string $name;
    public int $ownerId;
    public string $status;
    public bool $verified;
    public ?string $email;
    public ?string $website;
    public ?string $phone;
    public ?string $country;
    public ?string $city;
    public ?string $address;
    public ?string $taxNum;
    public ?string $registrationNum;
    public ?string $description;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->ownerId = $data['ownerId'];
        $this->status = $data['status'];
        $this->verified = $data['verified'];
        $this->email = $data['email'] ?? null;
        $this->website = $data['website'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->country = $data['country'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->taxNum = $data['taxNum'] ?? null;
        $this->registrationNum = $data['registrationNum'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->createdAt = $data['createdAt'];
        $this->updatedAt = $data['updatedAt'];
    }
}