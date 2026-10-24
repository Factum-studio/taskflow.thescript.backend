<?php

declare(strict_types=1);

namespace modules\tasks\application\handler;

use modules\projectRbac\application\port\IProjectAccessService;
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
    private IProjectAccessService $projectAccess;

    public function __construct(
        IBoardColumnRepository $columnRepository,
        BoardColumnDtoAssembler $dtoAssembler,
        IProjectAccessService $projectAccess,
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
        $boardId = $column->getBoardId()->value();
        if (!$this->projectAccess->canViewBoard($query->userId, $boardId)) {
            throw new RuntimeException('You are not allowed to view this column');
        }
        return $this->dtoAssembler->toDto($column);
    }
}
