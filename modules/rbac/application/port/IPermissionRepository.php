<?php

declare(strict_types=1);

namespace modules\rbac\application\port;

use modules\rbac\domain\valueObject\Permission;

interface IPermissionRepository
{
    public function findByCode(string $code): ?Permission;

    /** @return string[] */
    public function findCodesByUserId(int $userId): array;

    /** @return Permission[] */
    public function findAll(): array;

    /** @return Permission[] */
    public function findByRoleId(int $roleId): array;

    public function grant(int $roleId, int $permissionId): void;

    public function revoke(int $roleId, int $permissionId): void;
}
