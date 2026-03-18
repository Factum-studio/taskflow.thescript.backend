<?php

namespace modules\projects\application\assembler;

use modules\projects\domain\entity\Board;
use modules\projects\application\dto\BoardDto;

class BoardDtoAssembler
{
    public function toDto(Board $board): BoardDto
    {
        return new BoardDto([
            'id'            => $board->getId()->getValue(),
            'projectId'     => $board->getProjectId()->getValue(),
            'name'          => $board->getName(),
            'description'   => $board->getDescription(),
            'createdBy'     => $board->getCreatedBy()->getValue(),
            'settings'      => $board->getSettings()->toArray(),
            'createdAt'     => $board->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt'     => $board->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Board[] $boards
     * @return BoardDto[]
     */
    public function toDtoList(array $boards): array
    {
        return array_map([$this, 'toDto'], $boards);
    }
}