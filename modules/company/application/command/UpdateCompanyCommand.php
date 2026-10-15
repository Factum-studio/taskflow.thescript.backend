<?php

declare(strict_types=1);

namespace modules\company\application\command;

class UpdateCompanyCommand
{
    public int $id;
    public int $updatedBy;
    public ?string $name;
    public ?string $email;
    public ?string $website;
    public ?string $phone;
    public ?string $country;
    public ?string $city;
    public ?string $address;
    public ?string $taxNum;
    public ?string $registrationNum;
    public ?string $description;

    public function __construct(
        int $id,
        int $updatedBy,
        ?string $name = null,
        ?string $email = null,
        ?string $website = null,
        ?string $phone = null,
        ?string $country = null,
        ?string $city = null,
        ?string $address = null,
        ?string $taxNum = null,
        ?string $registrationNum = null,
        ?string $description = null,
    ) {
        $this->id = $id;
        $this->updatedBy = $updatedBy;
        $this->name = $name;
        $this->email = $email;
        $this->website = $website;
        $this->phone = $phone;
        $this->country = $country;
        $this->city = $city;
        $this->address = $address;
        $this->taxNum = $taxNum;
        $this->registrationNum = $registrationNum;
        $this->description = $description;
    }
}