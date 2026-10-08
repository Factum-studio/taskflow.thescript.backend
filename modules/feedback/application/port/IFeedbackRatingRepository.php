<?php

declare(strict_types=1);

namespace modules\feedback\application\port;

use modules\feedback\domain\entity\FeedbackRating;

interface IFeedbackRatingRepository
{
    public function save(FeedbackRating $rating): FeedbackRating;
    public function findByUserId(int $userId): ?FeedbackRating;
    public function countMaxRatings(): int;
    public function countMinRatings(): int;
}
