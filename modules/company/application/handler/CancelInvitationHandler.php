<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use modules\company\application\command\CancelInvitationCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyInvitationRepository;
use RuntimeException;

class CancelInvitationHandler
{
    public function __construct(
        private ICompanyInvitationRepository $invitationRepository,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    public function handle(CancelInvitationCommand $command): void
    {
        $invitation = $this->invitationRepository->findById($command->invitationId);
        if ($invitation === null) {
            throw new RuntimeException('Invitation not found');
        }

        if (!$this->companyAccess->canInviteUser($command->userId, $command->companyId)) {
            throw new RuntimeException('You are not allowed to cancel invitations');
        }

        $invitation->cancel();
        $this->invitationRepository->save($invitation);
    }
}
