<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\DeleteBoardColumnCommand;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\ColumnId;
use RuntimeException;

class DeleteBoardColumnHandler
{
    private IBoardColumnRepository $columnRepository;

    public function __construct(IBoardColumnRepository $columnRepository)
    {
        $this->columnRepository = $columnRepository;
    }

    public function handle(DeleteBoardColumnCommand $command): void
    {
        $columnId = new ColumnId($command->id);
        $column = $this->columnRepository->findById($columnId);
        if (!$column) {
            throw new RuntimeException("Column with ID {$command->id} not found");
        }
        // TODO: добавить проверку, что на колонку нет задач, иначе нельзя удалить
        $this->columnRepository->remove($column);
    }
}