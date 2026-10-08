<?php

declare(strict_types=1);

namespace core\infrastructure\repository;

use core\application\dto\UserRoleFiltersDto;
use core\application\port\IUserRoleSearch;
use core\domain\entity\UserRole;
use core\domain\exception\ValidationException;
use core\domain\valueObject\IdRange;
use core\domain\valueObject\UserRoleId;
use core\domain\valueObject\UserId;
use core\domain\valueObject\RoleId;
use core\infrastructure\persistence\UserRoleAR;
use DateTimeImmutable;
use Exception;
use yii\db\Query;

class DbUserRoleSearch implements IUserRoleSearch
{
    public function findWithFilters(UserRoleFiltersDto $filters): array
    {
        $query = $this->buildFilterQuery($filters);
        $query->orderBy($filters->orderBy ?? ['id' => SORT_ASC]);
        $query->limit($filters->limit)->offset($filters->offset);
        return array_map([$this, 'hydrate'], $query->all());
    }

    public function countWithFilters(UserRoleFiltersDto $filters): int
    {
        $query = $this->buildFilterQuery($filters);
        return (int) $query->count();
    }

    private function buildFilterQuery(UserRoleFiltersDto $filters): Query
    {
        $query = (new Query())->from(UserRoleAR::tableName());

        if ($filters->ids !== null) {
            $ids = (new IdRange($filters->ids))->toArray();
            if (!empty($ids)) {
                $query->andWhere(['id' => $ids]);
            }
        }

        if ($filters->userId !== null) {
            $query->andWhere(['user_id' => $filters->userId]);
        }
        if ($filters->roleId !== null) {
            $query->andWhere(['role_id' => $filters->roleId]);
        }
        if ($filters->assignedFrom !== null) {
            $query->andWhere(['>=', 'assigned_at', $filters->assignedFrom]);
        }
        if ($filters->assignedTo !== null) {
            $query->andWhere(['<=', 'assigned_at', $filters->assignedTo]);
        }
        if ($filters->assignedBy !== null) {
            $query->andWhere(['assigned_by' => $filters->assignedBy]);
        }

        return $query;
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    private function hydrate(array $row): UserRole
    {
        return new UserRole(
            new UserRoleId((int)$row['id']),
            new UserId((int)$row['user_id']),
            new RoleId((int)$row['role_id']),
            $row['assigned_at'] ? new DateTimeImmutable($row['assigned_at']) : null,
            $row['assigned_by'] ? new UserId((int)$row['assigned_by']) : null,
        );
    }
}
