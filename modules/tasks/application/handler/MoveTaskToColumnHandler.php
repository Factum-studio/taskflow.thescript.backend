<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\MoveTaskToColumnCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskMovedToColumnEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\ColumnId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class MoveTaskToColumnHandler
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

    public function handle(MoveTaskToColumnCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $oldColumn = $task->getColumnId();
        $newColumn = new ColumnId($command->columnId);

        $task->moveToColumn($newColumn);
        $savedTask = $this->taskRepository->save($task);

        $this->eventDispatcher->dispatch(
            new TaskMovedToColumnEvent($savedTask, $oldColumn, $command->movedBy ?? null)
        );

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}