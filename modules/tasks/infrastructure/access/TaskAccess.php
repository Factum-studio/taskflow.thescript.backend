<?php

namespace modules\tasks\infrastructure\access;

use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\ColumnId;
use modules\projects\application\port\IProjectAccess;

class TaskAccess implements ITaskAccess
{
    public function __construct(
        private ITaskRepository $taskRepository,
        private IBoardColumnRepository $columnRepository,
        private IProjectAccess $projectAccess
    ) {}

    public function getProjectIdsByColumnId(int $columnId): array
    {
        $column = $this->columnRepository->findById(new ColumnId($columnId));
        if (!$column) {
            return [];
        }
        $boardId = $column->getBoardId()->getValue();
        $projectId = $this->projectAccess->getProjectIdByBoardId($boardId);
        if ($projectId) {
            return [$projectId];
        }
        // Системные колонки (без доски) пока не поддерживаются
        return [];
    }

    public function canViewTask(int $userId, int $taskId): bool
    {
        $task = $this->taskRepository->findById(new TaskId($taskId));
        if (!$task) {
            return false;
        }
        return $this->projectAccess->canViewBoard($userId, $task->getBoardId());
    }

    public function canCreateTask(int $userId, int $boardId): bool
    {
        return $this->projectAccess->canViewBoard($userId, $boardId);
    }

    public function canUpdateTask(int $userId, int $taskId): bool
    {
        return $this->canViewTask($userId, $taskId);
    }

    public function canDeleteTask(int $userId, int $taskId): bool
    {
        return $this->canViewTask($userId, $taskId);
    }

    public function canHardDeleteTask(int $userId, int $taskId): bool
    {
        $task = $this->taskRepository->findById(new TaskId($taskId));
        if (!$task) {
            return false;
        }
        return $this->projectAccess->canManageBoard($userId, $task->getBoardId());
    }

    public function canRestoreTask(int $userId, int $taskId): bool
    {
        return $this->canViewTask($userId, $taskId);
    }

    public function canAssignTask(int $userId, int $taskId, int $assigneeId): bool
    {
        return $this->canViewTask($userId, $taskId);
    }

    public function canCommentOnTask(int $userId, int $taskId): bool
    {
        return $this->canViewTask($userId, $taskId);
    }

    public function canAddStickerToTask(int $userId, int $taskId): bool
    {
        return $this->canViewTask($userId, $taskId);
    }

    public function canCreateColumn(int $userId, int $boardId): bool
    {
        return $this->projectAccess->canManageBoard($userId, $boardId);
    }

    public function canUpdateColumn(int $userId, int $columnId): bool
    {
        $column = $this->columnRepository->findById(new ColumnId($columnId));
        if (!$column) {
            return false;
        }
        return $this->projectAccess->canManageBoard($userId, $column->getBoardId()->getValue());
    }

    public function canDeleteColumn(int $userId, int $columnId): bool
    {
        $column = $this->columnRepository->findById(new ColumnId($columnId));
        if (!$column) {
            return false;
        }

        // Проверяем наличие задач в колонке
        $tasksInColumn = $this->taskRepository->countByColumn($column->getId());
        if ($tasksInColumn > 0) {
            return false;
        }

        return $this->projectAccess->canManageBoard($userId, $column->getBoardId()->getValue());
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
        // Задача и колонка должны принадлежать одной доске
        if ($task->getBoardId() !== $column->getBoardId()->getValue()) {
            return false;
        }
        return $this->projectAccess->canViewBoard($userId, $task->getBoardId());
    }
}