<?php

declare(strict_types=1);

namespace modules\feedback\application\handler;

use modules\feedback\application\command\SubmitRatingCommand;
use modules\feedback\application\port\IFeedbackRatingRepository;
use core\application\port\IEventDispatcher;
use modules\feedback\domain\entity\FeedbackRating;
use modules\feedback\domain\event\RatingSubmittedEvent;
use modules\feedback\domain\valueObject\Rating;

class SubmitRatingHandler
{
    public function __construct(
        private IFeedbackRatingRepository $ratingRepository,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    public function handle(SubmitRatingCommand $command): FeedbackRating
    {
        $existing = $this->ratingRepository->findByUserId($command->userId);
        $speed = new Rating($command->speed);
        $functionality = new Rating($command->functionality);
        $design = new Rating($command->design);
        $usability = new Rating($command->usability);

        if ($existing) {
            $existing->updateRatings($speed, $functionality, $design, $usability);
            $rating = $this->ratingRepository->save($existing);
        } else {
            $rating = new FeedbackRating(
                $command->userId,
                $speed,
                $functionality,
                $design,
                $usability,
            );
            $rating = $this->ratingRepository->save($rating);
        }

        if (!$rating->isMaxRating()) {
            $this->eventDispatcher->dispatch(new RatingSubmittedEvent($rating));
        }

        return $rating;
    }
}
