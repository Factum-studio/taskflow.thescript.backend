<?php

declare(strict_types=1);

namespace core\application\port;

use core\domain\entity\UserRole;
use core\domain\valueObject\UserRoleId;

interface IUserRoleRepository
{
    public function sync(UserRole $role): void;
    public function save(UserRole $role): void;
    public function delete(UserRoleId $id): void;
}
