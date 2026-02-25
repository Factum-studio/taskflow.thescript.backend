<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\AssignTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;
use RuntimeException;

class AssignTaskHandler
{
    private ITaskRepository $taskRepository;
    private TaskDtoAssembler $taskDtoAssembler;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler,
        IEventDispatcher $eventDispatcher
    ) {
        $this->taskRepository   = $taskRepository;
        $this->taskDtoAssembler = $taskDtoAssembler;
        $this->eventDispatcher  = $eventDispatcher;
    }

    public function handle(AssignTaskCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $oldAssignee = $task->getAssignedTo();
        $newAssignee = $command->assignedTo ? new UserId($command->assignedTo) : null;

        $task->assignTo($newAssignee);
        $savedTask = $this->taskRepository->save($task);

        $this->eventDispatcher->dispatch(
            new TaskAssignedEvent($savedTask, $oldAssignee, $command->assignedBy)
        );

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}