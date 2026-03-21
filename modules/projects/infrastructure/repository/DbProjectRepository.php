<?php

namespace modules\projects\infrastructure\repository;

use DateTimeImmutable;
use modules\projects\domain\entity\Project;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\ProjectType;
use modules\projects\domain\valueObject\Settings;
use modules\projects\infrastructure\persistence\ProjectAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use Exception;
use yii\db\StaleObjectException;

class DbProjectRepository implements IProjectRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function save(Project $project): Project
    {
        $ar = $this->findARById($project->getId()) ?? new ProjectAR();
        $this->mapEntityToAR($project, $ar);

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save project: ' . implode(', ', $ar->getFirstErrors()));
        }

        if (!$project->getId() || $project->getId()->getValue() !== (int)$ar->id) {
            $project->setId(new ProjectId((int)$ar->id));
        }

        return $project;
    }

    /**
     * @throws Exception
     */
    public function findById(ProjectId $id): ?Project
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findByOwner(UserId $ownerId): array
    {
        $ars = ProjectAR::find()
            ->where(['owner_id' => $ownerId->getValue()])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function findByUser(UserId $userId): array
    {
        // Проекты, где пользователь владелец ИЛИ участник
        $ars = ProjectAR::find()
            ->leftJoin('{{%project_user}}', 'project.id = project_user.project_id')
            ->where(['or',
                ['project.owner_id' => $userId->getValue()],
                ['project_user.user_id' => $userId->getValue()]
            ])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    private function findARById(ProjectId $id): ?ProjectAR
    {
        return ProjectAR::findOne($id->getValue());
    }

    public function countByOwner(UserId $ownerId): int
    {
        return ProjectAR::find()->where(['owner_id' => $ownerId->getValue()])->count();
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Project $project): void
    {
        $ar = $this->findARById($project->getId());
        $ar?->delete();
    }

    private function mapEntityToAR(Project $project, ProjectAR $ar): void
    {
        $ar->name       = $project->getName();
        $ar->type       = $project->getType()->getValue();
        $ar->owner_id   = $project->getOwnerId()->getValue();
        $ar->settings   = $project->getSettings()->toArray();
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(ProjectAR $ar): Project
    {
        return new Project(
            new ProjectId((int)$ar->id),
            $ar->name,
            new ProjectType($ar->type),
            new UserId((int)$ar->owner_id),
            new Settings($ar->settings ?? []),
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}