<?php

declare(strict_types=1);

namespace modules\company\application\handler;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\application\command\InviteUserCommand;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\entity\CompanyInvitation;
use modules\company\domain\event\CompanyInvitationCreatedEvent;
use modules\company\domain\event\IEventDispatcher;
use modules\company\domain\repository\ICompanyInvitationRepository;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyInvitationStatus;
use RuntimeException;
use Yii;
use yii\base\Exception;

class InviteUserHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyInvitationRepository $invitationRepository,
        private ICompanyAccess $companyAccess,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function handle(InviteUserCommand $command): void
    {
        if (!$this->companyAccess->canInviteUser($command->invitedBy, $command->companyId)) {
            throw new RuntimeException('You are not allowed to invite users to this company');
        }

        $company = $this->companyRepository->findById(new CompanyId($command->companyId));
        if ($company === null) {
            throw new RuntimeException("Company with ID {$command->companyId} not found");
        }

        if (!$company->isActive()) {
            throw new RuntimeException('Cannot invite to an archived company');
        }

        // Проверка на существующее приглашение
        $existing = $this->invitationRepository->findPendingByCompanyAndEmail(
            new CompanyId($command->companyId),
            $command->email,
        );

        if ($existing !== null) {
            throw new RuntimeException('A pending invitation already exists for this email');
        }

        $token = Yii::$app->security->generateRandomString(40);
        $expiresAt = new DateTimeImmutable('+7 days');

        $invitation = new CompanyInvitation(
            0,
            new CompanyId($command->companyId),
            $command->email,
            new UserId($command->invitedBy),
            $token,
            new CompanyInvitationStatus(CompanyInvitationStatus::PENDING),
            new DateTimeImmutable(),
            $expiresAt,
        );

        $this->invitationRepository->save($invitation);

        $this->eventDispatcher->dispatch(
            new CompanyInvitationCreatedEvent($command->companyId, $command->email, $command->invitedBy),
        );
    }
}
