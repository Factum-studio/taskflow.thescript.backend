<?php

namespace core\application\notification;

use core\application\notification\Channel\EmailChannel;
use core\application\notification\channel\PushChannel;
use core\application\notification\Channel\TelegramChannel;
use Yii;

class NotificationHub
{
    private EmailChannel $emailChannel;
    private TelegramChannel $telegramChannel;
    private PushChannel $pushChannel;

    public function __construct(
        EmailChannel $emailChannel,
        TelegramChannel $telegramChannel,
        PushChannel $pushChannel
    ) {
        $this->emailChannel     = $emailChannel;
        $this->telegramChannel  = $telegramChannel;
        $this->pushChannel      = $pushChannel;
    }

    public function send(INotification $notification): void
    {
        try {
            $success = match ($notification->getType()) {
                'email'     => $this->emailChannel->send($notification),
                'telegram'  => $this->telegramChannel->send($notification),
                'push' => $this->pushChannel->send($notification),
                default     => false,
            };

            if (!$success) {
                Yii::warning("Failed to send {$notification->getType()} notification to {$notification->getRecipient()}", 'notification');
            }
        } catch (\Throwable $e) {
            Yii::error("Notification error: " . $e->getMessage(), 'notification');
        }
    }
}