<?php

declare(strict_types=1);

namespace core\application\port;

use core\domain\entity\UserRole;
use core\application\dto\UserRoleFiltersDto;

interface IUserRoleSearch
{
    /**
     * @return UserRole[]
     */
    public function findWithFilters(UserRoleFiltersDto $filters): array;

    public function countWithFilters(UserRoleFiltersDto $filters): int;
}
