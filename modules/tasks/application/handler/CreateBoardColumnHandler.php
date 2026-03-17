<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\CreateBoardColumnCommand;
use modules\tasks\application\dto\BoardColumnDto;
use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\domain\entity\BoardColumn;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\BoardId;
use modules\tasks\domain\valueObject\ColumnId;
use DateTimeImmutable;

class CreateBoardColumnHandler
{
    private IBoardColumnRepository $columnRepository;
    private BoardColumnDtoAssembler $dtoAssembler;

    public function __construct(
        IBoardColumnRepository $columnRepository,
        BoardColumnDtoAssembler $dtoAssembler
    ) {
        $this->columnRepository = $columnRepository;
        $this->dtoAssembler = $dtoAssembler;
    }

    public function handle(CreateBoardColumnCommand $command): BoardColumnDto
    {
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