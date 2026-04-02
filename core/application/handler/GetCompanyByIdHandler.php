<?php
namespace core\application\handler;

use core\application\dto\CompanyDto;
use core\application\port\ICompanyRepository;
use core\application\query\GetCompanyByIdQuery;
use core\domain\valueObject\CompanyId;
use RuntimeException;

class GetCompanyByIdHandler
{
    public function __construct(private ICompanyRepository $companyRepository) {}

    public function handle(GetCompanyByIdQuery $query): CompanyDto
    {
        $companyId = new CompanyId($query->id);
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new RuntimeException("Company with ID {$query->id} not found");
        }
        return CompanyDto::fromEntity($company);
    }
}