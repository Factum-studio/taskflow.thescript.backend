<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\application\command\CreateBoardCommand;
use modules\projects\application\dto\BoardDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\entity\Board;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\BoardId;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\Settings;
use DateTimeImmutable;
use RuntimeException;

class CreateBoardHandler
{
    private IBoardRepository $boardRepository;
    private IProjectRepository $projectRepository;
    private BoardDtoAssembler $boardDtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        IBoardRepository $boardRepository,
        IProjectRepository $projectRepository,
        BoardDtoAssembler $boardDtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->boardRepository      = $boardRepository;
        $this->projectRepository    = $projectRepository;
        $this->boardDtoAssembler    = $boardDtoAssembler;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(CreateBoardCommand $command): BoardDto
    {
        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$command->projectId} not found");
        }

        if (!$this->projectAccess->canCreateBoard($command->createdBy, $command->projectId)) {
            throw new RuntimeException('You are not allowed to create a board in this project');
        }

        $board = new Board(
            new BoardId(0),
            $projectId,
            $command->name,
            new UserId($command->createdBy),
            $command->description,
            new Settings($command->settings ?? []),
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $savedBoard = $this->boardRepository->save($board);

        return $this->boardDtoAssembler->toDto($savedBoard);
    }
}