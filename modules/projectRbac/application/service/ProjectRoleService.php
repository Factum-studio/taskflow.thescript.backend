<?php

declare(strict_types=1);

namespace modules\projectRbac\application\service;

use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\domain\valueObject\ProjectRole as ProjectRoleVO;
use core\domain\valueObject\UserId;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;

/**
 * Business logic for managing project roles with hierarchy enforcement.
 *
 * Rules (per the plan):
 *   - Role creation/edit/deletion requires explicit permissions and strict rank hierarchy
 *   - System roles cannot be edited or deleted
 *   - Assign role: can assign roles strictly below the actor's rank
 *   - Remove role assignment: can remove roles < actor's rank
 *   - Manage role permissions: can manage roles < actor's rank
 */
class ProjectRoleService
{
    private IProjectRoleRepository $roleRepository;
    private IProjectUserRepository $projectUserRepository;

    public function __construct(IProjectRoleRepository $roleRepository, IProjectUserRepository $projectUserRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->projectUserRepository = $projectUserRepository;
    }

    /** A new role must be strictly lower-ranked than the actor's role. */
    public function canCreateRole(int $actorUserId, int $projectId, int $newRank): bool
    {
        $actorRole = $this->getUserProjectRole($actorUserId, $projectId);
        return $actorRole !== null && $actorRole->getRank()->value() > $newRank;
    }

    /** A role can be edited only by someone ranked above it; system roles are immutable. */
    public function canUpdateRole(int $actorUserId, int $projectId, ProjectRoleVO $targetRole, int $newRank): bool
    {
        $actorRole = $this->getUserProjectRole($actorUserId, $projectId);
        if ($actorRole === null || $targetRole->getProjectId() !== $projectId || $targetRole->isSystem()) {
            return false;
        }

        $actorRank = $actorRole->getRank()->value();
        return $actorRank > $targetRole->getRank()->value() && $actorRank > $newRank;
    }

    /** A role can be deleted only by someone ranked above it; system roles are immutable. */
    public function canDeleteRole(int $actorUserId, int $projectId, ProjectRoleVO $targetRole): bool
    {
        $actorRole = $this->getUserProjectRole($actorUserId, $projectId);
        return $actorRole !== null
            && $targetRole->getProjectId() === $projectId
            && !$targetRole->isSystem()
            && $actorRole->getRank()->canRemove($targetRole->getRank());
    }

    /** A user may manage another member's permission overrides only when ranked above them. */
    public function canManageUserPermissions(int $actorUserId, int $projectId, int $targetUserId): bool
    {
        if ($actorUserId === $targetUserId) {
            return false;
        }
        $actorRole = $this->getUserProjectRole($actorUserId, $projectId);
        $targetRole = $this->getUserProjectRole($targetUserId, $projectId);
        return $actorRole !== null
            && $targetRole !== null
            && $actorRole->getRank()->isGreaterThan($targetRole->getRank());
    }

    /** Assigning is allowed only when the target role is not above the actor. */
    public function canAssignRole(int $actorUserId, int $projectId, ProjectRoleVO $targetRole): bool
    {
        $actorRole = $this->getUserProjectRole($actorUserId, $projectId);
        if (!$actorRole || $targetRole->getProjectId() !== $projectId) {
            return false;
        }
        return $actorRole->getRank()->canAssign($targetRole->getRank());
    }

    /**
     * Can the actor remove a role assignment from another user (change their role)?
     * - Can remove roles with rank < actor's rank.
     *
     * @param int $actorUserId
     * @param int $projectId
     * @param ProjectRoleVO $targetRole
     * @return bool
     */
    public function canRemoveRoleAssignment(int $actorUserId, int $projectId, ProjectRoleVO $targetRole): bool
    {
        $actorRole = $this->getUserProjectRole($actorUserId, $projectId);
        if (!$actorRole || $targetRole->getProjectId() !== $projectId) {
            return false;
        }
        return $actorRole->getRank()->canRemove($targetRole->getRank());
    }

    /**
     * Can the actor manage permissions of the given role?
     * - Can manage roles with rank < actor's rank.
     *
     * @param int $actorUserId
     * @param int $projectId
     * @param ProjectRoleVO $targetRole
     * @return bool
     */
    public function canManageRolePermissions(int $actorUserId, int $projectId, ProjectRoleVO $targetRole): bool
    {
        $actorRole = $this->getUserProjectRole($actorUserId, $projectId);
        if (!$actorRole || $targetRole->getProjectId() !== $projectId) {
            return false;
        }
        return $actorRole->getRank()->canManagePermissions($targetRole->getRank());
    }

    /**
     * Validate that the actor can modify a user's role (changeRole).
     *
     * @param int $actorUserId
     * @param int $projectId
     * @param int $targetUserId
     * @param int $newRoleId
     * @return bool
     */
    public function canChangeUserRole(int $actorUserId, int $projectId, int $targetUserId, int $newRoleId): bool
    {
        $targetRole = $this->roleRepository->findRoleById($newRoleId);
        if (!$targetRole || $targetRole->getProjectId() !== $projectId) {
            return false;
        }

        // Can assign the new role?
        if (!$this->canAssignRole($actorUserId, $projectId, $targetRole)) {
            return false;
        }

        // If removing a role from target (changing their existing role), check hierarchy
        $targetCurrentRole = $this->getUserProjectRole($targetUserId, $projectId);
        if ($targetCurrentRole) {
            if (!$this->canRemoveRoleAssignment($actorUserId, $projectId, $targetCurrentRole)) {
                return false;
            }
        }

        return true;
    }

    // ── Private ─────────────────────────────────────────────

    /**
     * @param int $userId
     * @param int $projectId
     * @return ProjectRoleVO|null
     */
    private function getUserProjectRole(int $userId, int $projectId): ?ProjectRoleVO
    {
        $projectUser = $this->projectUserRepository->find(new ProjectId($projectId), new UserId($userId));
        if (!$projectUser || $projectUser->getRoleId() === null) {
            return null;
        }
        $role = $this->roleRepository->findRoleById($projectUser->getRoleId());
        return $role !== null && $role->getProjectId() === $projectId ? $role : null;
    }
}
