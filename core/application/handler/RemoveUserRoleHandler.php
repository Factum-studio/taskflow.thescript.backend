<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\command\RemoveUserRoleCommand;
use core\application\port\IUserRoleSearch;
use core\application\port\IUserRoleRepository;
use core\domain\exception\EntityNotFoundException;
use core\domain\exception\EntityUnavailableException;
use core\domain\exception\UserNotFoundException;
use core\domain\valueObject\UserRoleId;
use core\application\dto\UserRoleFiltersDto;

class RemoveUserRoleHandler
{
    private IUserRoleSearch $roleSearch;
    private IUserRoleRepository $userRoleRepository;

    public function __construct(IUserRoleSearch $roleSearch, IUserRoleRepository $userRoleRepository)
    {
        $this->roleSearch = $roleSearch;
        $this->userRoleRepository = $userRoleRepository;
    }

    /**
     * @throws EntityUnavailableException
     * @throws UserNotFoundException
     * @throws EntityNotFoundException
     */
    public function handle(RemoveUserRoleCommand $command): void
    {
        $filters = new UserRoleFiltersDto(ids: (string)$command->userRoleId);
        $roles = $this->roleSearch->findWithFilters($filters);
        if (empty($roles)) {
            throw new EntityNotFoundException('User role assignment not found');
        }
        $userRole = $roles[0];
        $this->userRoleRepository->delete($userRole->getId());
    }
}
