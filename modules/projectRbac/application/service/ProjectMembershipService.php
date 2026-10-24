<?php

declare(strict_types=1);

namespace modules\projectRbac\application\service;

use core\domain\exception\ValidationException;
use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use core\domain\valueObject\UserId;
use RuntimeException;
use yii\db\Connection;

/**
 * Business logic for project membership: leaving and transfer of ownership.
 */
class ProjectMembershipService
{
    private IProjectRepository $projectRepository;
    private IProjectUserRepository $projectUserRepository;
    private IProjectRoleRepository $roleRepository;
    private Connection $db;

    public function __construct(
        IProjectRepository $projectRepository,
        IProjectUserRepository $projectUserRepository,
        IProjectRoleRepository $roleRepository,
        Connection $db,
    ) {
        $this->projectRepository     = $projectRepository;
        $this->projectUserRepository = $projectUserRepository;
        $this->roleRepository        = $roleRepository;
        $this->db                   = $db;
    }

    /**
     * Check if a user can leave the project.
     *
     * Business rules:
     *   - Owner cannot leave (must transfer ownership first)
     *   - Last admin cannot leave (would leave project without admin)
     *   - Personal project: leave is always false (single user context)
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     * @throws ValidationException
     */
    public function canLeave(int $userId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Personal — cannot leave (single-owner project)
        if ($project->getType()->isPersonal()) {
            return false;
        }

        // Owner cannot leave without transferring ownership
        if ($project->getOwnerId()->value() === $userId) {
            return false;
        }

        // Last admin protection
        if ($this->isLastAdmin($userId, $projectId)) {
            return false;
        }

        return true;
    }

    /**
     * Perform the leave action (soft-delete membership) for a user.
     *
     * Caller must check canLeave() first.
     *
     * @param int $userId
     * @param int $projectId
     * @return void
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function leave(int $userId, int $projectId): void
    {
        $this->db->transaction(function () use ($userId, $projectId): void {
            // Serialize membership exits for this project before checking the last-admin invariant.
            $this->db->createCommand(
                'SELECT user_id FROM {{%project_user}} WHERE project_id = :projectId FOR UPDATE',
                [':projectId' => $projectId],
            )->queryAll();

            if (!$this->canLeave($userId, $projectId)) {
                throw new RuntimeException('User is not allowed to leave this project');
            }

            $projectUser = $this->projectUserRepository->find(new ProjectId($projectId), new UserId($userId));
            if ($projectUser === null) {
                throw new RuntimeException('User is not an active member of this project');
            }
            $this->projectUserRepository->markLeft(new ProjectId($projectId), new UserId($userId));
        });
    }

    /**
     * Transfer project ownership to another member.
     *
     * Rules:
     *   - Only current owner can transfer
     *   - New owner must be an active member; transfer assigns the system admin role atomically
     *   - After transfer: new owner gets admin role, old owner stays as admin
     *
     * @param int $currentOwnerId
     * @param int $newOwnerId
     * @param int $projectId
     * @return void
     *
     * @throws RuntimeException on violation
     * @throws ValidationException
     */
    public function transferOwnership(int $currentOwnerId, int $newOwnerId, int $projectId): void
    {
        $this->db->transaction(function () use ($currentOwnerId, $newOwnerId, $projectId): void {
            // Serialize ownership transfers and membership changes for this project.
            $this->db->createCommand(
                'SELECT id FROM {{%project}} WHERE id = :projectId FOR UPDATE',
                [':projectId' => $projectId],
            )->queryOne();
            $this->db->createCommand(
                'SELECT user_id FROM {{%project_user}} WHERE project_id = :projectId FOR UPDATE',
                [':projectId' => $projectId],
            )->queryAll();

            $project = $this->projectRepository->findById(new ProjectId($projectId));
            if (!$project) {
                throw new RuntimeException('Project not found');
            }
            if ($project->getOwnerId()->value() !== $currentOwnerId) {
                throw new RuntimeException('Only the project owner can transfer ownership');
            }
            if ($currentOwnerId === $newOwnerId) {
                throw new RuntimeException('Cannot transfer ownership to yourself');
            }
            if ($project->getType()->isPersonal()) {
                throw new RuntimeException('Cannot transfer ownership of a personal project');
            }
            // Ownership transfer is an owner-only business operation. The target must be
            // an active member; this operation assigns the project-local admin role atomically.
            $newOwner = $this->projectUserRepository->find(new ProjectId($projectId), new UserId($newOwnerId));
            if ($newOwner === null) {
                throw new RuntimeException('New owner must be an active project member');
            }

            $adminRole = $this->roleRepository->findSystemRole($projectId, 'admin');
            if ($adminRole === null) {
                throw new RuntimeException('Project admin role is missing');
            }
            $oldOwner = $this->projectUserRepository->find(new ProjectId($projectId), new UserId($currentOwnerId));
            if ($oldOwner === null) {
                throw new RuntimeException('Both owners must be active project members');
            }

            $newOwner->changeRoleId($adminRole->getId());
            $oldOwner->changeRoleId($adminRole->getId());
            $this->projectUserRepository->save($newOwner);
            $this->projectUserRepository->save($oldOwner);
            $this->projectRepository->updateOwner(new ProjectId($projectId), new UserId($newOwnerId));
        });
    }

    // ── Private ─────────────────────────────────────────────

    /**
     * Check if the given user is the last admin in the project.
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     * @throws ValidationException
     */
    private function isLastAdmin(int $userId, int $projectId): bool
    {
        $userRole = $this->getUserRoleSlug($userId, $projectId);
        if ($userRole !== 'admin') {
            return false;
        }

        $members = $this->projectUserRepository->findByProject(new ProjectId($projectId));
        $adminCount = 0;
        foreach ($members as $member) {
            if ($this->getUserRoleSlug($member->getUserId()->value(), $projectId) === 'admin') {
                $adminCount++;
            }
        }
        return $adminCount <= 1;
    }

    private function getUserRoleSlug(int $userId, int $projectId): ?string
    {
        $projectUser = $this->projectUserRepository->find(new ProjectId($projectId), new UserId($userId));
        $roleId = $projectUser?->getRoleId();
        if ($roleId === null) {
            return null;
        }
        $role = $this->roleRepository->findRoleById($roleId);
        return $role !== null && $role->getProjectId() === $projectId ? ($role->isSystem() || $role->getSlug() !== 'admin' ? $role->getSlug() : null) : null;
    }

}
