<?php

declare(strict_types=1);

namespace modules\projectRbac\application\service;

use core\domain\exception\ValidationException;
use modules\projectRbac\application\port\ILocalRbacService;
use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\application\port\IBillingLimitsService;
use modules\projects\domain\entity\Project;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use core\domain\valueObject\UserId;

/**
 * Core implementation of local project RBAC.
 *
 * Algorithm (per the plan):
 *   Personal → Billing → Admin → Role → Override → Default-deny
 */
class LocalRbacService implements ILocalRbacService
{
    private IProjectRoleRepository $roleRepository;
    private IProjectRepository $projectRepository;
    private IProjectUserRepository $projectUserRepository;
    private IBillingLimitsService $billingLimits;

    public function __construct(
        IProjectRoleRepository $roleRepository,
        IProjectRepository $projectRepository,
        IProjectUserRepository $projectUserRepository,
        IBillingLimitsService $billingLimits,
    ) {
        $this->roleRepository        = $roleRepository;
        $this->projectRepository     = $projectRepository;
        $this->projectUserRepository = $projectUserRepository;
        $this->billingLimits         = $billingLimits;
    }

    /**
     * {@inheritdoc}
     * @throws ValidationException
     */
    public function hasPermission(int $userId, int $projectId, string $permissionCode): bool
    {
        // 1. Fetch project (also validates existence)
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // 2. Personal — always full access for the owner
        if ($project->getType()->isPersonal()) {
            return $userId === $project->getOwnerId()->value();
        }

        // 3. Billing — hard limit, even admin cannot bypass
        if (!$this->billingLimits->isFeatureAvailable($project->getOwnerId()->value(), $permissionCode)) {
            return false;
        }

        // 4. Admin — full access within project (billing already checked)
        if ($this->isProjectAdmin($userId, $projectId)) {
            return true;
        }

        // 5. Role-based permission
        $projectUser = $this->projectUserRepository->find(
            new ProjectId($projectId),
            new UserId($userId),
        );
        if (!$projectUser) {
            return false;
        }

        $roleId = $projectUser->getRoleId();
        if ($roleId === null) {
            return false;
        }

        $role = $this->roleRepository->findRoleById($roleId);
        if ($role === null || $role->getProjectId() !== $projectId) {
            return false;
        }
        $rolePermissionCodes = $this->roleRepository->findRolePermissionCodes($roleId);
        $hasRolePermission = in_array($permissionCode, $rolePermissionCodes, true);

        // 6. Individual override
        $override = $this->roleRepository->findUserOverride($projectId, $userId, $permissionCode);
        if ($override !== null) {
            return $override;
        }

        return $hasRolePermission;
    }

    /**
     * {@inheritdoc}
     * @throws ValidationException
     */
    public function getEffectivePermissions(int $userId, int $projectId): array
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return [];
        }

        // Personal — all permissions
        if ($project->getType()->isPersonal()) {
            return $userId === $project->getOwnerId()->value() ? $this->getAllPermissionCodes() : [];
        }

        // Admin — all permissions (filtered by billing)
        if ($this->isProjectAdmin($userId, $projectId)) {
            $allCodes = $this->getAllPermissionCodes();
            return array_values(array_filter(
                $allCodes,
                fn ($code) =>
                $this->billingLimits->isFeatureAvailable($project->getOwnerId()->value(), $code),
            ));
        }

        // Regular user: role permissions + granted overrides
        $projectUser = $this->projectUserRepository->find(
            new ProjectId($projectId),
            new UserId($userId),
        );
        if (!$projectUser) {
            return [];
        }

        $roleId = $projectUser->getRoleId();
        if ($roleId === null) {
            return [];
        }

        $role = $this->roleRepository->findRoleById($roleId);
        if ($role === null || $role->getProjectId() !== $projectId) {
            return [];
        }
        $result = $this->roleRepository->findRolePermissionCodes($roleId);

        // Apply overrides: granted → add, denied → remove
        $overrides = $this->roleRepository->findUserOverrides($projectId, $userId);
        foreach ($overrides as $ov) {
            if ($ov['is_granted']) {
                $result[] = $ov['permission_code'];
            } else {
                $idx = array_search($ov['permission_code'], $result, true);
                if ($idx !== false) {
                    unset($result[$idx]);
                }
            }
        }

        // Filter by billing
        $result = array_values(array_unique($result));
        return array_values(array_filter(
            $result,
            fn ($code) =>
            $this->billingLimits->isFeatureAvailable($project->getOwnerId()->value(), $code),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getRolePermissions(int $roleId): array
    {
        return $this->roleRepository->findRolePermissionCodes($roleId);
    }

    /**
     * {@inheritdoc}
     * @throws ValidationException
     */
    public function seedProjectRoles(Project $project): void
    {
        $projectId = $project->getId()->value();
        $type = $project->getType()->value();
        $ownerId = $project->getOwnerId()->value();

        $this->roleRepository->createDefaultRoles($projectId, $type, $ownerId);

        // Assign admin role to the owner
        $adminRole = $this->roleRepository->findSystemRole($projectId, 'admin');
        if ($adminRole) {
            $projectUser = $this->projectUserRepository->find(
                new ProjectId($projectId),
                new UserId($ownerId),
            );
            if ($projectUser) {
                $projectUser->changeRoleId($adminRole->getId());
                $this->projectUserRepository->save($projectUser);
            }
        }
    }

    // ── Private helpers ──────────────────────────────────────

    /**
     * Check whether the user has the project's exact 'admin' role.
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     * @throws ValidationException
     */
    private function isProjectAdmin(int $userId, int $projectId): bool
    {
        $projectUser = $this->projectUserRepository->find(
            new ProjectId($projectId),
            new UserId($userId),
        );
        if (!$projectUser) {
            return false;
        }

        $roleId = $projectUser->getRoleId();
        if ($roleId === null) {
            return false;
        }

        $role = $this->roleRepository->findRoleById($roleId);
        if (!$role) {
            return false;
        }

        return $role->getProjectId() === $projectId && $role->getSlug() === 'admin' && $role->isSystem();
    }

    private function getAllPermissionCodes(): array
    {
        return $this->roleRepository->findAllPermissionCodes();
    }
}
