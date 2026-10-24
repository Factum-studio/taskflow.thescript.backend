<?php

declare(strict_types=1);

namespace modules\projects\application\handler;

use core\application\port\IUserRepository;
use core\domain\valueObject\UserId;
use modules\projects\application\command\CancelInvitationCommand;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\InvitationCancelledEvent;
use modules\projects\domain\repository\IInvitationRepository;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;

class CancelInvitationHandler
{
    private IInvitationRepository $invitationRepository;
    private IProjectAccessService $projectAccess;
    private IUserRepository $userRepository;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        IInvitationRepository $invitationRepository,
        IProjectAccessService $projectAccess,
        IUserRepository $userRepository,
        IEventDispatcher $eventDispatcher,
    ) {
        $this->invitationRepository = $invitationRepository;
        $this->projectAccess        = $projectAccess;
        $this->userRepository       = $userRepository;
        $this->eventDispatcher      = $eventDispatcher;
    }

    public function handle(CancelInvitationCommand $command): void
    {
        $projectId = new ProjectId($command->projectId);

        $isAdmin = $this->projectAccess->canManageProject($command->cancelledBy, $command->projectId);

        if (!$isAdmin) {
            $currentUserEmail = $this->userRepository->findById(
                new UserId($command->cancelledBy),
            )->getEmail();
            if ($currentUserEmail->value() !== $command->email) {
                throw new RuntimeException('You are not allowed to cancel this invitation');
            }
        }

        $invitation = $this->invitationRepository->findPendingByProjectAndEmail($projectId, $command->email);
        if (!$invitation) {
            throw new RuntimeException('No pending invitation found for this email');
        }

        $invitation->cancel();
        $this->invitationRepository->save($invitation);

        $this->eventDispatcher->dispatch(new InvitationCancelledEvent($invitation, $command->cancelledBy));
    }
}
