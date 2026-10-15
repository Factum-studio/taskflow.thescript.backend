<?php

declare(strict_types=1);

namespace modules\company\application\assembler;

use modules\company\domain\entity\Company;
use modules\company\application\dto\CompanyDto;

class CompanyDtoAssembler
{
    public function toDto(Company $company): CompanyDto
    {
        return new CompanyDto([
            'id' => $company->getId()->value(),
            'name' => $company->getName(),
            'ownerId' => $company->getOwnerId()->value(),
            'status' => $company->getStatus()->value(),
            'verified' => $company->isVerified(),
            'email' => $company->getEmail(),
            'website' => $company->getWebsite(),
            'phone' => $company->getPhone(),
            'country' => $company->getCountry(),
            'city' => $company->getCity(),
            'address' => $company->getAddress(),
            'taxNum' => $company->getTaxNum(),
            'registrationNum' => $company->getRegistrationNum(),
            'description' => $company->getDescription(),
            'createdAt' => $company->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $company->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Company[] $companies
     * @return CompanyDto[]
     */
    public function toDtoList(array $companies): array
    {
        return array_map([$this, 'toDto'], $companies);
    }
}
