<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use modules\company\application\command\ArchiveCompanyCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\event\CompanyArchivedEvent;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use RuntimeException;

class ArchiveCompanyHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function handle(ArchiveCompanyCommand $command): void
    {
        if (!$this->companyAccess->canArchiveCompany($command->userId, $command->id)) {
            throw new RuntimeException('You are not allowed to archive this company');
        }

        $company = $this->companyRepository->findById(new CompanyId($command->id));
        if ($company === null) {
            throw new RuntimeException("Company with ID {$command->id} not found");
        }

        $company->archive();
        $this->companyRepository->save($company);

        $this->eventDispatcher->dispatch(new CompanyArchivedEvent($company));
    }
}
