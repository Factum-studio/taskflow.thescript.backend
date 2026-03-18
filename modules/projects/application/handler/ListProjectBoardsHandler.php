<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\application\dto\BoardDto;
use modules\projects\application\query\ListProjectBoardsQuery;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\ProjectId;

class ListProjectBoardsHandler
{
    private IBoardRepository $boardRepository;
    private BoardDtoAssembler $boardDtoAssembler;

    public function __construct(
        IBoardRepository $boardRepository,
        BoardDtoAssembler $boardDtoAssembler
    ) {
        $this->boardRepository = $boardRepository;
        $this->boardDtoAssembler = $boardDtoAssembler;
    }

    /**
     * @return BoardDto[]
     */
    public function handle(ListProjectBoardsQuery $query): array
    {
        $boards = $this->boardRepository->findByProject(new ProjectId($query->projectId));
        return $this->boardDtoAssembler->toDtoList($boards);
    }
}