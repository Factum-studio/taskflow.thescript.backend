<?php
namespace core\application\port;

use core\domain\entity\FeedbackRating;

interface IFeedbackRatingRepository
{
    public function save(FeedbackRating $rating): FeedbackRating;
    public function findByUserId(int $userId): ?FeedbackRating;
    public function countMaxRatings(): int;
    public function countMinRatings(): int;
}