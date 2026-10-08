<?php

declare(strict_types=1);

namespace modules\feedback\domain\event;

use modules\feedback\domain\entity\FeedbackIdea;
use DateTimeImmutable;

class IdeaSubmittedEvent
{
    private FeedbackIdea $idea;
    private DateTimeImmutable $occurredAt;

    public function __construct(FeedbackIdea $idea)
    {
        $this->idea         = $idea;
        $this->occurredAt   = new DateTimeImmutable();
    }

    public function getIdea(): FeedbackIdea
    {
        return $this->idea;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
