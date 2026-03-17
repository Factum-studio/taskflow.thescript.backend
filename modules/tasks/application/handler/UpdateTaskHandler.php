<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\command\UpdateTaskCommand;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\domain\event\TaskMovedToColumnEvent;
use modules\tasks\domain\event\TaskUpdatedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\ColumnId;
use modules\tasks\domain\valueObject\PriorityId;
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
        $oldAssignee = $task->getAssignedTo();
        $oldColumn = $task->getColumnId();

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
        if ($command->columnId !== null) {
            $old = $task->getColumnId()->getValue();
            $task->moveToColumn(new ColumnId($command->columnId));
            if ($old !== $command->columnId) {
                $changedFields['columnId'] = $command->columnId;
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
        if ($command->plannedStart !== null || $command->plannedEnd !== null) {
            $oldStart = $task->getPlannedStart()?->format('Y-m-d H:i:s');
            $oldEnd = $task->getPlannedEnd()?->format('Y-m-d H:i:s');
            $task->changePlannedTime($command->plannedStart, $command->plannedEnd);
            $newStart = $command->plannedStart?->format('Y-m-d H:i:s');
            $newEnd = $command->plannedEnd?->format('Y-m-d H:i:s');
            if ($oldStart !== $newStart || $oldEnd !== $newEnd) {
                $changedFields['plannedStart'] = $newStart;
                $changedFields['plannedEnd'] = $newEnd;
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
            // Диспатчим общее событие обновления
            $this->eventDispatcher->dispatch(
                new TaskUpdatedEvent($savedTask, $changedFields, $updatedBy)
            );

            // Если изменилась колонка – диспатчим отдельное событие
            if (isset($changedFields['columnId'])) {
                $this->eventDispatcher->dispatch(
                    new TaskMovedToColumnEvent($savedTask, $oldColumn, $updatedBy)
                );
            }

            // Если изменился исполнитель – диспатчим событие назначения
            if (isset($changedFields['assignedTo'])) {
                $this->eventDispatcher->dispatch(
                    new TaskAssignedEvent($savedTask, $oldAssignee, $updatedBy)
                );
            }
        }

        return $this->taskDtoAssembler->toDto($savedTask);
    }
}