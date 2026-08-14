<?php

namespace modules\projects\infrastructure\repository;

use DateTimeImmutable;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use modules\projects\infrastructure\persistence\ProjectUserAR;
use RuntimeException;
use yii\db\Connection;
use Exception;
use yii\db\StaleObjectException;

class DbProjectUserRepository implements IProjectUserRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function save(ProjectUser $projectUser): void
    {
        $ar = $this->findAR($projectUser->getProjectId(), $projectUser->getUserId()) ?? new ProjectUserAR();
        $this->mapEntityToAR($projectUser, $ar);

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save project user: ' . implode(', ', $ar->getFirstErrors()));
        }
    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function remove(ProjectUser $projectUser): void
    {
        $ar = $this->findAR($projectUser->getProjectId(), $projectUser->getUserId());
        $ar?->delete();
    }

    /**
     * @throws Exception
     */
    public function find(ProjectId $projectId, UserId $userId): ?ProjectUser
    {
        $ar = $this->findAR($projectId, $userId);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findByProject(ProjectId $projectId): array
    {
        $ars = ProjectUserAR::find()
            ->where(['project_id' => $projectId->getValue()])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function findByUser(UserId $userId): array
    {
        $ars = ProjectUserAR::find()
            ->where(['user_id' => $userId->getValue()])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    private function findAR(ProjectId $projectId, UserId $userId): ?ProjectUserAR
    {
        return ProjectUserAR::findOne([
            'project_id' => $projectId->getValue(),
            'user_id' => $userId->getValue(),
        ]);
    }

    private function mapEntityToAR(ProjectUser $projectUser, ProjectUserAR $ar): void
    {
        $ar->project_id     = $projectUser->getProjectId()->getValue();
        $ar->user_id        = $projectUser->getUserId()->getValue();
        $ar->role           = $projectUser->getRole()->getValue();
        $ar->invited_by     = $projectUser->getInvitedBy()?->getValue();
        $ar->invited_at     = $projectUser->getInvitedAt()?->format('Y-m-d H:i:s');
        $ar->accepted_at    = $projectUser->getAcceptedAt()?->format('Y-m-d H:i:s');
        $ar->joined_at      = $projectUser->getJoinedAt()->format('Y-m-d H:i:s');
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(ProjectUserAR $ar): ProjectUser
    {
        return new ProjectUser(
            new ProjectId((int)$ar->project_id),
            new UserId((int)$ar->user_id),
            new UserRole($ar->role),
            $ar->invited_by ? new UserId((int)$ar->invited_by) : null,
            $ar->invited_at ? new DateTimeImmutable($ar->invited_at) : null,
            $ar->accepted_at ? new DateTimeImmutable($ar->accepted_at) : null,
            $ar->joined_at ? new DateTimeImmutable($ar->joined_at) : null
        );
    }
}