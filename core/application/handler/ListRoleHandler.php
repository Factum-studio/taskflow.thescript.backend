<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\dto\RoleDto;
use core\application\port\IRoleRepository;
use core\application\query\ListRoleQuery;

class ListRoleHandler
{
    private IRoleRepository $roleRepository;

    public function __construct(IRoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param ListRoleQuery $query
     * @return RoleDto[]
     */
    public function handle(ListRoleQuery $query): array
    {
        $roles = $this->roleRepository->findWithFilters($query->filters);
        return array_map([RoleDto::class, 'fromEntity'], $roles);
    }
}
