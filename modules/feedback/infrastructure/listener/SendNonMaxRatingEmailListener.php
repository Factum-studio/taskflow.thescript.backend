<?php

declare(strict_types=1);

namespace modules\feedback\infrastructure\listener;

use core\domain\exception\UserNotFoundException;
use core\domain\exception\ValidationException;
use modules\feedback\domain\event\RatingSubmittedEvent;
use core\application\notification\NotificationHub;
use core\application\notification\Notification;
use core\application\port\IUserRepository;
use core\domain\valueObject\UserId;

class SendNonMaxRatingEmailListener
{
    public function __construct(
        private IUserRepository $userRepository,
        private NotificationHub $notificationHub,
    ) {
    }

    /**
     * @throws ValidationException
     * @throws UserNotFoundException
     */
    public function handle(RatingSubmittedEvent $event): void
    {
        $rating = $event->getRating();
        $userId = $rating->getUserId();

        $email = $this->userRepository->findById(new UserId($userId))->getEmail()->value();
        if (!$email) {
            return;
        }

        $notification = new Notification(
            'email',
            $email,
            'Помогите нам стать лучше',
            "Здравствуйте!\n\nМы заметили, что вы поставили не максимальную оценку нашему сервису. Пожалуйста, расскажите, с какими трудностями вы столкнулись при работе в нашем сервисе. Ваше мнение очень важно для нас!\n\nС уважением, команда TaskFlow.",
        );

        $this->notificationHub->send($notification);
    }
}
