<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\AssignTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;
use RuntimeException;

class AssignTaskHandler
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

    public function handle(AssignTaskCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $newAssignee = $command->assignedTo ? new UserId($command->assignedTo) : null;

        $task->assignTo($newAssignee);
        $savedTask = $this->taskRepository->save($task);

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}