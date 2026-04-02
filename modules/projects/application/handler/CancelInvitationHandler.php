<?php

namespace modules\projects\application\handler;

use core\application\port\IPassportGateway;
use core\domain\valueObject\QueryParams;
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
    private IPassportGateway $passportGateway;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        IInvitationRepository $invitationRepository,
        IProjectAccess $projectAccess,
        IPassportGateway $passportGateway,
        IEventDispatcher $eventDispatcher
    ) {
        $this->invitationRepository = $invitationRepository;
        $this->projectAccess        = $projectAccess;
        $this->passportGateway      = $passportGateway;
        $this->eventDispatcher      = $eventDispatcher;
    }

    public function handle(CancelInvitationCommand $command): void
    {
        $projectId = new ProjectId($command->projectId);

        $isAdmin = $this->projectAccess->canInviteUser($command->cancelledBy, $command->projectId);

        if (!$isAdmin) {
            $userData = $this->passportGateway->getUserById(
                (string)$command->cancelledBy,
                $command->jwtToken,
                QueryParams::create(expand: ['contacts']
            ));
            if (!$userData) {
                throw new RuntimeException('User data not found');
            }
            $currentUserEmail = null;
            if (isset($userData['contacts']) && is_array($userData['contacts'])) {
                foreach ($userData['contacts'] as $contact) {
                    if (($contact['name'] ?? '') === 'email') {
                        $currentUserEmail = $contact['data'] ?? null;
                        break;
                    }
                }
            }
            if (!$currentUserEmail || $currentUserEmail !== $command->email) {
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