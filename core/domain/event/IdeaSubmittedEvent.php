<?php
namespace core\domain\event;

use core\domain\entity\FeedbackIdea;
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