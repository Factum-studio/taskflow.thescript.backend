<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\CreateTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\entity\Task;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskCreatedEvent;
use modules\tasks\domain\repository\ITaskPriorityRepository;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStatusRepository;
use modules\tasks\domain\valueObject\PriorityId;
use modules\tasks\domain\valueObject\StatusId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\Title;
use modules\tasks\domain\valueObject\UserId;
use DateTimeImmutable;

class CreateTaskHandler
{
    private ITaskRepository $taskRepository;
    private TaskDtoAssembler $taskDtoAssembler;
    private IEventDispatcher $eventDispatcher;
    private ITaskStatusRepository $statusRepository;
    private ITaskPriorityRepository $priorityRepository;

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler,
        IEventDispatcher $eventDispatcher,
        ITaskStatusRepository $statusRepository,
        ITaskPriorityRepository $priorityRepository
    ) {
        $this->taskRepository       = $taskRepository;
        $this->taskDtoAssembler     = $taskDtoAssembler;
        $this->eventDispatcher      = $eventDispatcher;
        $this->statusRepository     = $statusRepository;
        $this->priorityRepository   = $priorityRepository;
    }

    public function handle(CreateTaskCommand $command): TaskDto
    {
        $statusId = new StatusId($command->statusId);
        if (!$this->statusRepository->findById($statusId)) {
            throw new InvalidArgumentException("Status with ID {$command->statusId} does not exist.");
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
            $statusId,
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