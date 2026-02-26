<?php

namespace modules\tasks\application\assembler;

use modules\tasks\application\dto\TaskPriorityDto;
use modules\tasks\domain\entity\TaskPriority;

class TaskPriorityDtoAssembler
{
    public function toDto(TaskPriority $priority): TaskPriorityDto
    {
        return new TaskPriorityDto([
            'id'    => $priority->getId()->getValue(),
            'value' => $priority->getValue(),
            'label' => $priority->getLabel(),
            'color' => $priority->getColor(),
        ]);
    }

    /**
     * @param TaskPriority[] $priorities
     * @return TaskPriorityDto[]
     */
    public function toDtoList(array $priorities): array
    {
        return array_map([$this, 'toDto'], $priorities);
    }
}