<?php

declare(strict_types=1);

namespace core\application\query;

use core\application\dto\UserRoleFiltersDto;

class ListUserRoleQuery
{
    public function __construct(public UserRoleFiltersDto $filters)
    {
    }
}
