<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\application\command\AcceptInvitationCommand;
use modules\company\domain\entity\CompanyMember;
use modules\company\domain\event\CompanyInvitationAcceptedEvent;
use modules\company\domain\event\CompanyMemberAddedEvent;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyInvitationRepository;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\valueObject\CompanyMemberRole;
use RuntimeException;

class AcceptInvitationHandler
{
    public function __construct(
        private ICompanyInvitationRepository $invitationRepository,
        private ICompanyMemberRepository $memberRepository,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function handle(AcceptInvitationCommand $command): void
    {
        $invitation = $this->invitationRepository->findByToken($command->token);
        if ($invitation === null) {
            throw new RuntimeException('Invitation not found');
        }

        $invitation->accept();
        $this->invitationRepository->save($invitation);

        // Добавляем пользователя в компанию как участника
        $member = new CompanyMember(
            $invitation->getCompanyId(),
            new UserId($command->userId),
            new CompanyMemberRole(CompanyMemberRole::MEMBER),
            $invitation->getInvitedBy(),
            $invitation->getCreatedAt(),
            new DateTimeImmutable(),
        );
        $this->memberRepository->save($member);

        $this->eventDispatcher->dispatch(
            new CompanyInvitationAcceptedEvent(
                $invitation->getCompanyId()->value(),
                $invitation->getEmail(),
                $command->userId,
            ),
        );

        $this->eventDispatcher->dispatch(
            new CompanyMemberAddedEvent(
                $invitation->getCompanyId()->value(),
                $command->userId,
                CompanyMemberRole::MEMBER,
            ),
        );
    }
}
