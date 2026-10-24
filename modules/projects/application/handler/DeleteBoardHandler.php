<?php

declare(strict_types=1);

namespace modules\projects\application\handler;

use modules\projects\application\command\DeleteBoardCommand;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\BoardId;
use RuntimeException;

class DeleteBoardHandler
{
    private IBoardRepository $boardRepository;
    private IProjectAccessService $projectAccess;

    public function __construct(
        IBoardRepository $boardRepository,
        IProjectAccessService $projectAccess,
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
