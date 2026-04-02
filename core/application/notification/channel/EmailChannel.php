<?php

namespace core\application\notification\channel;

use core\application\notification\INotification;
use yii\mail\MailerInterface;

class EmailChannel
{
    private MailerInterface $mailer;
    private string $fromEmail;
    private string $fromName;

    public function __construct(MailerInterface $mailer, string $fromEmail, string $fromName)
    {
        $this->mailer = $mailer;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send(INotification $notification): bool
    {
        if ($notification->getType() !== 'email') {
            return false;
        }

        return $this->mailer
            ->compose()
            ->setFrom([$this->fromEmail => $this->fromName])
            ->setTo($notification->getRecipient())
            ->setSubject($notification->getSubject())
            ->setTextBody($notification->getBody())
            ->send();
    }
}