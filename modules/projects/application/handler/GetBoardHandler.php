<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\application\dto\BoardDto;
use modules\projects\application\query\GetBoardQuery;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\BoardId;
use RuntimeException;

class GetBoardHandler
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

    public function handle(GetBoardQuery $query): BoardDto
    {
        $boardId = new BoardId($query->id);
        $board = $this->boardRepository->findById($boardId);

        if ($board === null) {
            throw new RuntimeException("Board with ID {$query->id} not found");
        }

        return $this->boardDtoAssembler->toDto($board);
    }
}