<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\application\command\UpdateBoardCommand;
use modules\projects\application\dto\BoardDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\BoardId;
use RuntimeException;

class UpdateBoardHandler
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

    public function handle(UpdateBoardCommand $command): BoardDto
    {
        $boardId = new BoardId($command->id);
        $board = $this->boardRepository->findById($boardId);

        if ($board === null) {
            throw new RuntimeException("Board with ID {$command->id} not found");
        }

        if (!$this->projectAccess->canManageBoard($command->updatedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to update this board');
        }

        if ($command->name !== null) {
            $board->rename($command->name);
        }

        if ($command->description !== null) {
            $board->changeDescription($command->description);
        }

        if ($command->settings !== null) {
            $board->changeSettings($command->settings);
        }

        $savedBoard = $this->boardRepository->save($board);

        return $this->boardDtoAssembler->toDto($savedBoard);
    }
}