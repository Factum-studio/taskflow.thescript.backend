<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\CreateBoardColumnCommand;
use modules\tasks\application\dto\BoardColumnDto;
use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\entity\BoardColumn;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\BoardId;
use modules\tasks\domain\valueObject\ColumnId;
use DateTimeImmutable;
use RuntimeException;

class CreateBoardColumnHandler
{
    private IBoardColumnRepository $columnRepository;
    private BoardColumnDtoAssembler $dtoAssembler;
    private ITaskAccess $taskAccess;

    public function __construct(
        IBoardColumnRepository $columnRepository,
        BoardColumnDtoAssembler $dtoAssembler,
        ITaskAccess $taskAccess
    ) {
        $this->columnRepository = $columnRepository;
        $this->dtoAssembler     = $dtoAssembler;
        $this->taskAccess       = $taskAccess;
    }

    public function handle(CreateBoardColumnCommand $command): BoardColumnDto
    {
        if (!$this->taskAccess->canCreateColumn($command->createdBy, $command->boardId)) {
            throw new RuntimeException('You are not allowed to create columns on this board');
        }

        $column = new BoardColumn(
            new ColumnId(0),
            new BoardId($command->boardId),
            $command->name,
            $command->label,
            $command->sortOrder,
            $command->isActive,
            $command->isFinal,
            $command->color,
            $command->workflowId,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $saved = $this->columnRepository->save($column);
        return $this->dtoAssembler->toDto($saved);
    }
}