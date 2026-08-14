<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\SoftDeleteTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskSoftDeletedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class SoftDeleteTaskHandler
{
    private ITaskRepository $taskRepository;
    private TaskDtoAssembler $taskDtoAssembler;
    private IEventDispatcher $eventDispatcher;
    private ITaskAccess $taskAccess;

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler,
        IEventDispatcher $eventDispatcher,
        ITaskAccess $taskAccess
    ) {
        $this->taskRepository   = $taskRepository;
        $this->taskDtoAssembler = $taskDtoAssembler;
        $this->eventDispatcher  = $eventDispatcher;
        $this->taskAccess       = $taskAccess;
    }

    public function handle(SoftDeleteTaskCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        if (!$this->taskAccess->canDeleteTask($command->deletedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to delete this task');
        }

        $task->markAsDeleted();
        $savedTask = $this->taskRepository->save($task);

        $this->eventDispatcher->dispatch(
            new TaskSoftDeletedEvent($task, $command->deletedBy)
        );

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}