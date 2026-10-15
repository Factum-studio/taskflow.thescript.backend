<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use modules\company\application\command\TransferOwnershipCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\entity\CompanyMember;
use modules\company\domain\event\CompanyTransferOwnershipEvent;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyMemberRole;
use RuntimeException;

class TransferOwnershipHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyMemberRepository $memberRepository,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function handle(TransferOwnershipCommand $command): void
    {
        if (!$this->companyAccess->canTransferOwnership($command->userId, $command->companyId)) {
            throw new RuntimeException('You are not allowed to transfer ownership');
        }

        $company = $this->companyRepository->findById(new CompanyId($command->companyId));
        if ($company === null) {
            throw new RuntimeException("Company with ID {$command->companyId} not found");
        }

        // Проверяем, что новый владелец — участник компании
        $newOwnerMember = $this->memberRepository->find(
            new CompanyId($command->companyId),
            new UserId($command->newOwnerId),
        );

        if ($newOwnerMember === null) {
            throw new RuntimeException('New owner must be a member of the company');
        }

        $previousOwnerId = $company->getOwnerId()->value();

        // Меняем владельца в компании
        $company->transferOwnership(new UserId($command->newOwnerId));
        $this->companyRepository->save($company);

        // Старый владелец становится администратором
        $oldOwnerMember = $this->memberRepository->find(
            new CompanyId($command->companyId),
            new UserId($command->userId),
        );

        if ($oldOwnerMember !== null) {
            $oldOwnerMember->changeRole(new CompanyMemberRole(CompanyMemberRole::ADMIN));
            $this->memberRepository->save($oldOwnerMember);
        }

        // Новый владелец получает роль owner
        $newOwnerMember->changeRole(new CompanyMemberRole(CompanyMemberRole::OWNER));
        $this->memberRepository->save($newOwnerMember);

        $this->eventDispatcher->dispatch(
            new CompanyTransferOwnershipEvent($command->companyId, $previousOwnerId, $command->newOwnerId),
        );
    }
}
