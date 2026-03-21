<?php

namespace modules\projects\application\handler;

use modules\projects\application\command\DeleteBoardCommand;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\BoardId;
use RuntimeException;

class DeleteBoardHandler
{
    private IBoardRepository $boardRepository;
    private IProjectAccess $projectAccess;

    public function __construct(
        IBoardRepository $boardRepository,
        IProjectAccess $projectAccess
    ) {
        $this->boardRepository  = $boardRepository;
        $this->projectAccess    = $projectAccess;
    }

    public function handle(DeleteBoardCommand $command): void
    {
        $boardId = new BoardId($command->id);
        $board = $this->boardRepository->findById($boardId);

        if ($board === null) {
            throw new RuntimeException("Board with ID {$command->id} not found");
        }

        if (!$this->projectAccess->canDeleteBoard($command->deletedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to delete this board');
        }

        $this->boardRepository->remove($board);
    }
}