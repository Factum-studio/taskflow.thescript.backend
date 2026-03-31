<?php

namespace modules\projects\application\handler;

use modules\projects\application\command\CancelInvitationCommand;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\InvitationCancelledEvent;
use modules\projects\domain\repository\IInvitationRepository;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;

class CancelInvitationHandler
{
    private IInvitationRepository $invitationRepository;
    private IProjectAccess $projectAccess;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        IInvitationRepository $invitationRepository,
        IProjectAccess $projectAccess,
        IEventDispatcher $eventDispatcher
    ) {
        $this->invitationRepository = $invitationRepository;
        $this->projectAccess        = $projectAccess;
        $this->eventDispatcher      = $eventDispatcher;
    }

    public function handle(CancelInvitationCommand $command): void
    {
        if (!$this->projectAccess->canInviteUser($command->cancelledBy, $command->projectId)) {
            throw new RuntimeException('You are not allowed to cancel invitations in this project');
        }

        $invitation = $this->invitationRepository->findPendingByProjectAndEmail(
            new ProjectId($command->projectId),
            $command->email
        );
        if (!$invitation) {
            throw new RuntimeException('No pending invitation found for this email');
        }

        $invitation->cancel();
        $this->invitationRepository->save($invitation);

        $this->eventDispatcher->dispatch(new InvitationCancelledEvent($invitation, $command->cancelledBy));
    }
}