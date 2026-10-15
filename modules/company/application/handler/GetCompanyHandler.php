<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use modules\company\application\assembler\CompanyDtoAssembler;
use modules\company\application\dto\CompanyDto;
use modules\company\application\port\ICompanyAccess;
use modules\company\application\query\GetCompanyQuery;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use RuntimeException;

class GetCompanyHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private CompanyDtoAssembler $companyDtoAssembler,
        private ICompanyAccess $companyAccess,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(GetCompanyQuery $query): CompanyDto
    {
        $company = $this->companyRepository->findById(new CompanyId($query->id));

        if ($company === null) {
            throw new RuntimeException("Company with ID {$query->id} not found");
        }

        if (!$this->companyAccess->canViewCompany($query->userId, $query->id)) {
            throw new RuntimeException('You are not allowed to view this company');
        }

        return $this->companyDtoAssembler->toDto($company);
    }
}