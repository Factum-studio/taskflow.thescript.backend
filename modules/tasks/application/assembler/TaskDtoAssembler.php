<?php

namespace modules\tasks\application\assembler;

use modules\tasks\domain\entity\Task;
use modules\tasks\application\dto\TaskDto;

class TaskDtoAssembler
{
    public function toDto(Task $task): TaskDto
    {
        return new TaskDto([
            'id'            => $task->getId()->getValue(),
            'title'         => $task->getTitle()->getValue(),
            'description'   => $task->getDescription(),
            'statusId'      => $task->getStatusId()->getValue(),
            'priorityId'    => $task->getPriorityId()->getValue(),
            'dueDate'       => $task->getDueDate()?->format('Y-m-d H:i:s'),
            'createdBy'     => $task->getCreatedBy()->getValue(),
            'assignedTo'    => $task->getAssignedTo()?->getValue(),
            'boardId'       => $task->getBoardId(),
            'parentId'      => $task->getParentId()?->getValue(),
            'overdue'       => $task->isOverdue(),
            'createdAt'     => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt'     => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            'deletedAt'     => $task->getDeletedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Task[] $tasks
     * @return TaskDto[]
     */
    public function toDtoList(array $tasks): array
    {
        return array_map([$this, 'toDto'], $tasks);
    }
}