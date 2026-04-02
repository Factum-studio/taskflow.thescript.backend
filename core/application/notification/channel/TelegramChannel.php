<?php

namespace core\application\notification\channel;

use core\application\notification\INotification;
use Yii;

class TelegramChannel
{
    public function send(INotification $notification): bool
    {
        // TODO: реализовать через бота
        Yii::info("Telegram notification would be sent to {$notification->getRecipient()}", 'notification');
        return true;
    }
}