<?php

namespace modules\tasks\infrastructure\repository;

use DateTimeImmutable;
use modules\tasks\domain\entity\BoardColumn;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\BoardId;
use modules\tasks\domain\valueObject\ColumnId;
use modules\tasks\infrastructure\persistence\BoardColumnAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use yii\db\StaleObjectException;
use Exception;

class DbBoardColumnRepository implements IBoardColumnRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function save(BoardColumn $column): BoardColumn
    {
        $ar = $this->findARById($column->getId()) ?? new BoardColumnAR();
        $this->mapEntityToAR($column, $ar);

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save board column: ' . implode(', ', $ar->getFirstErrors()));
        }

        if (!$column->getId() || $column->getId()->getValue() !== (int)$ar->id) {
            $column->setId(new ColumnId((int)$ar->id));
        }

        return $column;
    }

    public function findById(ColumnId $id): ?BoardColumn
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findByBoard(BoardId $boardId): array
    {
        $ars = BoardColumnAR::find()
            ->where(['board_id' => $boardId->getValue()])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(BoardColumn $column): void
    {
        $ar = $this->findARById($column->getId());
        $ar?->delete();
    }

    private function findARById(ColumnId $id): ?BoardColumnAR
    {
        return BoardColumnAR::findOne($id->getValue());
    }

    private function mapEntityToAR(BoardColumn $column, BoardColumnAR $ar): void
    {
        $ar->board_id       = $column->getBoardId()->getValue();
        $ar->name           = $column->getName();
        $ar->label          = $column->getLabel();
        $ar->sort_order     = $column->getSortOrder();
        $ar->is_active      = $column->isActive() ? 1 : 0;
        $ar->is_final       = $column->isFinal() ? 1 : 0;
        $ar->color          = $column->getColor();
        $ar->workflow_id    = $column->getWorkflowId();
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(BoardColumnAR $ar): BoardColumn
    {
        return new BoardColumn(
            new ColumnId((int)$ar->id),
            new BoardId((int)$ar->board_id),
            $ar->name,
            $ar->label,
            (int)$ar->sort_order,
            (bool)$ar->is_active,
            (bool)$ar->is_final,
            $ar->color,
            $ar->workflow_id,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}