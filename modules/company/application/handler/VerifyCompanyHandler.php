<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use modules\company\application\command\VerifyCompanyCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\event\CompanyVerifiedEvent;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use RuntimeException;

class VerifyCompanyHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(VerifyCompanyCommand $command): void
    {
        if (!$this->companyAccess->canVerifyCompany($command->userId)) {
            throw new RuntimeException('You are not allowed to verify companies');
        }

        $company = $this->companyRepository->findById(new CompanyId($command->id));
        if ($company === null) {
            throw new RuntimeException("Company with ID {$command->id} not found");
        }

        $company->verify();
        $this->companyRepository->save($company);

        $this->eventDispatcher->dispatch(new CompanyVerifiedEvent($company));
    }
}