<?php

declare(strict_types=1);

namespace core\application\port;

use core\application\dto\RoleFiltersDto;
use core\domain\entity\Role;
use core\domain\exception\EntityNotFoundException;
use core\domain\valueObject\RoleId;
use core\domain\valueObject\RoleName;

interface IRoleRepository
{
    /**
     * @throws EntityNotFoundException
     */
    public function findById(RoleId $id): Role;

    /**
     * @throws EntityNotFoundException
     */
    public function findByName(RoleName $name): Role;

    public function findAll(): array;

    /**
     * @return Role[]
     */
    public function findWithFilters(RoleFiltersDto $filters): array;

    public function countWithFilters(RoleFiltersDto $filters): int;

    public function save(Role $role): void;

    public function delete(RoleId $id): void;
}
