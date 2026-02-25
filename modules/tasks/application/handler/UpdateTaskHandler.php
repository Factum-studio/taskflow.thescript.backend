<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\UpdateTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskUpdatedEvent;
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

    public function handle(UpdateTaskCommand $command): TaskDto
    {
        $taskId = new TaskId($command->id);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with id {$command->id} not found.");
        }

        $changedFields = [];
        $updatedBy = $command->updatedBy ?? 0;

        // Обновляем только переданные параметры, что б их
        if ($command->title !== null) {
            $old = $task->getTitle()->getValue();
            $task->changeTitle(new Title($command->title));
            if ($old !== $command->title) {
                $changedFields['title'] = $command->title;
            }
        }
        if ($command->description !== null) {
            $old = $task->getDescription();
            $task->changeDescription($command->description);
            if ($old !== $command->description) {
                $changedFields['description'] = $command->description;
            }
        }
        if ($command->statusId !== null) {
            $old = $task->getStatusId()->getValue();
            $task->changeStatus(new StatusId($command->statusId));
            if ($old !== $command->statusId) {
                $changedFields['statusId'] = $command->statusId;
            }
        }
        if ($command->priorityId !== null) {
            $old = $task->getPriorityId()->getValue();
            $task->changePriority(new PriorityId($command->priorityId));
            if ($old !== $command->priorityId) {
                $changedFields['priorityId'] = $command->priorityId;
            }
        }
        if ($command->dueDate !== null) {
            $old = $task->getDueDate()?->format('Y-m-d H:i:s');
            $task->changeDueDate($command->dueDate);
            $new = $command->dueDate->format('Y-m-d H:i:s');
            if ($old !== $new) {
                $changedFields['dueDate'] = $new;
            }
        }
        if ($command->assignedTo !== null) {
            $old = $task->getAssignedTo()?->getValue();
            $task->assignTo($command->assignedTo ? new UserId($command->assignedTo) : null);
            if ($old !== $command->assignedTo) {
                $changedFields['assignedTo'] = $command->assignedTo;
            }
        }
        if ($command->boardId !== null) {
            $old = $task->getBoardId();
            $task->setBoardId($command->boardId);
            if ($old !== $command->boardId) {
                $changedFields['boardId'] = $command->boardId;
            }
        }
        if ($command->parentId !== null) {
            $old = $task->getParentId()?->getValue();
            $task->setParentId($command->parentId ? new TaskId($command->parentId) : null);
            if ($old !== $command->parentId) {
                $changedFields['parentId'] = $command->parentId;
            }
        }

        $savedTask = $this->taskRepository->save($task);

        if (!empty($changedFields)) {
            $this->eventDispatcher->dispatch(
                new TaskUpdatedEvent($savedTask, $changedFields, $updatedBy)
            );
        }

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}