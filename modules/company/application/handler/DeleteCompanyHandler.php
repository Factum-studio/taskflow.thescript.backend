<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use modules\company\application\command\DeleteCompanyCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use RuntimeException;

class DeleteCompanyHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyAccess $companyAccess,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(DeleteCompanyCommand $command): void
    {
        if (!$this->companyAccess->canDeleteCompany($command->userId, $command->id)) {
            throw new RuntimeException('You are not allowed to delete this company');
        }

        $company = $this->companyRepository->findById(new CompanyId($command->id));
        if ($company === null) {
            throw new RuntimeException("Company with ID {$command->id} not found");
        }

        $this->companyRepository->remove($company);
    }
}