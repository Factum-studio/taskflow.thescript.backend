<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\CreateTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\entity\Task;
use modules\tasks\domain\repository\ITaskRepository;
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

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler,
    ) {
        $this->taskRepository   = $taskRepository;
        $this->taskDtoAssembler = $taskDtoAssembler;
    }

    public function handle(CreateTaskCommand $command): TaskDto
    {
        $title      = new Title($command->title);
        $statusId   = new StatusId($command->statusId);
        $priorityId = new PriorityId($command->priorityId);
        $createdBy  = new UserId($command->createdBy);
        $assignedTo = $command->assignedTo ? new UserId($command->assignedTo) : null;
        $parentId   = $command->parentId ? new TaskId($command->parentId) : null;

        // Используем временный ID=0
        $task = new Task(
            new TaskId(0),
            $title,
            $statusId,
            $priorityId,
            $createdBy,
            $command->boardId,
            $command->description,
            $command->dueDate,
            $assignedTo,
            $parentId,
            false,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            null
        );

        $savedTask = $this->taskRepository->save($task);

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}