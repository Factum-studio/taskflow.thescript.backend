<?php
namespace core\application\handler;

use core\application\dto\CompanyDto;
use core\application\port\ICompanyRepository;
use core\application\query\GetAllCompaniesQuery;

class GetAllCompaniesHandler
{
    public function __construct(private ICompanyRepository $companyRepository) {}

    /**
     * @return CompanyDto[]
     */
    public function handle(GetAllCompaniesQuery $query): array
    {
        $companies = $this->companyRepository->findAll();
        return array_map([CompanyDto::class, 'fromEntity'], $companies);
    }
}