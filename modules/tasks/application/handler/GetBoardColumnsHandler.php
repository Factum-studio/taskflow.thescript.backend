<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\application\query\GetBoardColumnsQuery;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\BoardId;

class GetBoardColumnsHandler
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

    public function handle(GetBoardColumnsQuery $query): array
    {
        $boardId = new BoardId($query->boardId);
        $columns = $this->columnRepository->findByBoard($boardId);
        return $this->dtoAssembler->toDtoList($columns);
    }
}