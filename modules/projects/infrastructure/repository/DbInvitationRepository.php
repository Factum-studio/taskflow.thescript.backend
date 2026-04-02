<?php

namespace modules\projects\infrastructure\repository;

use DateTimeImmutable;
use Exception;
use modules\projects\domain\entity\Invitation;
use modules\projects\domain\repository\IInvitationRepository;
use modules\projects\domain\valueObject\InvitationStatus;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\infrastructure\persistence\InvitationAR;
use RuntimeException;
use yii\db\Connection;
use yii\db\StaleObjectException;

class DbInvitationRepository implements IInvitationRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function save(Invitation $invitation): Invitation
    {
        $ar = $invitation->getId() ? InvitationAR::findOne($invitation->getId()) : new InvitationAR();
        $this->mapEntityToAR($invitation, $ar);
        if (!$ar->save()) {
            throw new RuntimeException('Failed to save invitation: ' . implode(', ', $ar->getFirstErrors()));
        }
        if (!$invitation->getId()) {
            $invitation->setId((int)$ar->id);
        }
        return $invitation;
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?Invitation
    {
        $ar = InvitationAR::findOne($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findByToken(string $token): ?Invitation
    {
        $ar = InvitationAR::findOne(['token' => $token]);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findPendingByProjectAndEmail(ProjectId $projectId, string $email): ?Invitation
    {
        $ar = InvitationAR::find()
            ->where([
                'project_id' => $projectId->getValue(),
                'email' => $email,
                'status' => InvitationStatus::PENDING,
            ])
            ->andWhere(['>', 'expires_at', date('Y-m-d H:i:s')])
            ->one();
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function deleteExpired(): int
    {
        return InvitationAR::deleteAll(
            'status = :status AND expires_at < :now',
            [':status' => InvitationStatus::PENDING, ':now' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function remove(Invitation $invitation): void
    {
        $ar = InvitationAR::findOne($invitation->getId());
        $ar?->delete();
    }

    private function mapEntityToAR(Invitation $invitation, InvitationAR $ar): void
    {
        $ar->project_id = $invitation->getProjectId()->getValue();
        $ar->email = $invitation->getEmail();
        $ar->invited_by = $invitation->getInvitedBy()->getValue();
        $ar->token = $invitation->getToken();
        $ar->status = $invitation->getStatus()->getValue();
        $ar->expires_at = $invitation->getExpiresAt()->format('Y-m-d H:i:s');
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(InvitationAR $ar): Invitation
    {
        return new Invitation(
            (int)$ar->id,
            new ProjectId((int)$ar->project_id),
            $ar->email,
            new UserId((int)$ar->invited_by),
            $ar->token,
            new InvitationStatus($ar->status),
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->expires_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}