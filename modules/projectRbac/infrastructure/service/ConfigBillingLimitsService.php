<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\service;

use core\domain\exception\ValidationException;
use modules\projectRbac\application\port\IBillingLimitsService;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\tasks\domain\valueObject\BoardId as TaskBoardId;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\tasks\domain\repository\IBoardColumnRepository;
use core\domain\valueObject\UserId;

/**
 * Config-driven implementation of IBillingLimitsService.
 *
 * Reads limits and feature whitelist from module params.
 * When the real billing module is built, replace this implementation.
 */
class ConfigBillingLimitsService implements IBillingLimitsService
{
    private IProjectRepository $projectRepository;
    private IBoardRepository $boardRepository;
    private IBoardColumnRepository $columnRepository;
    private IProjectUserRepository $projectUserRepository;

    private int $maxProjects;
    private int $maxMembers;
    private int $maxBoards;
    private int $maxColumns;
    private array $availableFeatures;

    public function __construct(
        IProjectRepository $projectRepository,
        IBoardRepository $boardRepository,
        IBoardColumnRepository $columnRepository,
        IProjectUserRepository $projectUserRepository,
        array $params,
    ) {
        $this->projectRepository = $projectRepository;
        $this->boardRepository   = $boardRepository;
        $this->columnRepository  = $columnRepository;
        $this->projectUserRepository = $projectUserRepository;

        $this->maxProjects       = (int)($params['max_projects'] ?? 10);
        $this->maxMembers        = (int)($params['max_members'] ?? 50);
        $this->maxBoards         = (int)($params['max_boards'] ?? 10);
        $this->maxColumns        = (int)($params['max_columns'] ?? 20);
        $this->availableFeatures = $params['available_features'] ?? ['*'];
    }

    // ── Feature availability (functional segmentation) ──────

    public function isFeatureAvailable(int $userId, string $permissionCode): bool
    {
        // '*' — all features available
        if (in_array('*', $this->availableFeatures, true)) {
            return true;
        }

        // Check whitelist (with wildcard support e.g. "task.*")
        foreach ($this->availableFeatures as $pattern) {
            if ($this->matchWildcard($pattern, $permissionCode)) {
                return true;
            }
        }

        return false;
    }

    public function getAvailableFeatures(int $userId): array
    {
        return $this->availableFeatures;
    }

    // ── Quota / limits ──────────────────────────────────────

    public function getMaxProjects(int $userId): int
    {
        return $this->maxProjects;
    }

    public function getMaxMembers(int $projectId): int
    {
        return $this->maxMembers;
    }

    public function getMaxBoards(int $projectId): int
    {
        return $this->maxBoards;
    }

    public function getMaxColumns(int $projectId): int
    {
        return $this->maxColumns;
    }

    // ── Convenience checks ──────────────────────────────────

    /**
     * @throws ValidationException
     */
    public function canCreateProject(int $userId): bool
    {
        $count = $this->projectRepository->countByOwner(new UserId($userId));
        return $count < $this->maxProjects;
    }

    /**
     * @throws ValidationException
     */
    public function canAddMember(int $projectId): bool
    {
        $members = $this->projectUserRepository->findByProject(new ProjectId($projectId));
        return count($members) < $this->maxMembers;
    }

    /**
     * @throws ValidationException
     */
    public function canCreateBoard(int $projectId): bool
    {
        $count = $this->boardRepository->countByProject(new ProjectId($projectId));
        return $count < $this->maxBoards;
    }

    public function canCreateColumn(int $projectId): bool
    {
        $boards = $this->boardRepository->findByProject(new ProjectId($projectId));
        foreach ($boards as $board) {
            if (count($this->columnRepository->findByBoard(new TaskBoardId($board->getId()->value()))) < $this->maxColumns) {
                return true;
            }
        }
        return false;
    }

    // ── Private ─────────────────────────────────────────────

    /**
     * Simple wildcard match (supports trailing *).
     * E.g. "task.*" matches "task.create", "task.view", etc.
     */
    private function matchWildcard(string $pattern, string $value): bool
    {
        if ($pattern === '*') {
            return true;
        }
        $pos = mb_strpos($pattern, '*');
        if ($pos === false) {
            return $pattern === $value;
        }
        // Trailing wildcard only
        if ($pos === mb_strlen($pattern) - 1) {
            $prefix = mb_substr($pattern, 0, $pos);
            return mb_substr($value, 0, mb_strlen($prefix)) === $prefix;
        }
        return $pattern === $value;
    }
}
