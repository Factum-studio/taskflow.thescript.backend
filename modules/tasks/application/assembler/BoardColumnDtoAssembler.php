<?php

namespace modules\tasks\application\assembler;

use modules\tasks\application\dto\BoardColumnDto;
use modules\tasks\domain\entity\BoardColumn;

class BoardColumnDtoAssembler
{
    public function toDto(BoardColumn $column): BoardColumnDto
    {
        return new BoardColumnDto([
            'id'            => $column->getId()->getValue(),
            'boardId'       => $column->getBoardId()->getValue(),
            'name'          => $column->getName(),
            'label'         => $column->getLabel(),
            'sortOrder'     => $column->getSortOrder(),
            'isActive'      => $column->isActive(),
            'isFinal'       => $column->isFinal(),
            'color'         => $column->getColor(),
            'workflowId'    => $column->getWorkflowId(),
            'createdAt'     => $column->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt'     => $column->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param BoardColumn[] $columns
     * @return BoardColumnDto[]
     */
    public function toDtoList(array $columns): array
    {
        return array_map([$this, 'toDto'], $columns);
    }
}