<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use modules\company\application\assembler\CompanyDtoAssembler;
use modules\company\application\command\UpdateCompanyCommand;
use modules\company\application\dto\CompanyDto;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\event\CompanyUpdatedEvent;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use RuntimeException;

class UpdateCompanyHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private CompanyDtoAssembler $companyDtoAssembler,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function handle(UpdateCompanyCommand $command): CompanyDto
    {
        if (!$this->companyAccess->canManageCompany($command->updatedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to update this company');
        }

        $company = $this->companyRepository->findById(new CompanyId($command->id));
        if ($company === null) {
            throw new RuntimeException("Company with ID {$command->id} not found");
        }

        if (!$company->isActive()) {
            throw new RuntimeException('Cannot update an archived company');
        }

        if ($command->name !== null) {
            $company->rename($command->name);
        }
        $company->updateProfile(
            $command->email,
            $command->website,
            $command->phone,
            $command->country,
            $command->city,
            $command->address,
            $command->taxNum,
            $command->registrationNum,
            $command->description,
        );

        $savedCompany = $this->companyRepository->save($company);

        $this->eventDispatcher->dispatch(new CompanyUpdatedEvent($savedCompany));

        return $this->companyDtoAssembler->toDto($savedCompany);
    }
}
