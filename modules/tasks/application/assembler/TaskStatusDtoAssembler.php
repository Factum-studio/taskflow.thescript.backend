<?php

namespace modules\tasks\application\assembler;

use modules\tasks\application\dto\TaskStatusDto;
use modules\tasks\domain\entity\TaskStatus;

class TaskStatusDtoAssembler
{
    public function toDto(TaskStatus $status): TaskStatusDto
    {
        return new TaskStatusDto([
            'id'            => $status->getId()->getValue(),
            'name'          => $status->getName(),
            'label'         => $status->getLabel(),
            'sortOrder'     => $status->getSortOrder(),
            'workflowId'    => $status->getWorkflowId(),
        ]);
    }

    /**
     * @param TaskStatus[] $statuses
     * @return TaskStatusDto[]
     */
    public function toDtoList(array $statuses): array
    {
        return array_map([$this, 'toDto'], $statuses);
    }
}