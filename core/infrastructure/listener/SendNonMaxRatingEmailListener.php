<?php
namespace core\infrastructure\listener;

use core\domain\event\RatingSubmittedEvent;
use core\application\notification\NotificationHub;
use core\application\notification\Notification;
use core\application\port\ILocalUserRepository;

class SendNonMaxRatingEmailListener
{
    public function __construct(
        private ILocalUserRepository $userRepository,
        private NotificationHub $notificationHub
    ) {}

    public function handle(RatingSubmittedEvent $event): void
    {
        $rating = $event->getRating();
        $userId = $rating->getUserId();

        $email = $this->userRepository->getEmail($userId);
        if (!$email) {
            return;
        }

        $notification = new Notification(
            'email',
            $email,
            'Помогите нам стать лучше',
            "Здравствуйте!\n\nМы заметили, что вы поставили не максимальную оценку нашему сервису. Пожалуйста, расскажите, с какими трудностями вы столкнулись при работе в нашем сервисе. Ваше мнение очень важно для нас!\n\nС уважением, команда TaskFlow."
        );

        $this->notificationHub->send($notification);
    }
}