<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\ChangeTaskStatusCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskStatusChangedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\StatusId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class ChangeTaskStatusHandler
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

    public function handle(ChangeTaskStatusCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $oldStatus = $task->getStatusId();
        $newStatus = new StatusId($command->statusId);

        $task->changeStatus($newStatus);
        $savedTask = $this->taskRepository->save($task);

        $this->eventDispatcher->dispatch(
            new TaskStatusChangedEvent($savedTask, $oldStatus, $command->changedBy ?? null)
        );

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}