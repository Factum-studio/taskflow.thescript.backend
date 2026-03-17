<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\application\dto\BoardColumnDto;
use modules\tasks\application\query\GetBoardColumnQuery;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\ColumnId;
use RuntimeException;

class GetBoardColumnHandler
{
    private IBoardColumnRepository $columnRepository;
    private BoardColumnDtoAssembler $dtoAssembler;

    public function __construct(
        IBoardColumnRepository $columnRepository,
        BoardColumnDtoAssembler $dtoAssembler
    ) {
        $this->columnRepository = $columnRepository;
        $this->dtoAssembler     = $dtoAssembler;
    }

    public function handle(GetBoardColumnQuery $query): BoardColumnDto
    {
        $columnId = new ColumnId($query->id);
        $column = $this->columnRepository->findById($columnId);
        if (!$column) {
            throw new RuntimeException("Column with ID {$query->id} not found");
        }
        return $this->dtoAssembler->toDto($column);
    }
}