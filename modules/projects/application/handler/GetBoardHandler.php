<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\application\dto\BoardDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\application\query\GetBoardQuery;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\BoardId;
use RuntimeException;

class GetBoardHandler
{
    private IBoardRepository $boardRepository;
    private BoardDtoAssembler $boardDtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        IBoardRepository $boardRepository,
        BoardDtoAssembler $boardDtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->boardRepository      = $boardRepository;
        $this->boardDtoAssembler    = $boardDtoAssembler;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(GetBoardQuery $query): BoardDto
    {
        $boardId = new BoardId($query->id);
        $board = $this->boardRepository->findById($boardId);

        if ($board === null) {
            throw new RuntimeException("Board with ID {$query->id} not found");
        }

        if (!$this->projectAccess->canViewBoard($query->userId, $query->id)) {
            throw new RuntimeException('You are not allowed to view this board');
        }

        return $this->boardDtoAssembler->toDto($board);
    }
}