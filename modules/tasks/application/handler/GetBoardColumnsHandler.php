<?php

namespace modules\tasks\application\handler;

use modules\projects\application\port\IProjectAccess;
use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\application\query\GetBoardColumnsQuery;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\BoardId;
use RuntimeException;

class GetBoardColumnsHandler
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

    public function handle(GetBoardColumnsQuery $query): array
    {
        if (!$this->projectAccess->canViewBoard($query->userId, $query->boardId)) {
            throw new RuntimeException('You are not allowed to view columns of this board');
        }

        $boardId = new BoardId($query->boardId);
        $columns = $this->columnRepository->findByBoard($boardId);
        return $this->dtoAssembler->toDtoList($columns);
    }
}