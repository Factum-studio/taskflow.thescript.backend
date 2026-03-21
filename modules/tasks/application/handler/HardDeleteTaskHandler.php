<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\HardDeleteTaskCommand;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class HardDeleteTaskHandler
{
    private ITaskRepository $taskRepository;
    private ITaskAccess $taskAccess;

    public function __construct(
        ITaskRepository $taskRepository,
        ITaskAccess $taskAccess
    ) {
        $this->taskRepository   = $taskRepository;
        $this->taskAccess       = $taskAccess;
    }

    public function handle(HardDeleteTaskCommand $command): void
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }
        if (!$this->taskAccess->canHardDeleteTask($command->deletedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to permanently delete this task');
        }
        $this->taskRepository->remove($task);
    }
}