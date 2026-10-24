<?php

declare(strict_types=1);

namespace modules\tasks\application\handler;

use modules\projectRbac\application\port\IProjectAccessService;
use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\application\query\GetBoardColumnsQuery;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\BoardId;
use RuntimeException;

class GetBoardColumnsHandler
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
