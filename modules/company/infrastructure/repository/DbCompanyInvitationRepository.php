<?php

declare(strict_types=1);

namespace modules\company\infrastructure\repository;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\domain\entity\CompanyInvitation;
use modules\company\domain\repository\ICompanyInvitationRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyInvitationStatus;
use modules\company\infrastructure\persistence\CompanyInvitationAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use Exception;
use yii\db\StaleObjectException;

class DbCompanyInvitationRepository implements ICompanyInvitationRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function save(CompanyInvitation $invitation): CompanyInvitation
    {
        $ar = $this->findARById($invitation->getId()) ?? new CompanyInvitationAR();
        $this->mapEntityToAR($invitation, $ar);

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save company invitation: ' . implode(', ', $ar->getFirstErrors()));
        }

        if ($invitation->getId() === 0) {
            $invitation->setId((int)$ar->id);
        }

        return $invitation;
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?CompanyInvitation
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findByToken(string $token): ?CompanyInvitation
    {
        $ar = CompanyInvitationAR::findOne(['token' => $token]);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findPendingByCompanyAndEmail(CompanyId $companyId, string $email): ?CompanyInvitation
    {
        $ar = CompanyInvitationAR::find()
            ->where([
                'company_id' => $companyId->value(),
                'email' => $email,
                'status' => CompanyInvitationStatus::PENDING,
            ])
            ->one();
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findPendingByEmail(string $email): array
    {
        $ars = CompanyInvitationAR::find()
            ->where([
                'email' => $email,
                'status' => CompanyInvitationStatus::PENDING,
            ])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function deleteExpired(): int
    {
        $count = CompanyInvitationAR::deleteAll(
            'status = :status AND expires_at < NOW()',
            [':status' => CompanyInvitationStatus::PENDING],
        );
        return (int)$count;
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(CompanyInvitation $invitation): void
    {
        $ar = $this->findARById($invitation->getId());
        $ar?->delete();
    }

    private function findARById(int $id): ?CompanyInvitationAR
    {
        return CompanyInvitationAR::findOne($id);
    }

    private function mapEntityToAR(CompanyInvitation $invitation, CompanyInvitationAR $ar): void
    {
        $ar->company_id = $invitation->getCompanyId()->value();
        $ar->email = $invitation->getEmail();
        $ar->invited_by = $invitation->getInvitedBy()->value();
        $ar->token = $invitation->getToken();
        $ar->status = $invitation->getStatus()->value();
        $ar->created_at = $invitation->getCreatedAt()->format('Y-m-d H:i:s');
        $ar->expires_at = $invitation->getExpiresAt()->format('Y-m-d H:i:s');
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(CompanyInvitationAR $ar): CompanyInvitation
    {
        return new CompanyInvitation(
            (int)$ar->id,
            new CompanyId((int)$ar->company_id),
            $ar->email,
            new UserId((int)$ar->invited_by),
            $ar->token,
            new CompanyInvitationStatus($ar->status),
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->expires_at),
            new DateTimeImmutable($ar->updated_at),
        );
    }
}
