<?php
namespace core\infrastructure\repository;

use core\application\port\IFeedbackIdeaRepository;
use core\domain\entity\FeedbackIdea;
use core\domain\valueObject\IdeaType;
use core\infrastructure\persistence\FeedbackIdeaAR;
use DateTimeImmutable;
use RuntimeException;

class DbFeedbackIdeaRepository implements IFeedbackIdeaRepository
{
    public function save(FeedbackIdea $idea): FeedbackIdea
    {
        $ar = $idea->getId()
            ? FeedbackIdeaAR::findOne($idea->getId())
            : new FeedbackIdeaAR();

        $ar->user_id = $idea->getUserId();
        $ar->type = $idea->getType()->value();
        $ar->comment = $idea->getComment();
        $ar->is_implemented = $idea->isImplemented();

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save feedback idea: ' . implode(', ', $ar->getFirstErrors()));
        }

        return new FeedbackIdea(
            $ar->user_id,
            new IdeaType($ar->type),
            $ar->comment,
            (bool)$ar->is_implemented,
            (int)$ar->id,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }

    public function findById(int $id): ?FeedbackIdea
    {
        $ar = FeedbackIdeaAR::findOne($id);
        return $ar ? $this->mapToEntity($ar) : null;
    }

    public function findAll(array $filters = []): array
    {
        $query = FeedbackIdeaAR::find();
        if (isset($filters['type'])) {
            $query->andWhere(['type' => $filters['type']]);
        }
        if (isset($filters['is_implemented'])) {
            $query->andWhere(['is_implemented' => $filters['is_implemented']]);
        }
        $ars = $query->orderBy(['created_at' => SORT_DESC])->all();
        return array_map([$this, 'mapToEntity'], $ars);
    }

    public function countAll(): int
    {
        return FeedbackIdeaAR::find()->count();
    }

    public function countImplemented(): int
    {
        return FeedbackIdeaAR::find()->where(['is_implemented' => true])->count();
    }

    private function mapToEntity(FeedbackIdeaAR $ar): FeedbackIdea
    {
        return new FeedbackIdea(
            (int)$ar->user_id,
            new IdeaType($ar->type),
            $ar->comment,
            (bool)$ar->is_implemented,
            (int)$ar->id,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}