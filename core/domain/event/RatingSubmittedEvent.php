<?php
namespace core\domain\event;

use core\domain\entity\FeedbackRating;
use DateTimeImmutable;

class RatingSubmittedEvent
{
    private FeedbackRating $rating;
    private DateTimeImmutable $occurredAt;

    public function __construct(FeedbackRating $rating)
    {
        $this->rating       = $rating;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getRating(): FeedbackRating
    {
        return $this->rating;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}