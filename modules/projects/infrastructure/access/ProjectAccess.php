<?php

namespace modules\projects\infrastructure\access;

use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\BoardId;
use modules\projects\domain\entity\Project;
use modules\projects\domain\entity\Board;

class ProjectAccess implements IProjectAccess
{
    private const MAX_USER_PROJECTS = 10;
    private const MAX_PROJECT_BOARDS = 4;

    public function __construct(
        private IProjectRepository $projectRepository,
        private IBoardRepository $boardRepository,
        private IProjectUserRepository $projectUserRepository
    ) {}

    public function getUserProjectIds(int $userId): array
    {
        $projects = $this->projectRepository->findByUser(new UserId($userId));
        return array_map(fn(Project $p) => $p->getId()->getValue(), $projects);
    }

    public function getProjectBoardIds(int $projectId): array
    {
        $boards = $this->boardRepository->findByProject(new ProjectId($projectId));
        return array_map(fn(Board $b) => $b->getId()->getValue(), $boards);
    }

    public function getProjectIdByBoardId(int $boardId): ?int
    {
        $board = $this->boardRepository->findById(new BoardId($boardId));
        return $board?->getProjectId()->getValue();
    }

    public function canViewProject(int $userId, int $projectId): bool
    {
        return $this->isUserInProject($userId, $projectId);
    }

    public function canViewBoard(int $userId, int $boardId): bool
    {
        $projectId = $this->getProjectIdByBoardId($boardId);
        if (!$projectId) {
            return false;
        }
        return $this->canViewProject($userId, $projectId);
    }

    public function canManageUser(int $userId, int $targetUserId, int $projectId): bool
    {
        if (!$this->isAdmin($userId, $projectId)) {
            return false;
        }

        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Нельзя управлять владельцем проекта
        if ($targetUserId === $project->getOwnerId()->getValue()) {
            return false;
        }

        // Проверка последнего администратора
        if ($targetUserId !== $userId && $this->isAdmin($targetUserId, $projectId)) {
            $adminsCount = $this->countAdmins($projectId);
            if ($adminsCount <= 1) {
                return false;
            }
        }

        return true;
    }

    public function canManageProject(int $userId, int $projectId): bool
    {
        return $this->isOwner($userId, $projectId) || $this->isAdmin($userId, $projectId);
    }

    public function canManageBoard(int $userId, int $boardId): bool
    {
        $projectId = $this->getProjectIdByBoardId($boardId);
        if (!$projectId) {
            return false;
        }

        if ($this->isAdmin($userId, $projectId)) {
            return true;
        }

        $board = $this->boardRepository->findById(new BoardId($boardId));
        if (!$board) {
            return false;
        }

        return $board->getCreatedBy()->getValue() === $userId;
    }

    public function canInviteUser(int $userId, int $projectId): bool
    {
        return $this->isAdmin($userId, $projectId);
    }

    public function canRemoveUser(int $userId, int $targetUserId, int $projectId): bool
    {
        if (!$this->isAdmin($userId, $projectId)) {
            return false;
        }

        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Нельзя удалить владельца проекта
        if ($targetUserId === $project->getOwnerId()->getValue()) {
            return false;
        }

        // Проверка последнего администратора
        if ($this->isAdmin($targetUserId, $projectId)) {
            $adminsCount = $this->countAdmins($projectId);
            if ($adminsCount <= 1) {
                return false;
            }
        }

        return true;
    }

    public function canDeleteProject(int $userId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            return false;
        }

        // Нельзя удалить personal‑проект
        if ($project->getType()->isPersonal()) {
            return false;
        }

        return $project->getOwnerId()->getValue() === $userId;
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

        return $project->getOwnerId()->getValue() === $userId;
    }

    public function canCreateBoard(int $userId, int $projectId): bool
    {
        if (!$this->isAdmin($userId, $projectId) && !$this->isOwner($userId, $projectId)) {
            return false;
        }

        $boardsCount = $this->boardRepository->countByProject(new ProjectId($projectId));
        return $boardsCount < self::MAX_PROJECT_BOARDS;
    }

    public function canCreateProject(int $userId): bool
    {
        $projectsCount = $this->projectRepository->countByOwner(new UserId($userId));
        return $projectsCount < self::MAX_USER_PROJECTS;
    }

    private function isUserInProject(int $userId, int $projectId): bool
    {
        $projectUser = $this->projectUserRepository->find(
            new ProjectId($projectId),
            new UserId($userId)
        );
        return $projectUser !== null;
    }

    private function isAdmin(int $userId, int $projectId): bool
    {
        $projectUser = $this->projectUserRepository->find(
            new ProjectId($projectId),
            new UserId($userId)
        );
        return $projectUser && $projectUser->getRole()->isAdmin();
    }

    private function isOwner(int $userId, int $projectId): bool
    {
        $project = $this->projectRepository->findById(new ProjectId($projectId));
        return $project && $project->getOwnerId()->getValue() === $userId;
    }

    private function countAdmins(int $projectId): int
    {
        $members = $this->projectUserRepository->findByProject(new ProjectId($projectId));
        $admins = array_filter($members, fn($m) => $m->getRole()->isAdmin());
        return count($admins);
    }
}