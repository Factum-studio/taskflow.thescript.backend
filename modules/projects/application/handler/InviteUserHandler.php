<?php

namespace modules\projects\application\handler;

use core\application\port\IUserRepository;
use core\domain\valueObject\Email;
use core\domain\valueObject\UserId;
use modules\projects\application\command\InviteUserCommand;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\entity\Invitation;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\InvitationCreatedEvent;
use modules\projects\domain\repository\IInvitationRepository;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\InvitationStatus;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;
use DateTimeImmutable;

class InviteUserHandler
{
    private IInvitationRepository $invitationRepository;
    private IProjectRepository $projectRepository;
    private IProjectAccess $projectAccess;
    private IEventDispatcher $eventDispatcher;
    private IUserRepository $userRepository;

    public function __construct(
        IInvitationRepository $invitationRepository,
        IProjectRepository $projectRepository,
        IProjectAccess $projectAccess,
        IEventDispatcher $eventDispatcher,
        IUserRepository $userRepository,
    ) {
        $this->invitationRepository = $invitationRepository;
        $this->projectRepository    = $projectRepository;
        $this->projectAccess        = $projectAccess;
        $this->eventDispatcher      = $eventDispatcher;
        $this->userRepository       = $userRepository;
    }

    /**
     * @throws \Exception
     */
    public function handle(InviteUserCommand $command): void
    {
        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);
        if (!$project) {
            throw new RuntimeException("Project with ID {$command->projectId} not found");
        }

        if (!$this->projectAccess->canInviteUser($command->invitedBy, $command->userId, $command->projectId)) {
            throw new RuntimeException('You are not allowed to invite users to this project');
        }

        if ($command->userId === null && $command->email === null) {
            throw new RuntimeException('Either email or userId must be provided');
        }

        if ($command->userId !== null) {
            $user = $this->userRepository->findById(new UserId($command->userId));
        } else {
            $user = $this->userRepository->findByEmail(new Email($command->email));
        }

        $userId    = $user->getId()->value();
        $userEmail = $user->getEmail()->value();

        if ($this->projectAccess->canViewProject($userId, $command->projectId)) {
            throw new RuntimeException('User is already a member of this project');
        }

        $existingInvitation = $this->invitationRepository->findPendingByProjectAndEmail($projectId, $userEmail);
        if ($existingInvitation) {
            throw new RuntimeException('An invitation has already been sent to this email');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = new DateTimeImmutable('+7 days');

        $invitation = new Invitation(
            0,
            $projectId,
            $userEmail,
            new UserId($command->invitedBy),
            $token,
            new InvitationStatus(InvitationStatus::PENDING),
            new DateTimeImmutable(),
            $expiresAt,
        );

        $this->invitationRepository->save($invitation);

        $this->eventDispatcher->dispatch(new InvitationCreatedEvent($invitation));
    }
}