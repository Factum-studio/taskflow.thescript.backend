<?php

declare(strict_types=1);

namespace core\infrastructure\repository;

use core\application\port\IUserRoleRepository;
use core\domain\entity\UserRole;
use core\domain\exception\RuntimeException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\UserRoleId;
use core\infrastructure\persistence\UserRoleAR;
use yii\db\Exception;

class DbUserRoleRepository implements IUserRoleRepository
{
    /**
     * @throws RuntimeException
     * @throws Exception
     * @throws ValidationException
     */
    public function sync(UserRole $role): void
    {
        $ar = UserRoleAR::findOne([
            'user_id' => $role->getUserId()->value(),
            'role_id' => $role->getRoleId()->value(),
        ]);

        if ($ar === null) {
            $this->save($role);
            return;
        }

        if ($role->getId()->isNew()) {
            $role->setId(new UserRoleId((int)$ar->id));
        }
    }

    /**
     * @throws RuntimeException
     * @throws Exception
     * @throws ValidationException
     */
    public function save(UserRole $role): void
    {
        $ar = $role->getId()->isNew()
            ? new UserRoleAR()
            : UserRoleAR::findOne(['id' => $role->getId()->value()]);

        if ($ar === null) {
            throw new RuntimeException('User role assignment not found: ' . $role->getId()->value());
        }

        $ar->user_id = $role->getUserId()->value();
        $ar->role_id = $role->getRoleId()->value();
        $ar->assigned_at = $role->getAssignedAt()->format('Y-m-d H:i:s');
        $ar->assigned_by = $role->getAssignedBy()?->value();

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save user role: ', $ar->getErrors());
        }

        if ($role->getId()->isNew()) {
            $role->setId(new UserRoleId((int)$ar->id));
        }
    }

    /**
     * @throws RuntimeException
     */
    public function delete(UserRoleId $id): void
    {
        if (UserRoleAR::deleteAll(['id' => $id->value()]) === 0) {
            throw new RuntimeException('User role assignment not found: ' . $id->value());
        }
    }
}
