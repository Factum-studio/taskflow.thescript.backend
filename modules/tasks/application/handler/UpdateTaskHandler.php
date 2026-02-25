<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\UpdateTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\PriorityId;
use modules\tasks\domain\valueObject\StatusId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\Title;
use modules\tasks\domain\valueObject\UserId;
use RuntimeException;

class UpdateTaskHandler
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

    public function handle(UpdateTaskCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }


        // Обновляем только переданные параметры, что б их
        if ($command->title !== null) {
            $task->changeTitle(new Title($command->title));
        }
        if ($command->description !== null) {
            $task->changeDescription($command->description);
        }
        if ($command->statusId !== null) {
            $task->changeStatus(new StatusId($command->statusId));
        }
        if ($command->priorityId !== null) {
            $task->changePriority(new PriorityId($command->priorityId));
        }
        if ($command->dueDate !== null) {
            $task->changeDueDate($command->dueDate);
        }
        if ($command->assignedTo !== null) {
            $task->assignTo($command->assignedTo ? new UserId($command->assignedTo) : null);
        }
        if ($command->boardId !== null) {
            $task->setBoardId($command->boardId);
        }
        if ($command->parentId !== null) {
            $task->setParentId($command->parentId ? new TaskId($command->parentId) : null);
        }

        $savedTask = $this->taskRepository->save($task);

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}