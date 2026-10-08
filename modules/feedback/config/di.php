<?php

declare(strict_types=1);

use core\application\port\IEventDispatcher as GlobalEventDispatcher;
use modules\feedback\application\port\IFeedbackIdeaRepository;
use modules\feedback\application\port\IFeedbackRatingRepository;
use modules\feedback\domain\event\RatingSubmittedEvent;
use modules\feedback\infrastructure\listener\SendNonMaxRatingEmailListener;
use modules\feedback\infrastructure\repository\DbFeedbackIdeaRepository;
use modules\feedback\infrastructure\repository\DbFeedbackRatingRepository;

$container = Yii::$container;

$container->setSingleton(IFeedbackRatingRepository::class, function () {
    return new DbFeedbackRatingRepository();
});

$container->setSingleton(IFeedbackIdeaRepository::class, function () {
    return new DbFeedbackIdeaRepository();
});

$globalDispatcher = Yii::$container->get(GlobalEventDispatcher::class);
$globalDispatcher->addListener(RatingSubmittedEvent::class, SendNonMaxRatingEmailListener::class);
