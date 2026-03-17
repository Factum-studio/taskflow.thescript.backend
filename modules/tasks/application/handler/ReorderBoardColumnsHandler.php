<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\ReorderBoardColumnsCommand;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\BoardId;
use modules\tasks\domain\valueObject\ColumnId;
use RuntimeException;

class ReorderBoardColumnsHandler
{
    private IBoardColumnRepository $columnRepository;

    public function __construct(IBoardColumnRepository $columnRepository)
    {
        $this->columnRepository = $columnRepository;
    }

    public function handle(ReorderBoardColumnsCommand $command): void
    {
        $boardId = new BoardId($command->boardId);
        $columns = $this->columnRepository->findByBoard($boardId);
        $columnMap = [];
        foreach ($columns as $col) {
            $columnMap[$col->getId()->getValue()] = $col;
        }

        $newOrder = 10; // можно использовать шаг 10 для возможности вставок
        foreach ($command->orderedIds as $id) {
            if (!isset($columnMap[$id])) {
                throw new RuntimeException("Column ID $id not found in this board");
            }
            $columnMap[$id]->setSortOrder($newOrder);
            $newOrder += 10;
            $this->columnRepository->save($columnMap[$id]);
        }
    }
}