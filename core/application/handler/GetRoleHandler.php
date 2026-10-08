<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\dto\RoleDto;
use core\application\port\IRoleRepository;
use core\application\query\GetRoleQuery;
use core\domain\exception\EntityNotFoundException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\RoleId;

class GetRoleHandler
{
    private IRoleRepository $roleRepository;

    public function __construct(IRoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function handle(GetRoleQuery $query): RoleDto
    {
        $role = $this->roleRepository->findById(new RoleId($query->roleId));
        return RoleDto::fromEntity($role);
    }
}
