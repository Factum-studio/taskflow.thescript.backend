<?php

namespace modules\projects\application\handler;

use core\application\port\IPassportGateway;
use core\domain\valueObject\QueryParams;
use modules\projects\application\command\AcceptInvitationCommand;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\InvitationAcceptedEvent;
use modules\projects\domain\repository\IInvitationRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;
use DateTimeImmutable;

class AcceptInvitationHandler
{
    private IInvitationRepository $invitationRepository;
    private IProjectUserRepository $projectUserRepository;
    private IEventDispatcher $eventDispatcher;
    private IPassportGateway $passportGateway;

    public function __construct(
        IInvitationRepository $invitationRepository,
        IProjectUserRepository $projectUserRepository,
        IEventDispatcher $eventDispatcher,
        IPassportGateway $passportGateway
    ) {
        $this->invitationRepository     = $invitationRepository;
        $this->projectUserRepository    = $projectUserRepository;
        $this->eventDispatcher          = $eventDispatcher;
        $this->passportGateway          = $passportGateway;
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

        $userData = $this->passportGateway->getUserById(
            (string)$command->userId,
            $command->jwtToken,
            QueryParams::create(expand: ['contacts'])
        );
        if (!$userData) {
            throw new RuntimeException('User not found in Passport');
        }

        $userEmail = null;
        if (isset($userData['contacts']) && is_array($userData['contacts'])) {
            foreach ($userData['contacts'] as $contact) {
                if (($contact['name'] ?? '') === 'email') {
                    $userEmail = $contact['data'] ?? null;
                    break;
                }
            }
        }

        if (!$userEmail || $userEmail !== $invitation->getEmail()) {
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