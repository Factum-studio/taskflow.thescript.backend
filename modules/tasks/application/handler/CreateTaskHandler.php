<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\CreateTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\entity\Task;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskCreatedEvent;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\repository\ITaskPriorityRepository;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\ColumnId;
use modules\tasks\domain\valueObject\PriorityId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\Title;
use modules\tasks\domain\valueObject\UserId;
use DateTimeImmutable;

class CreateTaskHandler
{
    private ITaskRepository $taskRepository;
    private TaskDtoAssembler $taskDtoAssembler;
    private IEventDispatcher $eventDispatcher;
    private IBoardColumnRepository $columnRepository;
    private ITaskPriorityRepository $priorityRepository;

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler,
        IEventDispatcher $eventDispatcher,
        IBoardColumnRepository $columnRepository,
        ITaskPriorityRepository $priorityRepository
    ) {
        $this->taskRepository       = $taskRepository;
        $this->taskDtoAssembler     = $taskDtoAssembler;
        $this->eventDispatcher      = $eventDispatcher;
        $this->columnRepository     = $columnRepository;
        $this->priorityRepository   = $priorityRepository;
    }

    public function handle(CreateTaskCommand $command): TaskDto
    {
        $columnId = new ColumnId($command->columnId);
        if (!$this->columnRepository->findById($columnId)) {
            throw new InvalidArgumentException("Column with ID {$command->columnId} does not exist.");
        }

        $priorityId = new PriorityId($command->priorityId);
        if (!$this->priorityRepository->findById($priorityId)) {
            throw new InvalidArgumentException("Priority with ID {$command->priorityId} does not exist.");
        }

        $title      = new Title($command->title);
        $createdBy  = new UserId($command->createdBy);
        $assignedTo = $command->assignedTo ? new UserId($command->assignedTo) : null;
        $parentId   = $command->parentId ? new TaskId($command->parentId) : null;

        $task = new Task(
            new TaskId(0),
            $title,
            $columnId,
            $priorityId,
            $createdBy,
            $command->boardId,
            $command->description,
            $command->dueDate,
            $command->plannedStart,
            $command->plannedEnd,
            $assignedTo,
            $parentId,
            false,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            null
        );

        $savedTask = $this->taskRepository->save($task);

        $this->eventDispatcher->dispatch(new TaskCreatedEvent($savedTask));

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}