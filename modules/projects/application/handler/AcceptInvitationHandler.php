<?php

namespace modules\projects\application\handler;

use core\application\port\IUserRepository;
use core\domain\valueObject\UserId;
use modules\projects\application\command\AcceptInvitationCommand;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\InvitationAcceptedEvent;
use modules\projects\domain\repository\IInvitationRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;
use DateTimeImmutable;

class AcceptInvitationHandler
{
    private IInvitationRepository $invitationRepository;
    private IProjectUserRepository $projectUserRepository;
    private IEventDispatcher $eventDispatcher;
    private IUserRepository $userRepository;

    public function __construct(
        IInvitationRepository $invitationRepository,
        IProjectUserRepository $projectUserRepository,
        IEventDispatcher $eventDispatcher,
        IUserRepository $userRepository
    ) {
        $this->invitationRepository     = $invitationRepository;
        $this->projectUserRepository    = $projectUserRepository;
        $this->eventDispatcher          = $eventDispatcher;
        $this->userRepository          = $userRepository;
    }

    public function handle(AcceptInvitationCommand $command): void
    {
        $invitation = $this->invitationRepository->findByToken($command->token);
        if (!$invitation) {
            throw new RuntimeException('Invalid invitation token');
        }

        if (!$invitation->isPending()) {
            throw new RuntimeException('Invitation is no longer valid');
        }

        $userEmail = $this->userRepository->findById(
            new UserId($command->userId),
        )->getEmail();

        if ($userEmail !== $invitation->getEmail()) {
            throw new RuntimeException('This invitation was sent to a different email address');
        }

        $invitation->accept();
        $this->invitationRepository->save($invitation);

        $projectUser = new ProjectUser(
            $invitation->getProjectId(),
            new UserId($command->userId),
            new UserRole(UserRole::MEMBER),
            $invitation->getInvitedBy(),
            $invitation->getCreatedAt(),
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );
        $this->projectUserRepository->save($projectUser);

        $this->eventDispatcher->dispatch(new InvitationAcceptedEvent($invitation, new UserId($command->userId)));
    }
}