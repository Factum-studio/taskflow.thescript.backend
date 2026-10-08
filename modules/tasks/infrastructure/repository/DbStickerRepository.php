<?php

namespace modules\tasks\infrastructure\repository;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\tasks\domain\entity\Sticker;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\StickerName;
use modules\tasks\domain\valueObject\StickerType;
use modules\tasks\infrastructure\persistence\StickerAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use yii\db\Exception;
use Exception as Ex;
use yii\db\StaleObjectException;

class DbStickerRepository implements IStickerRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }


    /**
     * @throws Exception
     */
    public function save(Sticker $sticker): Sticker
    {
        $ar = $this->findARById($sticker->getId()) ?? new StickerAR();
        $this->mapEntityToAR($sticker, $ar);

        // Пользовательские
        if ($sticker->getType()->isUser()) {
            $exists = StickerAR::find()
                ->where(['name' => $sticker->getName()->value()])
                ->andWhere(['project_id' => $sticker->getProjectId()])
                ->andWhere(['type' => 'user'])
                ->exists();
            if ($exists && (!$sticker->getId() || $this->findARById($sticker->getId())->name !== $sticker->getName()->value())) {
                throw new RuntimeException("Sticker with name '{$sticker->getName()->value()}' already exists in this project");
            }
        } else {
            // Системные
            $exists = StickerAR::find()
                ->where(['name' => $sticker->getName()->value()])
                ->andWhere(['type' => 'system'])
                ->exists();
            if ($exists && (!$sticker->getId() || $this->findARById($sticker->getId())->name !== $sticker->getName()->value())) {
                throw new RuntimeException("System sticker with name '{$sticker->getName()->value()}' already exists");
            }
        }

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save sticker: ' . implode(', ', $ar->getFirstErrors()));
        }

        if (!$sticker->getId() || $sticker->getId()->value() !== (int)$ar->id) {
            $sticker->setId(new StickerId((int)$ar->id));
        }

        return $sticker;
    }

    /**
     * @throws Ex
     */
    public function findById(StickerId $id): ?Sticker
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findByProject(int $projectId, ?StickerType $type = null): array
    {
        $query = StickerAR::find()->where(['project_id' => $projectId]);
        if ($type) {
            $query->andWhere(['type' => $type->value()]);
        }
        $ars = $query->orderBy(['name' => SORT_ASC])->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function findSystemStickers(): array
    {
        $ars = StickerAR::find()
            ->where(['type' => 'system'])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(Sticker $sticker): void
    {
        $ar = $this->findARById($sticker->getId());
        $ar?->delete();
    }

    private function findARById(StickerId $id): ?StickerAR
    {
        return StickerAR::findOne($id->value());
    }

    private function mapEntityToAR(Sticker $sticker, StickerAR $ar): void
    {
        $ar->name = $sticker->getName()->value();
        $ar->type = $sticker->getType()->value();
        $ar->project_id = $sticker->getProjectId();
        $ar->data = $sticker->getData();
        $ar->color = $sticker->getColor();
        $ar->created_by = $sticker->getCreatedBy()->value();
    }

    /**
     * @throws Ex
     */
    private function mapARToEntity(StickerAR $ar): Sticker
    {
        return new Sticker(
            new StickerId((int)$ar->id),
            new StickerName($ar->name),
            new StickerType($ar->type),
            new UserId((int)$ar->created_by),
            $ar->project_id ? (int)$ar->project_id : null,
            $ar->data,
            $ar->color,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}