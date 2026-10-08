<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\command\CreateRoleCommand;
use core\application\dto\RoleDto;
use core\application\port\IRoleRepository;
use core\domain\entity\Role;
use core\domain\exception\ValidationException;
use core\domain\valueObject\RoleId;
use core\domain\valueObject\RoleName;

class CreateRoleHandler
{
    private IRoleRepository $roleRepository;

    public function __construct(IRoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @throws ValidationException
     */
    public function handle(CreateRoleCommand $command): RoleDto
    {
        $role = new Role(new RoleId(0), new RoleName($command->name), $command->description);
        $this->roleRepository->save($role);
        return RoleDto::fromEntity($role);
    }
}
