<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\command\DeleteRoleCommand;
use core\application\port\IRoleRepository;
use core\domain\exception\ValidationException;
use core\domain\valueObject\RoleId;

class DeleteRoleHandler
{
    private IRoleRepository $roleRepository;

    public function __construct(IRoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @throws ValidationException
     */
    public function handle(DeleteRoleCommand $command): void
    {
        $this->roleRepository->delete(new RoleId($command->roleId));
    }
}
