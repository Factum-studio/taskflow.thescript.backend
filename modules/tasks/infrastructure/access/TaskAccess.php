<?php

declare(strict_types=1);

namespace modules\tasks\infrastructure\access;

use modules\projectRbac\application\port\ITaskAccessService;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\ColumnId;
use modules\projectRbac\application\port\ILocalRbacService;
use modules\projectRbac\application\port\IProjectAccessService;

/**
 * Task access checking — delegates to LocalRbacService for permission checks.
 *
 * Business rules (hardcoded, cannot be overridden):
 *   - canDeleteColumn: column must be empty (no tasks) to be deleted
 *   - canMoveTaskToColumn: task and target column must belong to the same board
 */
class TaskAccess implements ITaskAccessService
{
    private ITaskRepository $taskRepository;
    private IBoardColumnRepository $columnRepository;
    private ILocalRbacService $rbac;
    private IProjectAccessService $projectAccess;

    public function __construct(
        ITaskRepository $taskRepository,
        IBoardColumnRepository $columnRepository,
        ILocalRbacService $rbac,
        IProjectAccessService $projectAccess,
    ) {
        $this->taskRepository  = $taskRepository;
        $this->columnRepository = $columnRepository;
        $this->rbac            = $rbac;
        $this->projectAccess   = $projectAccess;
    }

    public function getProjectIdsByColumnId(int $columnId): array
    {
        $column = $this->columnRepository->findById(new ColumnId($columnId));
        if (!$column) {
            return [];
        }
        $boardId = $column->getBoardId()->value();
        $projectId = $this->projectAccess->getProjectIdByBoardId($boardId);
        if ($projectId) {
            return [$projectId];
        }
        return [];
    }

    // ── Project id resolution helper ────────────────────────

    /**
     * Resolve projectId from boardId, return 0 if not found.
     */
    private function projectIdByBoardId(int $boardId): int
    {
        $pid = $this->projectAccess->getProjectIdByBoardId($boardId);
        return $pid ?? 0;
    }

    /**
     * Resolve projectId from a task entity.
     */
    private function projectIdByTaskId(int $taskId): int
    {
        $task = $this->taskRepository->findById(new TaskId($taskId));
        if (!$task) {
            return 0;
        }
        $boardId = $task->getBoardId();
        return $this->projectIdByBoardId($boardId);
    }

    /**
     * Resolve projectId from a column id.
     */
    private function projectIdByColumnId(int $columnId): int
    {
        $column = $this->columnRepository->findById(new ColumnId($columnId));
        if (!$column) {
            return 0;
        }
        return $this->projectIdByBoardId($column->getBoardId()->value());
    }

    // ── Task permissions ────────────────────────────────────

    public function canViewTask(int $userId, int $taskId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.view');
    }

    public function canCreateTask(int $userId, int $boardId): bool
    {
        $projectId = $this->projectIdByBoardId($boardId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.create');
    }

    public function canUpdateTask(int $userId, int $taskId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.update');
    }

    public function canDeleteTask(int $userId, int $taskId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.delete');
    }

    public function canHardDeleteTask(int $userId, int $taskId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.hard_delete');
    }

    public function canRestoreTask(int $userId, int $taskId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.restore');
    }

    public function canAssignTask(int $userId, int $taskId, int $assigneeId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.assign');
    }

    public function canCommentOnTask(int $userId, int $taskId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'comment.create');
    }

    public function canAddStickerToTask(int $userId, int $taskId): bool
    {
        $projectId = $this->projectIdByTaskId($taskId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.sticker.attach');
    }

    // ── Column permissions ──────────────────────────────────

    public function canCreateColumn(int $userId, int $boardId): bool
    {
        $projectId = $this->projectIdByBoardId($boardId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'column.create');
    }

    public function canUpdateColumn(int $userId, int $columnId): bool
    {
        $projectId = $this->projectIdByColumnId($columnId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'column.update');
    }

    public function canDeleteColumn(int $userId, int $columnId): bool
    {
        $column = $this->columnRepository->findById(new ColumnId($columnId));
        if (!$column) {
            return false;
        }

        // Business rule: column must be empty
        $tasksInColumn = $this->taskRepository->countByColumn($column->getId());
        if ($tasksInColumn > 0) {
            return false;
        }

        $projectId = $this->projectIdByColumnId($columnId);
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'column.delete');
    }

    public function canMoveTaskToColumn(int $userId, int $taskId, int $columnId): bool
    {
        $task = $this->taskRepository->findById(new TaskId($taskId));
        if (!$task) {
            return false;
        }

        $column = $this->columnRepository->findById(new ColumnId($columnId));
        if (!$column) {
            return false;
        }

        // Business rule: task and column must belong to the same board
        if ($task->getBoardId() !== $column->getBoardId()->value()) {
            return false;
        }

        $projectId = $this->projectIdByBoardId($task->getBoardId());
        if ($projectId === 0) {
            return false;
        }
        return $this->rbac->hasPermission($userId, $projectId, 'task.move');
    }
}