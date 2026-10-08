<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\command\AssignUserRoleCommand;
use core\application\port\IUserRepository;
use core\application\port\IUserRoleRepository;
use core\application\port\IRoleRepository;
use core\domain\entity\UserRole;
use core\domain\exception\EntityNotFoundException;
use core\domain\exception\EntityUnavailableException;
use core\domain\exception\UserNotFoundException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\RoleId;
use core\domain\valueObject\UserRoleId;
use core\domain\valueObject\UserId;
use DateTimeImmutable;

class AssignUserRoleHandler
{
    private IUserRepository $userRepository;
    private IRoleRepository $roleRepository;
    private IUserRoleRepository $userRoleRepository;

    public function __construct(IUserRepository $userRepository, IRoleRepository $roleRepository, IUserRoleRepository $userRoleRepository)
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->userRoleRepository = $userRoleRepository;
    }

    /**
     * @throws ValidationException
     * @throws EntityUnavailableException
     * @throws UserNotFoundException
     * @throws EntityNotFoundException
     */
    public function handle(AssignUserRoleCommand $command): void
    {
        $user = $this->userRepository->findById(new UserId($command->userId));
        // Проверяем, существует ли роль
        $this->roleRepository->findById(new RoleId($command->roleId));
        $userRole = new UserRole(
            new UserRoleId(0),
            $user->getId(),
            new RoleId($command->roleId),
            new DateTimeImmutable(),
            $command->assignedBy ? new UserId($command->assignedBy) : null,
        );
        $this->userRoleRepository->save($userRole);
    }
}
