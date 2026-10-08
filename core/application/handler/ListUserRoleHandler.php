<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\dto\UserRoleDto;
use core\application\port\IUserRoleSearch;
use core\application\query\ListUserRoleQuery;

class ListUserRoleHandler
{
    private IUserRoleSearch $roleSearch;

    public function __construct(IUserRoleSearch $roleSearch)
    {
        $this->roleSearch = $roleSearch;
    }

    /**
     * @param ListUserRoleQuery $query
     * @return UserRoleDto[]
     */
    public function handle(ListUserRoleQuery $query): array
    {
        $roles = $this->roleSearch->findWithFilters($query->filters);
        return array_map([UserRoleDto::class, 'fromEntity'], $roles);
    }
}
