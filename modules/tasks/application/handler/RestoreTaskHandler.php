<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\RestoreTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskRestoredEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class RestoreTaskHandler
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

    public function handle(RestoreTaskCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $task->restore();
        $savedTask = $this->taskRepository->save($task);

        $this->eventDispatcher->dispatch(
            new TaskRestoredEvent($savedTask, $command->restoredBy)
        );

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}