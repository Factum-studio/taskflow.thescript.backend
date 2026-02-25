<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\ChangeTaskStatusCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\StatusId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class ChangeTaskStatusHandler
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

    public function handle(ChangeTaskStatusCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $newStatus = new StatusId($command->statusId);

        $task->changeStatus($newStatus);
        $savedTask = $this->taskRepository->save($task);

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}