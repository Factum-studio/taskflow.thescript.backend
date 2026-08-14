<?php

namespace modules\tasks\application\handler;

use modules\projects\application\port\IProjectAccess;
use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\application\dto\BoardColumnDto;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\application\query\GetBoardColumnQuery;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\ColumnId;
use RuntimeException;

class GetBoardColumnHandler
{
    private IBoardColumnRepository $columnRepository;
    private BoardColumnDtoAssembler $dtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        IBoardColumnRepository $columnRepository,
        BoardColumnDtoAssembler $dtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->columnRepository = $columnRepository;
        $this->dtoAssembler     = $dtoAssembler;
        $this->projectAccess    = $projectAccess;
    }

    public function handle(GetBoardColumnQuery $query): BoardColumnDto
    {
        $columnId = new ColumnId($query->id);
        $column = $this->columnRepository->findById($columnId);
        if (!$column) {
            throw new RuntimeException("Column with ID {$query->id} not found");
        }
        $boardId = $column->getBoardId()->getValue();
        if (!$this->projectAccess->canViewBoard($query->userId, $boardId)) {
            throw new RuntimeException('You are not allowed to view this column');
        }
        return $this->dtoAssembler->toDto($column);
    }
}