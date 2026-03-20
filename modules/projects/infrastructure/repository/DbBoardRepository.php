<?php

namespace modules\projects\infrastructure\repository;

use DateTimeImmutable;
use modules\projects\domain\entity\Board;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\valueObject\BoardId;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\Settings;
use modules\projects\infrastructure\persistence\BoardAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use yii\db\StaleObjectException;
use Exception;

class DbBoardRepository implements IBoardRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function save(Board $board): Board
    {
        $ar = $this->findARById($board->getId()) ?? new BoardAR();
        $this->mapEntityToAR($board, $ar);

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save board: ' . implode(', ', $ar->getFirstErrors()));
        }

        if (!$board->getId() || $board->getId()->getValue() !== (int)$ar->id) {
            $board->setId(new BoardId((int)$ar->id));
        }

        return $board;
    }

    /**
     * @throws Exception
     */
    public function findById(BoardId $id): ?Board
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findByProject(ProjectId $projectId): array
    {
        $ars = BoardAR::find()
            ->where(['project_id' => $projectId->getValue()])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Board $board): void
    {
        $ar = $this->findARById($board->getId());
        $ar?->delete();
    }

    private function findARById(BoardId $id): ?BoardAR
    {
        return BoardAR::findOne($id->getValue());
    }

    private function mapEntityToAR(Board $board, BoardAR $ar): void
    {
        $ar->project_id     = $board->getProjectId()->getValue();
        $ar->name           = $board->getName();
        $ar->description    = $board->getDescription();
        $ar->created_by     = $board->getCreatedBy()->getValue();
        $ar->settings       = $board->getSettings()->toArray();
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(BoardAR $ar): Board
    {
        return new Board(
            new BoardId((int)$ar->id),
            new ProjectId((int)$ar->project_id),
            $ar->name,
            new UserId((int)$ar->created_by),
            $ar->description,
            new Settings($ar->settings ?? []),
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}