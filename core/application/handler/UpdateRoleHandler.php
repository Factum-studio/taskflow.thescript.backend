<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\command\UpdateRoleCommand;
use core\application\dto\RoleDto;
use core\application\port\IRoleRepository;
use core\domain\exception\EntityNotFoundException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\RoleId;
use core\domain\valueObject\RoleName;

class UpdateRoleHandler
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
    public function handle(UpdateRoleCommand $command): RoleDto
    {
        $role = $this->roleRepository->findById(new RoleId($command->roleId));
        if ($command->name !== null) {
            $role->rename(new RoleName($command->name));
        }
        if ($command->description !== null) {
            $role->setDescription($command->description);
        }
        $this->roleRepository->save($role);
        return RoleDto::fromEntity($role);
    }
}
