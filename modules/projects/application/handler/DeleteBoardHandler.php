<?php

namespace modules\projects\application\handler;

use modules\projects\application\command\DeleteBoardCommand;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\BoardId;
use RuntimeException;

class DeleteBoardHandler
{
    private IBoardRepository $boardRepository;

    public function __construct(IBoardRepository $boardRepository)
    {
        $this->boardRepository = $boardRepository;
    }

    public function handle(DeleteBoardCommand $command): void
    {
        $boardId = new BoardId($command->id);
        $board = $this->boardRepository->findById($boardId);

        if ($board === null) {
            throw new RuntimeException("Board with ID {$command->id} not found");
        }

        // TODO: проверка прав

        $this->boardRepository->remove($board);
    }
}