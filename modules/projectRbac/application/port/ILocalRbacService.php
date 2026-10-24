<?php

declare(strict_types=1);

namespace modules\projectRbac\application\port;

use modules\projects\domain\entity\Project;

/**
 * Core service for checking local project RBAC permissions.
 *
 * This is the main entry point consumed by controllers, use-cases,
 * and the old ProjectAccess / TaskAccess classes.
 */
interface ILocalRbacService
{
    /**
     * Check whether a user has a specific permission within a project.
     *
     * Algorithm: Personal → Billing → Admin → Role → Override → Default-deny
     *
     * @param int $userId Acting user id
     * @param int $projectId Target project id
     * @param string $permissionCode e.g. "task.create"
     *
     * @return bool
     */
    public function hasPermission(int $userId, int $projectId, string $permissionCode): bool;

    /**
     * Returns the list of ALL effective permission codes for a user in a project.
     * Merges role permissions + overrides into one flat array.
     *
     * @param int $userId
     * @param int $projectId
     *
     * @return string[]
     */
    public function getEffectivePermissions(int $userId, int $projectId): array;

    /**
     * Returns all permission codes assigned to a specific role.
     *
     * @param int $roleId
     *
     * @return string[]
     */
    public function getRolePermissions(int $roleId): array;

    /**
     * Seed default roles (admin, member, and optionally manager) for a newly created project.
     *
     * @param Project $project
     *
     * @return void
     */
    public function seedProjectRoles(Project $project): void;
}
