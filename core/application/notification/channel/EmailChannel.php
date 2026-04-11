<?php

namespace core\application\notification\channel;

use core\application\notification\INotification;
use Yii;
use yii\mail\MailerInterface;

class EmailChannel
{
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $fromEmail, string $fromName)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send(INotification $notification): bool
    {
        if ($notification->getType() !== 'email') {
            return false;
        }

        /** @var MailerInterface $mailer */
        $mailer = Yii::$app->mailer;

        return $mailer
            ->compose()
            ->setFrom([$this->fromEmail => $this->fromName])
            ->setTo($notification->getRecipient())
            ->setSubject($notification->getSubject())
            ->setTextBody($notification->getBody())
            ->send();
    }
}