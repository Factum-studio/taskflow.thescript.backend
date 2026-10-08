<?php

declare(strict_types=1);

namespace core\application\query;

use core\application\dto\RoleFiltersDto;

class ListRoleQuery
{
    public function __construct(public RoleFiltersDto $filters)
    {
    }
}
