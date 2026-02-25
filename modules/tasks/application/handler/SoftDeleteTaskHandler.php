<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\SoftDeleteTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class SoftDeleteTaskHandler
{
    private ITaskRepository $taskRepository;
    private TaskDtoAssembler $taskDtoAssembler;

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler,
    ) {
        $this->taskRepository   = $taskRepository;
        $this->taskDtoAssembler = $taskDtoAssembler;
    }

    public function handle(SoftDeleteTaskCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $task->markAsDeleted();
        $savedTask = $this->taskRepository->save($task);

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}