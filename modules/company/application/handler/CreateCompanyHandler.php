<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use modules\company\application\assembler\CompanyDtoAssembler;
use modules\company\application\command\CreateCompanyCommand;
use modules\company\application\dto\CompanyDto;
use modules\company\domain\entity\Company;
use modules\company\domain\entity\CompanyMember;
use modules\company\domain\event\CompanyCreatedEvent;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyMemberRole;
use DateTimeImmutable;
use RuntimeException;

class CreateCompanyHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyMemberRepository $companyMemberRepository,
        private CompanyDtoAssembler $companyDtoAssembler,
        private IEventDispatcher $eventDispatcher,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(CreateCompanyCommand $command): CompanyDto
    {
        // Проверка уникальности названия
        $existing = $this->companyRepository->findByName($command->name);
        if ($existing !== null) {
            throw new RuntimeException('Company with this name already exists');
        }

        $company = new Company(
            new CompanyId(0),
            $command->name,
            new UserId($command->ownerId),
            null,
            false,
            $command->email,
            $command->website,
            $command->phone,
            $command->country,
            $command->city,
            $command->address,
            $command->taxNum,
            $command->registrationNum,
            $command->description,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $savedCompany = $this->companyRepository->save($company);

        // Добавляем создателя как владельца (owner)
        $ownerMember = new CompanyMember(
            $savedCompany->getId(),
            new UserId($command->ownerId),
            new CompanyMemberRole(CompanyMemberRole::OWNER),
        );
        $ownerMember->accept();
        $this->companyMemberRepository->save($ownerMember);

        $this->eventDispatcher->dispatch(new CompanyCreatedEvent($savedCompany));

        return $this->companyDtoAssembler->toDto($savedCompany);
    }
}