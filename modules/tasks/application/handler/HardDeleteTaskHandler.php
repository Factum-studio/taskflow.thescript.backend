<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\HardDeleteTaskCommand;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class HardDeleteTaskHandler
{
    private ITaskRepository $taskRepository;

    public function __construct(
        ITaskRepository $taskRepository
    ) {
        $this->taskRepository = $taskRepository;
    }

    public function handle(HardDeleteTaskCommand $command): void
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }
        $this->taskRepository->remove($task);
    }
}