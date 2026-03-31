<?php

namespace modules\projects\infrastructure\listener;

use core\application\notification\Notification;
use core\application\notification\NotificationHub;
use modules\projects\domain\event\InvitationCreatedEvent;
use yii\helpers\Url;

class SendInvitationEmailListener
{
    private NotificationHub $notificationHub;

    public function __construct(NotificationHub $notificationHub)
    {
        $this->notificationHub = $notificationHub;
    }

    public function handle(InvitationCreatedEvent $event): void
    {
        $acceptUrl = Url::to(['/invitation/accept', 'token' => $event->getToken()], true);
        $subject = 'Invitation to project';
        $body = "You have been invited to join the project. Click here to accept: {$acceptUrl}";

        $notification = new Notification(
            'email',
            $event->getEmail(),
            $subject,
            $body,
            ['invitationId' => $event->getAggregateId()]
        );
        $this->notificationHub->send($notification);
    }
}