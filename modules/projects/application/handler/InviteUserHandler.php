<?php

namespace modules\projects\application\handler;

use core\application\port\IPassportGateway;
use core\domain\valueObject\JwtToken;
use modules\projects\application\command\InviteUserCommand;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\entity\Invitation;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\InvitationCreatedEvent;
use modules\projects\domain\repository\IInvitationRepository;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\InvitationStatus;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use RuntimeException;
use DateTimeImmutable;

class InviteUserHandler
{
    private IInvitationRepository $invitationRepository;
    private IProjectRepository $projectRepository;
    private IProjectAccess $projectAccess;
    private IEventDispatcher $eventDispatcher;
    private IPassportGateway $passportGateway;

    public function __construct(
        IInvitationRepository $invitationRepository,
        IProjectRepository $projectRepository,
        IProjectAccess $projectAccess,
        IEventDispatcher $eventDispatcher,
        IPassportGateway $passportGateway
    ) {
        $this->invitationRepository = $invitationRepository;
        $this->projectRepository    = $projectRepository;
        $this->projectAccess        = $projectAccess;
        $this->eventDispatcher      = $eventDispatcher;
        $this->passportGateway      = $passportGateway;
    }

    public function handle(InviteUserCommand $command): void
    {
        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);
        if (!$project) {
            throw new RuntimeException("Project with ID {$command->projectId} not found");
        }

        if (!$this->projectAccess->canInviteUser($command->invitedBy, $command->projectId)) {
            throw new RuntimeException('You are not allowed to invite users to this project');
        }

        if ($this->projectAccess->canViewProject($command->invitedBy, $command->projectId)) {
            throw new RuntimeException('User is already a member of this project');
        }

        $existingInvitation = $this->invitationRepository->findPendingByProjectAndEmail($projectId, $command->email);
        if ($existingInvitation) {
            throw new RuntimeException('An invitation has already been sent to this email');
        }

        $userData = $this->passportGateway->findUserByEmail($command->email, $command->jwtToken);
        if (!$userData) {
            throw new RuntimeException("User with email {$command->email} not found in Passport");
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = new DateTimeImmutable('+7 days');

        $invitation = new Invitation(
            0,
            $projectId,
            $command->email,
            new UserId($command->invitedBy),
            $token,
            new InvitationStatus(InvitationStatus::PENDING),
            new DateTimeImmutable(),
            $expiresAt
        );

        $this->invitationRepository->save($invitation);

        $this->eventDispatcher->dispatch(new InvitationCreatedEvent($invitation));
    }
}