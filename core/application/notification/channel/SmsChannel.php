<?php

namespace core\application\notification\channel;

use core\application\notification\INotification;
use Yii;

class SmsChannel
{
    public function send(INotification $notification): bool
    {
        // TODO: реализовать через сервис sms
        Yii::info("Sms notification would be sent to {$notification->getRecipient()}", 'notification');
        return true;
    }
}