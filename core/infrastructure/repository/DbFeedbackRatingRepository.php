<?php
namespace core\infrastructure\repository;

use core\application\port\IFeedbackRatingRepository;
use core\domain\entity\FeedbackRating;
use core\domain\valueObject\Rating;
use core\infrastructure\persistence\FeedbackRatingAR;
use DateTimeImmutable;
use RuntimeException;

class DbFeedbackRatingRepository implements IFeedbackRatingRepository
{
    public function save(FeedbackRating $rating): FeedbackRating
    {
        $ar = $rating->getId()
            ? FeedbackRatingAR::findOne($rating->getId())
            : new FeedbackRatingAR();

        $ar->user_id = $rating->getUserId();
        $ar->speed = $rating->getSpeed()->value();
        $ar->functionality = $rating->getFunctionality()->value();
        $ar->design = $rating->getDesign()->value();
        $ar->usability = $rating->getUsability()->value();

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save feedback rating: ' . implode(', ', $ar->getFirstErrors()));
        }

        return new FeedbackRating(
            $ar->user_id,
            new Rating($ar->speed),
            new Rating($ar->functionality),
            new Rating($ar->design),
            new Rating($ar->usability),
            (int)$ar->id,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }

    public function findByUserId(int $userId): ?FeedbackRating
    {
        $ar = FeedbackRatingAR::find()->where(['user_id' => $userId])->one();
        if (!$ar) {
            return null;
        }
        return $this->mapToEntity($ar);
    }

    public function countMaxRatings(): int
    {
        return FeedbackRatingAR::find()
            ->where(['>=', 'speed', 4])
            ->andWhere(['>=', 'functionality', 4])
            ->andWhere(['>=', 'design', 4])
            ->andWhere(['>=', 'usability', 4])
            ->count();
    }

    public function countMinRatings(): int
    {
        return FeedbackRatingAR::find()
            ->where(['and',
                ['<', 'speed', 3],
                ['<', 'functionality', 3],
                ['<', 'design', 3],
                ['<', 'usability', 3]
            ])
            ->count();
    }

    private function mapToEntity(FeedbackRatingAR $ar): FeedbackRating
    {
        return new FeedbackRating(
            (int)$ar->user_id,
            new Rating((int)$ar->speed),
            new Rating((int)$ar->functionality),
            new Rating((int)$ar->design),
            new Rating((int)$ar->usability),
            (int)$ar->id,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}