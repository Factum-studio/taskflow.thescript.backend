<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use modules\company\application\command\UnarchiveCompanyCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use RuntimeException;

class UnarchiveCompanyHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(UnarchiveCompanyCommand $command): void
    {
        if (!$this->companyAccess->canArchiveCompany($command->userId, $command->id)) {
            throw new RuntimeException('You are not allowed to unarchive this company');
        }

        $company = $this->companyRepository->findById(new CompanyId($command->id));
        if ($company === null) {
            throw new RuntimeException("Company with ID {$command->id} not found");
        }

        $company->unarchive();
        $this->companyRepository->save($company);
    }
}