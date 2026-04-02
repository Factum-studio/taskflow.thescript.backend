<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\UpdateBoardColumnCommand;
use modules\tasks\application\dto\BoardColumnDto;
use modules\tasks\application\assembler\BoardColumnDtoAssembler;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\ColumnId;
use RuntimeException;

class UpdateBoardColumnHandler
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

    public function handle(UpdateBoardColumnCommand $command): BoardColumnDto
    {
        $columnId = new ColumnId($command->id);
        $column = $this->columnRepository->findById($columnId);
        if (!$column) {
            throw new RuntimeException("Column with ID {$command->id} not found");
        }

        if (!$this->taskAccess->canUpdateColumn($command->updatedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to update this column');
        }

        if ($command->name !== null) {
            $column->rename($command->name);
        }
        if ($command->label !== null) {
            $column->changeLabel($command->label);
        }
        if ($command->sortOrder !== null) {
            $column->setSortOrder($command->sortOrder);
        }
        if ($command->isActive !== null) {
            $column->setActive($command->isActive);
        }
        if ($command->isFinal !== null) {
            $column->setFinal($command->isFinal);
        }
        if ($command->color !== null) {
            $column->setColor($command->color);
        }
        if ($command->workflowId !== null) {
            $column->setWorkflowId($command->workflowId);
        }

        $saved = $this->columnRepository->save($column);
        return $this->dtoAssembler->toDto($saved);
    }
}