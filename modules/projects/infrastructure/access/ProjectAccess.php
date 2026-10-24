<?php

declare(strict_types=1);

namespace modules\projects\infrastructure\access;

use core\domain\valueObject\UserId;
use modules\projectRbac\application\port\ILocalRbacService;
use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\application\service\ProjectRoleService;
use modules\projectRbac\application\port\IBillingLimitsService;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\BoardId;
use modules\projects\domain\entity\Project;
use modules\projects\domain\entity\Board;
use modules\company\domain\repository\ICompanyMemberRepository;

/**
 * Project access checking — delegates to LocalRbacService for RBAC,
 * keeps hard-coded business rules that cannot be overridden.
 */
class ProjectAccess implements IProjectAccessService
{
    private ILocalRbacService $rbac;
    private IProjectRepository $projectRepository;
    private IBoardRepository $boardRepository;
    private IProjectUserRepository $projectUserRepository;
    private ICompanyMemberRepository $companyMemberRepository;
    private IBillingLimitsService $billingLimits;
    private IProjectRoleRepository $roleRepository;
    private ProjectRoleService $roleService;

    public function __construct(
        ILocalRbacService $rbac,
        IProjectRepository $projectRepository,
        IBoardRepository $boardRepository,
        IProjectUserRepository $projectUserRepository,
        ICompanyMemberRepository $companyMemberRepository,
        IBillingLimitsService $billingLimits,
        IProjectRoleRepository $roleRepository,
        ProjectRoleService $roleService,
    ) {
        $this->rbac                    = $rbac;
        $this->projectRepository       = $projectRepository;
        $this->boardRepository         = $boardRepository;
        $this->projectUserRepository   = $projectUserRepository;
        $this->companyMemberRepository = $companyMemberRepository;
        $this->billingLimits           = $billingLimits;
        $this->roleRepository         = $roleRepository;
        $this->roleService            = $roleService;
    }

    // ── Utility ─────────────────────────────────────────────

    public function getUserProjectIds(int $userId): array
    {
        $projects = $this->projectRepository->findByUser(new UserId($userId));
        return array_map(fn (Project $p) => $p->getId()->value(), $projects);
    }

    public function getProjectBoardIds(int $projectId): array
    {
        $boards = $this->boardRepository->findByProject(new ProjectId($projectId));
        return array_map(fn (Board $b) => $b->getId()->value(), $boards);
    }

    public function getProjectIdByBoardId(int $boardId): ?int
    {
        $board = $this->boardRepository->findById(new BoardId($boardId));
        return $board?->getProjectId()->value();
    }

    // ── View ────────────────────────────────────────────────

    public function canViewProject(int $userId, int $projectId): bool
    {
        return $this->rbac->hasPermission($userId, $projectId, 'project.view')
            || $this->isUserInProject($userId, $projectId);
    }

