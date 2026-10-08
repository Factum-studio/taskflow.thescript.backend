<?php

declare(strict_types=1);

namespace modules\rbac\infrastructure\repository;

use modules\rbac\application\port\IRoleAssignmentPolicy;
use yii\db\Query;

final class DbRoleAssignmentPolicy implements IRoleAssignmentPolicy
{
    /** @param array<string, int> $roleRanks */
    public function __construct(
        private readonly array $roleRanks,
    ) {
    }

    public function canAssign(int $actorUserId, int $targetUserId, int $targetRoleId): bool
    {
        if ($actorUserId === $targetUserId) {
            return false;
        }

        $actorRank = $this->getMaxRankForUser($actorUserId);
        $targetRank = $this->getRoleRank($targetRoleId);

        return $actorRank !== null && $targetRank !== null && $actorRank > $targetRank;
    }

    public function canRemove(int $actorUserId, int $userRoleId): bool
    {
        $assignment = (new Query())
            ->select(['user_id', 'role_id'])
            ->from('{{%user_roles}}')
            ->where(['id' => $userRoleId])
            ->one();

        if ($assignment === false || (int)$assignment['user_id'] === $actorUserId) {
            return false;
        }

        $actorRank = $this->getMaxRankForUser($actorUserId);
        $targetRank = $this->getRoleRank((int)$assignment['role_id']);

        return $actorRank !== null && $targetRank !== null && $actorRank > $targetRank;
    }

    private function getMaxRankForUser(int $userId): ?int
    {
        $roleNames = (new Query())
            ->select(['r.name'])
            ->from(['ur' => '{{%user_roles}}'])
            ->innerJoin(['r' => '{{%roles}}'], 'r.id = ur.role_id')
            ->where(['ur.user_id' => $userId])
            ->column();

        $ranks = [];
        foreach ($roleNames as $roleName) {
            if (isset($this->roleRanks[(string)$roleName])) {
                $ranks[] = $this->roleRanks[(string)$roleName];
            }
        }

        return $ranks === [] ? null : max($ranks);
    }

    private function getRoleRank(int $roleId): ?int
    {
        $roleName = (new Query())
            ->select('name')
            ->from('{{%roles}}')
            ->where(['id' => $roleId])
            ->scalar();

        return $roleName === false || $roleName === null
            ? null
            : ($this->roleRanks[(string)$roleName] ?? null);
    }
}
