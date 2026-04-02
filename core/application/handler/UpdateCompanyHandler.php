<?php
namespace core\application\handler;

use core\application\command\UpdateCompanyCommand;
use core\application\port\ICompanyRepository;
use core\domain\valueObject\CompanyId;
use core\domain\valueObject\CompanyName;
use core\domain\valueObject\CompanyDescription;
use RuntimeException;

class UpdateCompanyHandler
{
    public function __construct(private ICompanyRepository $companyRepository) {}

    public function handle(UpdateCompanyCommand $command): void
    {
        $companyId = new CompanyId($command->id);
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new RuntimeException("Company with ID {$command->id} not found");
        }

        if ($command->name !== null) {
            $company->updateName(new CompanyName($command->name));
        }
        if ($command->description !== null) {
            $company->updateDescription(new CompanyDescription($command->description));
        }

        $this->companyRepository->save($company);
    }
}