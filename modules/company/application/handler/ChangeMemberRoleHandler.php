<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use modules\company\application\command\ChangeMemberRoleCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyMemberRole;
use RuntimeException;

class ChangeMemberRoleHandler
{
    public function __construct(
        private ICompanyMemberRepository $memberRepository,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(ChangeMemberRoleCommand $command): void
    {
        if (!$this->companyAccess->canChangeMemberRole($command->userId, $command->targetUserId, $command->companyId)) {
            throw new RuntimeException('You are not allowed to change member role');
        }

        $member = $this->memberRepository->find(
            new CompanyId($command->companyId),
            new UserId($command->targetUserId)
        );

        if ($member === null) {
            throw new RuntimeException('User is not a member of this company');
        }

        $member->changeRole(new CompanyMemberRole($command->newRole));
        $this->memberRepository->save($member);
    }
}