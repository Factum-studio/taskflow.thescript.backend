<?php

namespace modules\tasks\domain\repository;

use modules\tasks\domain\entity\BoardColumn;
use modules\tasks\domain\valueObject\BoardId;
use modules\tasks\domain\valueObject\ColumnId;

interface IBoardColumnRepository
{
    public function save(BoardColumn $column): BoardColumn;
    public function findById(ColumnId $id): ?BoardColumn;
    /**
     * @return BoardColumn[]
     */
    public function findByBoard(BoardId $boardId): array;
    public function remove(BoardColumn $column): void;
}