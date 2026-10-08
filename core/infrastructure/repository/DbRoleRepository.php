<?php

declare(strict_types=1);

namespace core\infrastructure\repository;

use core\application\dto\RoleFiltersDto;
use core\application\port\IRoleRepository;
use core\domain\entity\Role;
use core\domain\exception\EntityNotFoundException;
use core\domain\exception\LogicException;
use core\domain\exception\RuntimeException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\RoleId;
use core\domain\valueObject\RoleName;
use core\domain\valueObject\IdRange;
use core\infrastructure\persistence\RoleAR;
use yii\db\Exception;
use yii\db\Query;

class DbRoleRepository implements IRoleRepository
{
    /**
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function findById(RoleId $id): Role
    {
        $row = (new Query())->from(RoleAR::tableName())->where(['id' => $id->value()])->one();
        if (!$row) {
            throw new EntityNotFoundException('Role not found.');
        }
        return $this->hydrate($row);
    }

    /**
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function findByName(RoleName $name): Role
    {
        $row = (new Query())->from(RoleAR::tableName())->where(['name' => $name->value()])->one();
        if (!$row) {
            throw new EntityNotFoundException('Role not found.');
        }
        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        $rows = (new Query())->from(RoleAR::tableName())->orderBy(['name' => SORT_ASC])->all();
        return array_map([$this, 'hydrate'], $rows);
    }

    public function findWithFilters(RoleFiltersDto $filters): array
    {
        $query = $this->buildFilterQuery($filters);
        $query->orderBy($filters->orderBy ?? ['id' => SORT_ASC]);
        $query->limit($filters->limit)->offset($filters->offset);
        return array_map([$this, 'hydrate'], $query->all());
    }

    public function countWithFilters(RoleFiltersDto $filters): int
    {
        $query = $this->buildFilterQuery($filters);
        return (int) $query->count();
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws ValidationException
     * @throws LogicException
     */
    public function save(Role $role): void
    {
        $ar                 = $this->findOrCreateAR($role->getId());
        $isNew              = $ar->isNewRecord;
        $ar->name           = $role->getName()->value();
        $ar->description    = $role->getDescription();

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save role: ', $ar->getErrors());
        }
        if ($isNew) {
            if (!$role->getId()->isNew()) {
                throw new LogicException('New entity must have ID = 0');
            }
            $role->setId(new RoleId($ar->id));
        }
    }

    public function delete(RoleId $id): void
    {
        RoleAR::deleteAll(['id' => $id->value()]);
    }

    private function buildFilterQuery(RoleFiltersDto $filters): Query
    {
        $query = (new Query())
            ->from(RoleAR::tableName());
        if ($filters->ids !== null) {
            $ids = (new IdRange($filters->ids))->toArray();
            if (!empty($ids)) {
                $query->andWhere(['id' => $ids]);
            }
        }

        if ($filters->name !== null) {
            $query->andWhere(['like', 'name', $filters->name]);
        }

        if ($filters->description !== null) {
            $query->andWhere(['like', 'description', $filters->description]);
        }

        if ($filters->createdFrom !== null) {
            $query->andWhere(['>=', 'created_at', $filters->createdFrom]);
        }

        if ($filters->createdTo !== null) {
            $query->andWhere(['<=', 'created_at', $filters->createdTo]);
        }
        return $query;
    }

    private function findOrCreateAR(RoleId $id): RoleAR
    {
        $ar = RoleAR::findOne(['id' => $id->value()]);
        return $ar ?? new RoleAR();
    }

    /**
     * @throws ValidationException
     */
    private function hydrate(array $row): Role
    {
        return new Role(
            new RoleId((int) $row['id']),
            new RoleName($row['name']),
            $row['description'] ?? null,
            $row['created_at'],
        );
    }
}
