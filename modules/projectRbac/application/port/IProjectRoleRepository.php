<?php

declare(strict_types=1);

namespace modules\projectRbac\application\port;

use modules\projectRbac\domain\valueObject\ProjectRole;

/**
 * Repository interface for project roles and their permissions.
 *
 * Handles CRUD for roles, mappings, and individual user overrides.
 */
interface IProjectRoleRepository
{
    // ── Role CRUD ────────────────────────────────────────────

    /**
     * @param int $roleId
     * @return ProjectRole|null
     */
    public function findRoleById(int $roleId): ?ProjectRole;

    public function findRoleByIdInProject(int $projectId, int $roleId): ?ProjectRole;

    /** @return string[] */
    public function findAllPermissionCodes(): array;

    /**
     * @param int $projectId
     * @return ProjectRole[]
     */
    public function findRolesByProject(int $projectId): array;

    /**
     * @param int $projectId
     * @param string $slug "admin" | "member" | etc.
     * @return ProjectRole|null
     */
    public function findSystemRole(int $projectId, string $slug): ?ProjectRole;

    /**
     * Insert a new (custom/preset) role into a project.
     *
     * @param int $projectId
     * @param string $name display name
     * @param string $slug unique slug
     * @param int $rank hierarchy rank
     * @param bool $isSystem is this a system role (admin/member)?
     * @param bool $isPreset is this a preset role?
     * @param int $actorUserId
     * @return int inserted role id
     */
    public function insertRole(int $projectId, string $name, string $slug, int $rank, bool $isSystem, bool $isPreset, int $actorUserId): int;

    /**
     * Update a role's name and/or rank.
     *
     * @param int $roleId
     * @param string $name
     * @param int $rank
     * @param int $actorUserId
     * @return void
     */
    public function updateRole(int $roleId, string $name, int $rank, int $actorUserId): void;

    /**
     * Delete a role (non-system only).
     *
     * @param int $roleId
     * @return void
     */
    public function deleteRole(int $roleId): void;

    // ── Permissions assigned to roles ────────────────────────

    /**
     * @param int $roleId
     * @return string[] permission codes
     */
    public function findRolePermissionCodes(int $roleId): array;


    /**
     * Replace all permissions for a role (transactionally).
     *
     * @param int $roleId
     * @param string[] $permissionCodes
     * @param int $actorUserId
     * @return void
     */
    public function setRolePermissions(int $roleId, array $permissionCodes, int $actorUserId): void;

    // ── Individual user overrides ────────────────────────────

    /**
     * @param int $projectId
     * @param int $userId
     * @param string $permissionCode
     * @return bool|null true=granted, false=denied, null=no override
     */
    public function findUserOverride(int $projectId, int $userId, string $permissionCode): ?bool;

    /**
     * Returns all overrides for a user in a project.
     *
     * @param int $projectId
     * @param int $userId
     * @return array each item: ['permission_code' => string, 'is_granted' => bool]
     */
    public function findUserOverrides(int $projectId, int $userId): array;

    /**
     * Upsert an individual permission override for a user.
     *
     * @param int $projectId
     * @param int $userId
     * @param string $permissionCode
     * @param bool $isGranted
     * @param int $actorUserId
     * @return void
     */
    public function setUserOverride(int $projectId, int $userId, string $permissionCode, bool $isGranted, int $actorUserId): void;

    /**
     * Remove an individual permission override.
     *
     * @param int $projectId
     * @param int $userId
     * @param string $permissionCode
     * @return void
     */
    public function removeUserOverride(int $projectId, int $userId, string $permissionCode): void;

    // ── Role seeding for new projects ────────────────────────

    /**
     * Create default roles for a newly created project.
     *
     * - collaborative: admin (rank=100, system), member (rank=10, system)
     * - corporate:     admin (rank=100, system), manager (rank=50, preset), member (rank=10, system)
     *
     * @param int $projectId
     * @param string $projectType "collaborative" | "corporate"
     * @param int $ownerId creator user id
     * @return array [adminRoleId, memberRoleId, ?managerRoleId]
     */
    public function createDefaultRoles(int $projectId, string $projectType, int $ownerId): array;
}
