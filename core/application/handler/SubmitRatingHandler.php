<?php
namespace core\application\handler;

use core\application\command\SubmitRatingCommand;
use core\application\notification\Notification;
use core\application\notification\NotificationHub;
use core\application\port\IFeedbackRatingRepository;
use core\application\port\IEventDispatcher;
use core\application\port\ILocalUserRepository;
use core\domain\entity\FeedbackRating;
use core\domain\event\RatingSubmittedEvent;
use core\domain\valueObject\Rating;

class SubmitRatingHandler
{
    public function __construct(
        private IFeedbackRatingRepository $ratingRepository,
        private IEventDispatcher $eventDispatcher,
    ) {}

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
                $usability
            );
            $rating = $this->ratingRepository->save($rating);
        }

        if (!$rating->isMaxRating()) {
            $this->eventDispatcher->dispatch(new RatingSubmittedEvent($rating));
        }

        return $rating;
    }
}