    public function canViewBoard(int $userId, int $boardId): bool
    {
        $projectId = $this->getProjectIdByBoardId($boardId);
        if (!$projectId) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'board.view')
            || $this->isUserInProject($userId, $projectId);
    }

    // ── Manage ──────────────────────────────────────────────

    public function canManageProject(int $userId, int $projectId): bool
    {
        // Manage project = either owner or admin
        return $this->isOwner($userId, $projectId)
            || $this->rbac->hasPermission($userId, $projectId, 'project.settings.manage');
    }

    public function canManageBoard(int $userId, int $boardId): bool
    {
        $projectId = $this->getProjectIdByBoardId($boardId);
        if (!$projectId) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'board.settings.manage')
            || $this->isOwner($userId, $projectId);
    }

    // ── Participants ────────────────────────────────────────

    public function canManageUser(int $userId, int $targetUserId, int $projectId): bool
    {
        // Нельзя управлять владельцем
        if ($targetUserId === $this->getOwnerId($projectId)) {
            return false;
        }

        if (!$this->canManageTargetRole($userId, $targetUserId, $projectId)) {
            return false;
        }

        // Business rule: последнего админа нельзя понизить/удалить
        if ($targetUserId !== $userId && $this->isAdmin($targetUserId, $projectId)) {
            $adminsCount = $this->countAdmins($projectId);
            if ($adminsCount <= 1) {
                return false;
            }
        }

        return $this->rbac->hasPermission($userId, $projectId, 'member.role.change');
    }

    public function canAddMember(int $userId, int $targetUserId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Personal — нельзя добавить никого
        if ($project->getType()->isPersonal()) {
            return false;
        }

        // Collaborative — только через приглашение (addMember всегда false)
        if ($project->getType()->isCollaborative()) {
            return false;
        }

        // Corporate — только из своей компании
        if ($project->getType()->isCorporate()) {
            $userCompany   = $this->getUserCompanyId($userId);
            $targetCompany = $this->getUserCompanyId($targetUserId);
            if ($userCompany === null || $userCompany !== $targetCompany) {
                return false;
            }
        }

        return $this->billingLimits->canAddMember($projectId)
            && $this->rbac->hasPermission($userId, $projectId, 'member.add');
    }

    public function canInviteUser(int $userId, int $targetUserId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Personal — нельзя приглашать
        if ($project->getType()->isPersonal()) {
            return false;
        }

        // Corporate: invite только если компании РАЗНЫЕ (иначе addMember)
        if ($project->getType()->isCorporate()) {
            $userCompany   = $this->getUserCompanyId($userId);
            $targetCompany = $this->getUserCompanyId($targetUserId);
            // Если в одной компании — используй addMember, не invite
            if ($userCompany !== null && $userCompany === $targetCompany) {
                return false;
            }
        }

        $memberRole = $this->roleRepository->findSystemRole($projectId, 'member');
        return $memberRole !== null
            && $this->roleService->canAssignRole($userId, $projectId, $memberRole)
            && $this->rbac->hasPermission($userId, $projectId, 'member.invite');
    }

    public function canRemoveUser(int $userId, int $targetUserId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Нельзя удалить владельца
        if ($targetUserId === $project->getOwnerId()->value()) {
            return false;
        }

        if (!$this->canManageTargetRole($userId, $targetUserId, $projectId)) {
            return false;
        }

        // Нельзя удалить последнего админа
        if ($this->isAdmin($targetUserId, $projectId)) {
            $adminsCount = $this->countAdmins($projectId);
            if ($adminsCount <= 1) {
                return false;
            }
        }

        return $this->rbac->hasPermission($userId, $projectId, 'member.remove');
    }


    private function canManageTargetRole(int $actorUserId, int $targetUserId, int $projectId): bool
    {
        $targetMember = $this->projectUserRepository->find(new ProjectId($projectId), new UserId($targetUserId));
        $targetRoleId = $targetMember?->getRoleId();
        if ($targetRoleId === null) {
            return false;
        }
        $targetRole = $this->roleRepository->findRoleById($targetRoleId);
        return $targetRole !== null
            && $targetRole->getProjectId() === $projectId
            && $this->roleService->canRemoveRoleAssignment($actorUserId, $projectId, $targetRole);
    }

    // ── Delete ──────────────────────────────────────────────

    public function canDeleteProject(int $userId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Personal нельзя удалить
        if ($project->getType()->isPersonal()) {
            return false;
        }

        // Только owner
        return $project->getOwnerId()->value() === $userId;
    }

    public function canDeleteBoard(int $userId, int $boardId): bool
    {
        $projectId = $this->getProjectIdByBoardId($boardId);
        if (!$projectId) {
            return false;
        }

        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Personal — доску нельзя удалить (правило бизнеса)
        if ($project->getType()->isPersonal()) {
            return false;
        }

        return $this->rbac->hasPermission($userId, $projectId, 'board.delete');
    }

    // ── Create (с лимитами) ────────────────────────────────

    public function canCreateBoard(int $userId, int $projectId): bool
    {
        if (!$this->billingLimits->canCreateBoard($projectId)) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'board.create');
    }

    /**
     * {@inheritdoc}
     *
     * Лимит MAX_USER_PROJECTS планируется вынести в billing.
     */
    public function canCreateProject(int $userId): bool
    {
        return $this->billingLimits->canCreateProject($userId);
    }

    // ── Private: business rules helpers ──────────────────────

    private function isUserInProject(int $userId, int $projectId): bool
    {
        $projectUser = $this->projectUserRepository->find(
            new ProjectId($projectId),
            new UserId($userId),
        );
        return $projectUser !== null;
    }

    private function isAdmin(int $userId, int $projectId): bool
    {
        $projectUser = $this->projectUserRepository->find(
            new ProjectId($projectId),
            new UserId($userId),
        );
        if (!$projectUser || $projectUser->getRoleId() === null) {
            return false;
        }
        $role = $this->roleRepository->findRoleById($projectUser->getRoleId());
        return $role !== null && $role->getProjectId() === $projectId && $role->getSlug() === 'admin' && $role->isSystem();
    }

    private function isOwner(int $userId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        return $project && $project->getOwnerId()->value() === $userId;
    }

    private function getOwnerId(int $projectId): ?int
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        return $project?->getOwnerId()->value();
    }

    private function countAdmins(int $projectId): int
    {
        $members = $this->projectUserRepository->findByProject(new ProjectId($projectId));
        return count(array_filter($members, function ($member) use ($projectId): bool {
            $roleId = $member->getRoleId();
            if ($roleId === null) { return false; }
            $role = $this->roleRepository->findRoleById($roleId);
            return $role !== null && $role->getProjectId() === $projectId && $role->getSlug() === 'admin' && $role->isSystem();
        }));
    }

    private function getUserCompanyId(int $userId): ?int
    {
        $members = $this->companyMemberRepository->findByUser(
            new \core\domain\valueObject\UserId($userId),
        );
        if (empty($members)) {
            return null;
        }
        return $members[0]->getCompanyId()->value();
    }
}