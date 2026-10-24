<?php

declare(strict_types=1);

namespace modules\projects\application\handler;

use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\application\dto\BoardDto;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projects\application\query\ListProjectBoardsQuery;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;

class ListProjectBoardsHandler
{
    private IBoardRepository $boardRepository;
    private BoardDtoAssembler $boardDtoAssembler;
    private IProjectAccessService $projectAccess;

    public function __construct(
        IBoardRepository $boardRepository,
        BoardDtoAssembler $boardDtoAssembler,
        IProjectAccessService $projectAccess,
    ) {
        $this->boardRepository      = $boardRepository;
        $this->boardDtoAssembler    = $boardDtoAssembler;
        $this->projectAccess        = $projectAccess;
    }

    /**
     * @return BoardDto[]
     */
    public function handle(ListProjectBoardsQuery $query): array
    {
        if (!$this->projectAccess->canViewProject($query->userId, $query->projectId)) {
            throw new RuntimeException('You are not allowed to view boards of this project');
        }

        $boards = $this->boardRepository->findByProject(new ProjectId($query->projectId));
        return $this->boardDtoAssembler->toDtoList($boards);
    }
}
