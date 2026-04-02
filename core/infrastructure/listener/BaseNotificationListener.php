<?php

namespace core\infrastructure\listener;

use core\application\port\ILocalUserRepository;
use core\application\notification\NotificationHub;
use core\application\notification\Notification;
use Yii;

abstract class BaseNotificationListener
{
    protected ILocalUserRepository $localUserRepository;
    protected NotificationHub $notificationHub;

    public function __construct(ILocalUserRepository $localUserRepository, NotificationHub $notificationHub)
    {
        $this->localUserRepository  = $localUserRepository;
        $this->notificationHub      = $notificationHub;
    }

    /**
     * Возвращает ID пользователя, которому нужно отправить уведомление
     */
    abstract protected function getTargetUserId(object $event): ?int;

    /**
     * Тема уведомления (для email)
     */
    abstract protected function getSubject(object $event): string;

    /**
     * Тело уведомления (для email/telegram/sms)
     */
    abstract protected function getBody(object $event): string;

    /**
     * Тип канала доставки: 'email', 'telegram', 'sms', 'push'
     */
    protected function getChannels(): array
    {
        return ['email', 'push'];
    }

    /**
     * Шаблонный метод
     */
    public function handle(object $event): void
    {
        $userId = $this->getTargetUserId($event);
        if (!$userId) {
            return;
        }

        $channels = $this->getChannels();

        foreach ($channels as $channel) {
            $recipient = $this->getRecipient($userId, $channel);
            if (!$recipient) {
                Yii::warning("Cannot send notification: no recipient for user {$userId} via {$channel}", 'notification');
                continue;
            }

            $notification = $this->createNotification($channel, $recipient, $event);
            $this->notificationHub->send($notification);
        }
    }

    private function getRecipient(int $userId, string $channel): ?string
    {
        return match ($channel) {
            'email'     => $this->localUserRepository->getEmail($userId),
            'push'      => (string)$userId,
            'telegram'  => null,
            'sms'       => null,
            default     => null,
        };
    }

    protected function createNotification(string $channel, string $recipient, object $event): Notification
    {
        $subject = $this->getSubject($event);
        $body = $this->getBody($event);
        $context = ['event' => $event];

        return new Notification($channel, $recipient, $subject, $body, $context);
    }
}